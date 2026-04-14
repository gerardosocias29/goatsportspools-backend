<?php

namespace App\Http\Controllers;

use App\Models\NbaPlayoff;
use App\Models\NbaPlayoffTeam;
use App\Models\PlayoffPool;
use App\Models\Team;
use App\Services\PlayoffScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlayoffAdminController extends Controller
{
    /**
     * List all playoffs (superadmin).
     */
    public function listPlayoffs()
    {
        $this->requireSuperadmin();

        $playoffs = NbaPlayoff::with('teams.team:id,name,nickname,code,image_url,conference')
            ->orderBy('year', 'desc')
            ->get();

        return response()->json(['status' => true, 'data' => $playoffs]);
    }

    /**
     * Get a single playoff with all teams and their results.
     */
    public function showPlayoff($id)
    {
        $this->requireSuperadmin();

        $playoff = NbaPlayoff::with('teams.team:id,name,nickname,code,image_url,conference')
            ->findOrFail($id);

        // Organize by conference for easy display
        $east = $playoff->teams->where('conference', 'East')->sortBy('seed')->values();
        $west = $playoff->teams->where('conference', 'West')->sortBy('seed')->values();

        return response()->json([
            'status' => true,
            'data' => [
                'playoff' => $playoff,
                'east' => $east,
                'west' => $west,
            ],
        ]);
    }

    /**
     * Update round results for a team.
     * Admin enters who won each series: the winning team's beat_seed and games.
     *
     * POST /api/admin/playoffs/{playoffId}/results
     * Body: { team_id, round, beat_seed, games }
     *
     * Example: Thunder (seed 1) beat Mavericks (seed 8) in 5 games in R1
     *   { team_id: <thunder_id>, round: 1, beat_seed: 8, games: 5 }
     */
    public function updateResult(Request $request, $playoffId)
    {
        $this->requireSuperadmin();

        $validator = Validator::make($request->all(), [
            'team_id' => 'required|integer',
            'round' => 'required|integer|between:1,4',
            'beat_seed' => 'required|integer|between:1,8',
            'games' => 'required|integer|between:4,7',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $playoff = NbaPlayoff::findOrFail($playoffId);
        $playoffTeam = NbaPlayoffTeam::where('playoff_id', $playoff->id)
            ->where('team_id', $request->team_id)
            ->firstOrFail();

        $round = $request->round;
        $beatSeedCol = $round === 4 ? 'finals_beat_seed' : "r{$round}_beat_seed";
        $gamesCol = $round === 4 ? 'finals_games' : "r{$round}_games";

        $playoffTeam->update([
            $beatSeedCol => $request->beat_seed,
            $gamesCol => $request->games,
        ]);

        // Update playoff current_round to at least this round
        if ($playoff->current_round < $round) {
            $playoff->update([
                'current_round' => $round,
                'status' => 'in_progress',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => "Round {$round} result saved for {$playoffTeam->team->nickname} (beat seed {$request->beat_seed} in {$request->games} games).",
            'data' => $playoffTeam->fresh('team:id,name,nickname,image_url'),
        ]);
    }

    /**
     * Bulk update round results — for entering an entire round at once.
     *
     * POST /api/admin/playoffs/{playoffId}/results/bulk
     * Body: { round: 1, results: [ { team_id, beat_seed, games }, ... ] }
     */
    public function bulkUpdateResults(Request $request, $playoffId)
    {
        $this->requireSuperadmin();

        $validator = Validator::make($request->all(), [
            'round' => 'required|integer|between:1,4',
            'results' => 'required|array|min:1',
            'results.*.team_id' => 'required|integer',
            'results.*.beat_seed' => 'required|integer|between:1,8',
            'results.*.games' => 'required|integer|between:4,7',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $playoff = NbaPlayoff::findOrFail($playoffId);
        $round = $request->round;
        $beatSeedCol = $round === 4 ? 'finals_beat_seed' : "r{$round}_beat_seed";
        $gamesCol = $round === 4 ? 'finals_games' : "r{$round}_games";

        $updated = 0;
        foreach ($request->results as $result) {
            $playoffTeam = NbaPlayoffTeam::where('playoff_id', $playoff->id)
                ->where('team_id', $result['team_id'])
                ->first();

            if ($playoffTeam) {
                $playoffTeam->update([
                    $beatSeedCol => $result['beat_seed'],
                    $gamesCol => $result['games'],
                ]);
                $updated++;
            }
        }

        // Update playoff current_round
        if ($playoff->current_round < $round) {
            $playoff->update([
                'current_round' => $round,
                'status' => 'in_progress',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => "Updated {$updated} team(s) for round {$round}.",
        ]);
    }

    /**
     * Clear a round result for a team (undo).
     */
    public function clearResult(Request $request, $playoffId)
    {
        $this->requireSuperadmin();

        $validator = Validator::make($request->all(), [
            'team_id' => 'required|integer',
            'round' => 'required|integer|between:1,4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $playoff = NbaPlayoff::findOrFail($playoffId);
        $playoffTeam = NbaPlayoffTeam::where('playoff_id', $playoff->id)
            ->where('team_id', $request->team_id)
            ->firstOrFail();

        $round = $request->round;
        $beatSeedCol = $round === 4 ? 'finals_beat_seed' : "r{$round}_beat_seed";
        $gamesCol = $round === 4 ? 'finals_games' : "r{$round}_games";

        $playoffTeam->update([
            $beatSeedCol => null,
            $gamesCol => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => "Cleared round {$round} result for {$playoffTeam->team->nickname}.",
        ]);
    }

    /**
     * Get round summary — which teams won each round.
     */
    public function roundSummary($playoffId)
    {
        $this->requireSuperadmin();

        $playoff = NbaPlayoff::with('teams.team:id,name,nickname,code,image_url,conference')
            ->findOrFail($playoffId);

        $summary = [];
        foreach ([1, 2, 3, 4] as $round) {
            $beatSeedCol = $round === 4 ? 'finals_beat_seed' : "r{$round}_beat_seed";
            $gamesCol = $round === 4 ? 'finals_games' : "r{$round}_games";

            $winners = $playoff->teams
                ->filter(fn($t) => $t->$beatSeedCol !== null)
                ->map(fn($t) => [
                    'team_id' => $t->team_id,
                    'team' => $t->team,
                    'conference' => $t->conference,
                    'seed' => $t->seed,
                    'beat_seed' => $t->$beatSeedCol,
                    'games' => $t->$gamesCol,
                ])
                ->values();

            $summary[] = [
                'round' => $round,
                'label' => $round === 4 ? 'NBA Finals' : "Round {$round}",
                'winners_count' => $winners->count(),
                'expected_count' => $round === 4 ? 1 : ($round === 3 ? 2 : ($round === 2 ? 4 : 8)),
                'complete' => $winners->count() >= ($round === 4 ? 1 : ($round === 3 ? 2 : ($round === 2 ? 4 : 8))),
                'winners' => $winners,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'playoff' => $playoff->only(['id', 'year', 'name', 'status', 'current_round']),
                'rounds' => $summary,
            ],
        ]);
    }

    /**
     * Create a playoff pool (superadmin or playoff admin).
     */
    public function createPool(Request $request)
    {
        $user = Auth::user();
        if ($user->role_id !== 1 && !$user->is_playoff_admin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'playoff_id' => 'required|integer|exists:nba_playoffs,id',
            'pool_name' => 'required|string|max:100',
            'pool_description' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:4',
            'initial_credits' => 'nullable|integer|min:0',
            'close_datetime' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Hardcoded defaults — not user-configurable to prevent injection
        $pool = \App\Models\PlayoffPool::create([
            'admin_id' => $user->id,
            'playoff_id' => $request->playoff_id,
            'pool_number' => \App\Models\PlayoffPool::generatePoolNumber(),
            'pool_name' => $request->pool_name,
            'pool_description' => $request->pool_description,
            'password' => $request->password ? bcrypt($request->password) : null,
            'initial_credits' => $request->initial_credits ?? 0,
            'credit_cost_per_bracket' => 0,
            'max_brackets_per_user' => 8,
            'close_datetime' => $request->close_datetime,
            'pool_status' => 'open',
        ]);

        // Auto-join the creator as a participant
        \App\Models\PlayoffPoolParticipant::create([
            'pool_id' => $pool->id,
            'user_id' => $user->id,
            'credits_available' => $pool->initial_credits,
            'brackets_count' => 0,
            'total_points' => 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Playoff pool created.',
            'data' => $pool,
        ], 201);
    }

    // ─── Scoring ─────────────────────────────────────────────

    /**
     * Score a specific round across all finalized brackets.
     *
     * POST /api/admin/playoffs/{playoffId}/score
     * Body: { round: 1 }   (1-4)
     */
    public function scoreRound(Request $request, $playoffId)
    {
        $this->requireSuperadmin();

        $validator = Validator::make($request->all(), [
            'round' => 'required|integer|between:1,4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $service = new PlayoffScoringService();
        $result = $service->scoreRound((int) $playoffId, (int) $request->round);

        return response()->json([
            'status' => true,
            'message' => "Round {$request->round} scored: {$result['correct']} correct, {$result['incorrect']} incorrect out of {$result['picks_scored']} picks.",
            'data' => $result,
        ]);
    }

    /**
     * Rescore ALL rounds (1-4) for a playoff.
     *
     * POST /api/admin/playoffs/{playoffId}/score-all
     */
    public function scoreAll($playoffId)
    {
        $this->requireSuperadmin();

        $service = new PlayoffScoringService();
        $results = $service->scoreAll((int) $playoffId);

        $totalScored = array_sum(array_column($results, 'picks_scored'));
        $totalCorrect = array_sum(array_column($results, 'correct'));

        return response()->json([
            'status' => true,
            'message' => "Full rescore complete: {$totalCorrect} correct picks out of {$totalScored} total across all rounds.",
            'data' => $results,
        ]);
    }

    /**
     * Get scoring breakdown for a specific pool (standings data).
     *
     * GET /api/admin/playoffs/{playoffId}/standings/{poolId}
     */
    public function poolStandings($playoffId, $poolId)
    {
        $this->requireSuperadmin();

        $pool = PlayoffPool::where('id', $poolId)
            ->where('playoff_id', $playoffId)
            ->firstOrFail();

        // Per-bracket rows: only PAID brackets appear in standings
        $brackets = \App\Models\PlayoffBracket::whereHas('participant', function ($q) use ($pool) {
                $q->where('pool_id', $pool->id);
            })
            ->where('is_paid', true)
            ->with(['participant.user:id,name,username,avatar,image_url', 'picks'])
            ->orderByDesc('total_points')
            ->get()
            ->map(fn($b) => $this->bracketStandingRow($b, $pool));

        return response()->json([
            'status' => true,
            'data' => [
                'pool' => $pool->only(['id', 'pool_name', 'pool_number', 'pool_status']),
                'standings' => $brackets,
            ],
        ]);
    }

    /**
     * Global standings across all pools for a playoff.
     *
     * GET /api/admin/playoffs/{playoffId}/global-standings
     */
    public function globalStandings($playoffId)
    {
        $this->requireSuperadmin();

        $playoff = NbaPlayoff::findOrFail($playoffId);

        $poolIds = PlayoffPool::where('playoff_id', $playoffId)->pluck('id');
        $poolsById = PlayoffPool::whereIn('id', $poolIds)->get()->keyBy('id');

        // Per-bracket rows across all pools — only PAID brackets
        $rows = \App\Models\PlayoffBracket::whereHas('participant', function ($q) use ($poolIds) {
                $q->whereIn('pool_id', $poolIds);
            })
            ->where('is_paid', true)
            ->with(['participant.user:id,name,username,avatar,image_url', 'picks'])
            ->orderByDesc('total_points')
            ->get()
            ->map(function ($b) use ($poolsById) {
                $pool = $poolsById->get($b->participant->pool_id);
                return $this->bracketStandingRow($b, $pool);
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => [
                'playoff' => $playoff->only(['id', 'year', 'name', 'status', 'current_round']),
                'pools_count' => $poolIds->count(),
                'participants_count' => $rows->pluck('user.id')->unique()->count(),
                'brackets_count' => $rows->count(),
                'standings' => $rows,
            ],
        ]);
    }

    /**
     * Shared per-bracket standings row shape.
     */
    private function bracketStandingRow(\App\Models\PlayoffBracket $b, $pool = null): array
    {
        $rounds = $b->picks->groupBy('round')->map(function ($roundPicks) {
            return [
                'correct' => $roundPicks->filter(fn($p) => $p->base_points > 0)->count(),
                'total' => $roundPicks->count(),
                'base_points' => $roundPicks->sum('base_points'),
                'games_bonus' => $roundPicks->sum('games_bonus'),
                'seed_bonus' => $roundPicks->sum('seed_bonus'),
            ];
        });

        return [
            'bracket_id' => $b->id,
            'bracket_name' => $b->bracket_name,
            'status' => $b->status,
            'is_paid' => (bool) $b->is_paid,
            'total_points' => (int) $b->total_points,
            'rounds' => $rounds,
            'user' => $b->participant?->user,
            'participant_id' => $b->participant_id,
            'pool_id' => $pool?->id,
            'pool_name' => $pool?->pool_name,
            'pool_number' => $pool?->pool_number,
        ];
    }

    // ─── Playoff Year + Team Setup ──────────────────────────

    /**
     * Create a new playoff year.
     *
     * POST /api/admin/playoffs/create
     * Body: { year, name }
     */
    public function createPlayoff(Request $request)
    {
        $this->requireSuperadmin();

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:2040',
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $playoff = NbaPlayoff::create([
            'year' => $request->year,
            'name' => $request->name,
            'status' => 'upcoming',
            'current_round' => 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Playoff year created.',
            'data' => $playoff,
        ], 201);
    }

    /**
     * Save/update teams for a playoff year.
     *
     * POST /api/admin/playoffs/{playoffId}/teams
     * Body: { teams: [ { team_id, conference, seed }, ... ] }
     */
    public function saveTeams(Request $request, $playoffId)
    {
        $this->requireSuperadmin();

        $playoff = NbaPlayoff::findOrFail($playoffId);

        $validator = Validator::make($request->all(), [
            'teams' => 'required|array|min:1|max:16',
            'teams.*.team_id' => 'required|integer|exists:teams,id',
            'teams.*.conference' => 'required|string|in:East,West',
            'teams.*.seed' => 'required|integer|between:1,8',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Clear existing teams and re-insert
        NbaPlayoffTeam::where('playoff_id', $playoff->id)->forceDelete();

        foreach ($request->teams as $t) {
            NbaPlayoffTeam::create([
                'playoff_id' => $playoff->id,
                'team_id' => $t['team_id'],
                'conference' => $t['conference'],
                'seed' => $t['seed'],
            ]);
        }

        $playoff->update(['status' => 'upcoming']);

        return response()->json([
            'status' => true,
            'message' => count($request->teams) . ' teams saved.',
            'data' => $playoff->fresh('teams.team:id,name,nickname,code,image_url,conference'),
        ]);
    }

    /**
     * List NBA teams for team selection (filtered by league = NBA).
     */
    public function listNbaTeams()
    {
        $this->requireSuperadmin();

        $teams = Team::where('league', 'NBA')
            ->orderBy('conference')
            ->orderBy('name')
            ->get(['id', 'name', 'nickname', 'code', 'conference', 'image_url']);

        return response()->json(['status' => true, 'data' => $teams]);
    }

    // ─── Pool Management ────────────────────────────────────

    /**
     * Lock a playoff pool (stop bracket edits).
     *
     * POST /api/admin/playoffs/pools/{poolNumber}/lock
     */
    public function lockPool($poolNumber)
    {
        $this->requireAdmin();

        $pool = PlayoffPool::where('pool_number', $poolNumber)->firstOrFail();
        $pool->update([
            'pool_status' => 'locked',
            'locked_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pool locked. No more bracket edits allowed.',
        ]);
    }

    /**
     * Recalculate scores for all brackets in a specific pool.
     *
     * POST /api/admin/playoffs/pools/{poolNumber}/recalculate
     */
    public function recalculatePool($poolNumber)
    {
        $this->requireAdmin();

        $pool = PlayoffPool::where('pool_number', $poolNumber)->firstOrFail();

        if (!$pool->playoff_id) {
            return response()->json(['status' => false, 'message' => 'Pool is not linked to a playoff year.'], 422);
        }

        $service = new PlayoffScoringService();
        $results = $service->scoreAll($pool->playoff_id);

        // Count finalized brackets in this pool
        $bracketsCount = \App\Models\PlayoffBracket::whereHas('participant', function ($q) use ($pool) {
            $q->where('pool_id', $pool->id);
        })->where('status', 'finalized')->count();

        // Per-round summary: "R1: 3/4 correct" etc.
        $roundSummary = [];
        foreach ($results as $r) {
            if ($r['picks_scored'] > 0) {
                $roundSummary[] = "R{$r['round']}: {$r['correct']}/{$r['picks_scored']} correct";
            }
        }

        $message = $bracketsCount === 0
            ? 'No finalized brackets found to score.'
            : "Scores recalculated for {$bracketsCount} bracket(s). " . implode(', ', $roundSummary);

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $results,
        ]);
    }

    /**
     * Update a playoff (name, year, status).
     *
     * PATCH /api/admin/playoffs/{id}
     */
    public function updatePlayoff(Request $request, $id)
    {
        $this->requireSuperadmin();

        $playoff = NbaPlayoff::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'year' => 'sometimes|integer|min:2020|max:2040',
            'status' => 'sometimes|in:upcoming,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $playoff->update($request->only(['name', 'year', 'status']));

        return response()->json([
            'status' => true,
            'message' => 'Playoff updated.',
            'data' => $playoff->fresh(),
        ]);
    }

    /**
     * Archive (soft delete) a playoff.
     *
     * DELETE /api/admin/playoffs/{id}
     */
    public function deletePlayoff($id)
    {
        $this->requireSuperadmin();

        $playoff = NbaPlayoff::findOrFail($id);
        $playoff->delete();

        return response()->json([
            'status' => true,
            'message' => 'Playoff archived.',
        ]);
    }

    /**
     * Update a pool's editable fields.
     *
     * PATCH /api/admin/playoffs/pools/{poolNumber}
     */
    public function updatePool(Request $request, $poolNumber)
    {
        $this->requireAdmin();

        $pool = PlayoffPool::where('pool_number', $poolNumber)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'pool_name' => 'sometimes|string|max:150',
            'pool_description' => 'sometimes|nullable|string',
            'initial_credits' => 'sometimes|integer|min:0',
            'credit_cost_per_bracket' => 'sometimes|integer|min:0',
            'max_brackets_per_user' => 'sometimes|integer|min:1|max:50',
            'close_datetime' => 'sometimes|nullable|date',
            'password' => 'sometimes|nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $pool->update($request->only([
            'pool_name', 'pool_description', 'initial_credits',
            'credit_cost_per_bracket', 'max_brackets_per_user',
            'close_datetime', 'password',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Pool updated.',
            'data' => $pool->fresh(),
        ]);
    }

    /**
     * List brackets in a pool (admin).
     *
     * GET /api/admin/playoffs/pools/{poolNumber}/brackets
     */
    public function listPoolBrackets($poolNumber)
    {
        $this->requireAdmin();

        $pool = PlayoffPool::where('pool_number', $poolNumber)->firstOrFail();

        $brackets = \App\Models\PlayoffBracket::whereHas('participant', function ($q) use ($pool) {
            $q->where('pool_id', $pool->id);
        })
        ->with(['participant.user:id,name,username,avatar'])
        ->orderByDesc('is_paid')
        ->orderByDesc('total_points')
        ->get();

        return response()->json(['status' => true, 'data' => $brackets]);
    }

    /**
     * Toggle PAID on a bracket. Only PAID brackets appear in standings.
     *
     * PATCH /api/admin/playoffs/brackets/{bracketId}/paid
     * Body: { is_paid: true|false }
     */
    public function toggleBracketPaid(Request $request, $bracketId)
    {
        $this->requireAdmin();

        $validator = Validator::make($request->all(), [
            'is_paid' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $bracket = \App\Models\PlayoffBracket::findOrFail($bracketId);

        if ($request->boolean('is_paid')) {
            $bracket->update([
                'is_paid' => true,
                'paid_at' => now(),
                'paid_by_admin_id' => Auth::id(),
            ]);
        } else {
            $bracket->update([
                'is_paid' => false,
                'paid_at' => null,
                'paid_by_admin_id' => null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => $bracket->is_paid ? 'Bracket marked as PAID.' : 'Bracket marked as unpaid.',
            'data' => $bracket->fresh(),
        ]);
    }

    /**
     * Delete (soft) a pool.
     *
     * DELETE /api/admin/playoffs/pools/{poolNumber}
     */
    public function deletePool($poolNumber)
    {
        $this->requireSuperadmin();

        $pool = PlayoffPool::where('pool_number', $poolNumber)->firstOrFail();
        $pool->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pool deleted.',
        ]);
    }

    // ─── Helper ─────────────────────────────────────────────

    private function requireSuperadmin()
    {
        $user = Auth::user();
        if (!$user || $user->role_id !== 1) {
            abort(403, 'Superadmin access required.');
        }
    }

    private function requireAdmin()
    {
        $user = Auth::user();
        if (!$user || ($user->role_id !== 1 && $user->role_id !== 2 && !$user->is_playoff_admin)) {
            abort(403, 'Admin access required.');
        }
    }
}
