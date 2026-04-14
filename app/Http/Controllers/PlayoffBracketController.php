<?php

namespace App\Http\Controllers;

use App\Models\PlayoffPool;
use App\Models\PlayoffPoolParticipant;
use App\Models\PlayoffBracket;
use App\Models\PlayoffBracketPick;
use App\Models\NbaPlayoffTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayoffBracketController extends Controller
{
    /**
     * List user's brackets in a pool.
     */
    public function index($poolId)
    {
        $pool = $this->findPool($poolId);
        $participant = $this->requireParticipant($pool);
        if ($participant instanceof \Illuminate\Http\JsonResponse) return $participant;

        $brackets = $participant->brackets()->with('picks.pickedTeam:id,name,nickname,image_url,conference')->get();

        return response()->json(['status' => true, 'data' => $brackets]);
    }

    /**
     * Create a new bracket (deducts credits if applicable).
     */
    public function store($poolId)
    {
        $pool = $this->findPool($poolId);
        $participant = $this->requireParticipant($pool);
        if ($participant instanceof \Illuminate\Http\JsonResponse) return $participant;

        if ($pool->is_locked) {
            return response()->json(['status' => false, 'message' => 'Pool is locked.'], 403);
        }

        if ($participant->brackets_count >= $pool->max_brackets_per_user) {
            return response()->json(['status' => false, 'message' => 'Maximum brackets reached.'], 400);
        }

        $cost = $pool->credit_cost_per_bracket;
        if ($cost > 0 && $participant->credits_available < $cost) {
            return response()->json(['status' => false, 'message' => 'Insufficient credits.'], 400);
        }

        // Monotonic bracket_index — never reuse (considers soft-deleted too)
        $maxIndex = PlayoffBracket::withTrashed()
            ->where('participant_id', $participant->id)
            ->max('bracket_index') ?? 0;
        $nextIndex = $maxIndex + 1;

        // Default name: {username}{N}, auto-advancing N until globally unique
        $username = Auth::user()->username ?? 'player';
        $bracketName = $this->generateUniqueBracketName($username, $nextIndex);

        DB::beginTransaction();
        try {
            if ($cost > 0) {
                $participant->decrement('credits_available', $cost);
            }
            $participant->increment('brackets_count');

            $bracket = PlayoffBracket::create([
                'participant_id' => $participant->id,
                'bracket_name' => $bracketName,
                'bracket_index' => $nextIndex,
                'status' => 'draft',
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Bracket created.',
                'data' => $bracket->load('picks'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Failed to create bracket.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a single bracket with picks.
     *
     * - Owner or superadmin can always view.
     * - Other pool participants can view once pool is locked / past close_datetime
     *   (NBA-0010: let users see each other's brackets after the deadline).
     */
    public function show($poolId, $bracketId)
    {
        $pool = $this->findPool($poolId);
        $user = Auth::user();
        $isSuperadmin = $user && $user->role_id === 1;

        $bracket = PlayoffBracket::where('id', $bracketId)
            ->with(['picks.pickedTeam:id,name,nickname,image_url,conference',
                'participant.user:id,name,username,avatar,image_url'])
            ->firstOrFail();

        // Verify bracket belongs to this pool
        if (!$bracket->participant || $bracket->participant->pool_id !== $pool->id) {
            abort(404);
        }

        $isOwner = $user && $bracket->participant->user_id === $user->id;

        if (!$isOwner && !$isSuperadmin) {
            // Must be a participant of the pool
            $isParticipant = $user && $pool->participants()->where('user_id', $user->id)->exists();
            if (!$isParticipant) {
                return response()->json(['status' => false, 'message' => 'You must join this pool first.'], 403);
            }
            // And pool must be past its reveal gate
            $locked = $pool->locked_at !== null
                || ($pool->pool_status ?? null) === 'locked'
                || ($pool->close_datetime && now()->gte($pool->close_datetime));
            if (!$locked) {
                return response()->json([
                    'status' => false,
                    'message' => 'Other brackets are hidden until the pool is locked.',
                ], 403);
            }
        }

        return response()->json(['status' => true, 'data' => $bracket]);
    }

    /**
     * Rename a bracket. Name must be globally unique.
     */
    public function update(Request $request, $poolId, $bracketId)
    {
        $pool = $this->findPool($poolId);
        $participant = $this->requireParticipant($pool);
        if ($participant instanceof \Illuminate\Http\JsonResponse) return $participant;

        $bracket = PlayoffBracket::where('id', $bracketId)->where('participant_id', $participant->id)->firstOrFail();

        $request->validate(['bracket_name' => 'required|string|max:50']);

        $name = trim($request->bracket_name);
        if ($this->bracketNameExists($name, $bracket->id)) {
            return response()->json(['status' => false, 'message' => 'That bracket name is already taken.'], 422);
        }

        $bracket->update(['bracket_name' => $name]);

        return response()->json(['status' => true, 'data' => $bracket]);
    }

    /**
     * Upsert all picks for a bracket (replace semantics).
     */
    public function savePicks(Request $request, $poolId, $bracketId)
    {
        $pool = $this->findPool($poolId);
        $participant = $this->requireParticipant($pool);
        if ($participant instanceof \Illuminate\Http\JsonResponse) return $participant;

        if ($pool->is_locked) {
            return response()->json(['status' => false, 'message' => 'Pool is locked.'], 403);
        }

        $bracket = PlayoffBracket::where('id', $bracketId)->where('participant_id', $participant->id)->firstOrFail();

        if ($bracket->status === 'finalized') {
            return response()->json(['status' => false, 'message' => 'Bracket is already finalized.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'picks' => 'required|array|max:15',
            'picks.*.pick_type' => 'required|in:round,champion',
            'picks.*.round' => 'required|integer|between:1,4',
            'picks.*.conference' => 'nullable|in:East,West',
            'picks.*.picked_team_id' => 'required|integer|exists:teams,id',
            'picks.*.picked_games' => 'nullable|integer|between:4,7',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $picks = collect($request->picks);

        // Validate per-round/conference caps
        $capsError = $this->validateRoundCaps($picks);
        if ($capsError) {
            return response()->json(['status' => false, 'message' => $capsError], 422);
        }

        // Validate R1 team membership (must be seeded in the playoff)
        $seedError = $this->validateSeedMembership($picks, $pool->playoff_id);
        if ($seedError) {
            return response()->json(['status' => false, 'message' => $seedError], 422);
        }

        // Validate cascade: R2 teams must be in R1, R3 in R2, Champion in R3
        $cascadeError = $this->validateCascade($picks);
        if ($cascadeError) {
            return response()->json(['status' => false, 'message' => $cascadeError], 422);
        }

        DB::beginTransaction();
        try {
            // Delete existing picks and replace
            $bracket->picks()->forceDelete();

            foreach ($picks as $pick) {
                PlayoffBracketPick::create([
                    'bracket_id' => $bracket->id,
                    'pick_type' => $pick['pick_type'],
                    'round' => $pick['round'],
                    'conference' => $pick['conference'] ?? null,
                    'picked_team_id' => $pick['picked_team_id'],
                    'picked_games' => $pick['picked_games'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Picks saved.',
                'data' => $bracket->fresh('picks.pickedTeam'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Failed to save picks.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Finalize a bracket (locks it permanently).
     * Optionally accepts bracket_name to rename the bracket as part of finalizing.
     */
    public function finalize(Request $request, $poolId, $bracketId)
    {
        $pool = $this->findPool($poolId);
        $participant = $this->requireParticipant($pool);
        if ($participant instanceof \Illuminate\Http\JsonResponse) return $participant;

        if ($pool->is_locked) {
            return response()->json(['status' => false, 'message' => 'Pool is locked.'], 403);
        }

        $bracket = PlayoffBracket::where('id', $bracketId)->where('participant_id', $participant->id)->firstOrFail();

        if ($bracket->status === 'finalized') {
            return response()->json(['status' => false, 'message' => 'Already finalized.'], 400);
        }

        // Optional rename at finalize time
        if ($request->filled('bracket_name')) {
            $request->validate(['bracket_name' => 'string|max:50']);
            $name = trim($request->bracket_name);
            if ($this->bracketNameExists($name, $bracket->id)) {
                return response()->json(['status' => false, 'message' => 'That bracket name is already taken.'], 422);
            }
            $bracket->bracket_name = $name;
        }

        $picks = $bracket->picks;

        // Must have exactly 15 picks
        if ($picks->count() !== 15) {
            return response()->json(['status' => false, 'message' => 'All 15 picks are required to finalize.'], 422);
        }

        // Every pick must have picked_games set
        $missingGames = $picks->filter(fn($p) => $p->picked_games === null);
        if ($missingGames->isNotEmpty()) {
            return response()->json(['status' => false, 'message' => 'All picks must have a games prediction (4-7).'], 422);
        }

        // Validate round counts: 4+4 R1, 2+2 R2, 1+1 R3, 1 Champion
        $capsError = $this->validateRoundCaps(collect($picks->toArray()));
        if ($capsError) {
            return response()->json(['status' => false, 'message' => $capsError], 422);
        }

        $bracket->status = 'finalized';
        $bracket->finalized_at = now();
        $bracket->save();

        return response()->json([
            'status' => true,
            'message' => 'Bracket finalized.',
            'data' => $bracket->fresh('picks.pickedTeam'),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Generate a unique bracket name by appending incrementing suffix if base is taken.
     * e.g. "gerardo3" → if taken → "gerardo3-2", "gerardo3-3", ...
     */
    private function generateUniqueBracketName($username, $index)
    {
        $base = "{$username}{$index}";
        if (!$this->bracketNameExists($base)) return $base;

        $i = 2;
        while ($this->bracketNameExists("{$base}-{$i}")) {
            $i++;
            if ($i > 9999) break;
        }
        return "{$base}-{$i}";
    }

    private function bracketNameExists($name, $exceptId = null)
    {
        $q = PlayoffBracket::where('bracket_name', $name);
        if ($exceptId) $q->where('id', '!=', $exceptId);
        return $q->exists();
    }

    private function findPool($id)
    {
        return is_numeric($id)
            ? PlayoffPool::findOrFail($id)
            : PlayoffPool::where('pool_number', $id)->firstOrFail();
    }

    private function requireParticipant(PlayoffPool $pool)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $participant = PlayoffPoolParticipant::where('pool_id', $pool->id)->where('user_id', $userId)->first();

        if ($participant) {
            return $participant;
        }

        // Superadmins who haven't joined: auto-join them
        if ($user && $user->role_id === 1) {
            return PlayoffPoolParticipant::create([
                'pool_id' => $pool->id,
                'user_id' => $userId,
                'credits_available' => $pool->initial_credits,
                'brackets_count' => 0,
                'total_points' => 0,
            ]);
        }

        return response()->json(['status' => false, 'message' => 'You must join this pool first.'], 403);
    }

    private function validateRoundCaps($picks)
    {
        $caps = [
            '1_East' => 4, '1_West' => 4,
            '2_East' => 2, '2_West' => 2,
            '3_East' => 1, '3_West' => 1,
        ];

        foreach ($caps as $key => $cap) {
            [$round, $conf] = explode('_', $key);
            $count = $picks->filter(fn($p) => (string) $p['round'] === $round && ($p['conference'] ?? '') === $conf)->count();
            if ($count > $cap) {
                return "Round {$round} {$conf} has {$count} picks — max is {$cap}.";
            }
        }

        // Champion cap
        $champCount = $picks->filter(fn($p) => (string) $p['round'] === '4')->count();
        if ($champCount > 1) {
            return "Only 1 champion pick allowed.";
        }

        return null;
    }

    private function validateSeedMembership($picks, $playoffId)
    {
        $seededTeamIds = NbaPlayoffTeam::where('playoff_id', $playoffId)
            ->pluck('team_id')
            ->toArray();

        foreach ($picks as $pick) {
            if (!in_array($pick['picked_team_id'], $seededTeamIds)) {
                return "Team ID {$pick['picked_team_id']} is not seeded in this playoff.";
            }
        }

        // Validate conference membership for R1 picks
        $r1Picks = $picks->filter(fn($p) => (string) $p['round'] === '1');
        foreach ($r1Picks as $pick) {
            $seedRow = NbaPlayoffTeam::where('playoff_id', $playoffId)
                ->where('team_id', $pick['picked_team_id'])
                ->first();
            if ($seedRow && $seedRow->conference !== $pick['conference']) {
                return "Team ID {$pick['picked_team_id']} is in {$seedRow->conference} conference, not {$pick['conference']}.";
            }
        }

        return null;
    }

    private function validateCascade($picks)
    {
        // R2 teams must be in R1 (same conference)
        foreach ([2, 3] as $round) {
            $prevRound = $round - 1;
            $roundPicks = $picks->filter(fn($p) => (string) $p['round'] === (string) $round);
            foreach ($roundPicks as $pick) {
                $inPrevRound = $picks->contains(fn($p) =>
                    (string) $p['round'] === (string) $prevRound
                    && $p['conference'] === $pick['conference']
                    && $p['picked_team_id'] === $pick['picked_team_id']
                );
                if (!$inPrevRound) {
                    return "Round {$round} pick (team {$pick['picked_team_id']}) must also be picked in Round {$prevRound} for {$pick['conference']}.";
                }
            }
        }

        // Champion (round 4) must be in R3 (either conference)
        $champPicks = $picks->filter(fn($p) => (string) $p['round'] === '4');
        foreach ($champPicks as $champ) {
            $inR3 = $picks->contains(fn($p) =>
                (string) $p['round'] === '3'
                && $p['picked_team_id'] === $champ['picked_team_id']
            );
            if (!$inR3) {
                return "Champion pick (team {$champ['picked_team_id']}) must also be a Conference Finals (R3) pick.";
            }
        }

        return null;
    }
}
