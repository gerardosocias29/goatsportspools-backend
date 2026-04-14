<?php

namespace App\Http\Controllers;

use App\Models\PlayoffPool;
use App\Models\PlayoffPoolParticipant;
use App\Models\NbaPlayoff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PlayoffPoolController extends Controller
{
    /**
     * List playoff pools for the current user.
     * Superadmin sees all pools; regular users see pools they've joined.
     */
    public function index()
    {
        $user = Auth::user();
        $isSuperadmin = $user && $user->role_id === 1;

        $query = PlayoffPool::with([
            'admin:id,name,avatar,username',
            'playoff:id,year,name,status,current_round',
        ])->withCount('participants');

        if (!$isSuperadmin) {
            // Only pools user has joined
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $pools = $query->orderBy('created_at', 'desc')->get()->map(function ($pool) {
            return [
                'id' => $pool->id,
                'pool_number' => $pool->pool_number,
                'pool_name' => $pool->pool_name,
                'pool_description' => $pool->pool_description,
                'pool_status' => $pool->pool_status,
                'is_locked' => $pool->is_locked,
                'participants_count' => $pool->participants_count,
                'max_brackets_per_user' => $pool->max_brackets_per_user,
                'admin' => $pool->admin,
                'playoff' => $pool->playoff,
                'created_at' => $pool->created_at,
            ];
        });

        return response()->json(['status' => true, 'data' => $pools]);
    }

    /**
     * Show a playoff pool by ID or pool_number.
     */
    public function show($id)
    {
        $query = PlayoffPool::with([
            'admin:id,name,avatar,username',
            'playoff.teams.team:id,name,nickname,image_url,conference',
            'participants.user:id,name,avatar,username',
        ]);

        $pool = is_numeric($id)
            ? $query->findOrFail($id)
            : $query->where('pool_number', $id)->firstOrFail();

        $currentUserId = Auth::id();
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser && $currentUser->role_id === 1;
        $isPoolAdmin = $currentUserId && $pool->admin_id === $currentUserId;

        // Check if user is a participant
        $participant = null;
        if ($currentUserId) {
            $participant = $pool->participants()->where('user_id', $currentUserId)->first();
        }

        // Access: must be admin, superadmin, or a participant
        if (!$isPoolAdmin && !$isSuperAdmin && !$participant) {
            return response()->json([
                'status' => false,
                'message' => 'You must join this pool to view it',
            ], 403);
        }

        // Add has_password flag before hiding password
        $pool->has_password = !empty($pool->password);

        // Hide password from non-admins
        if (!$isPoolAdmin && !$isSuperAdmin) {
            unset($pool->password);
        }

        return response()->json([
            'status' => true,
            'data' => array_merge($pool->toArray(), [
                'is_locked' => $pool->is_locked,
                'participant' => $participant,
            ]),
        ]);
    }

    /**
     * Public lookup — find a playoff pool by pool_number (no auth required).
     */
    public function lookupByNumber($poolNumber)
    {
        $pool = PlayoffPool::where('pool_number', $poolNumber)->first();

        if (!$pool) {
            return response()->json(['status' => false, 'message' => 'Pool not found.'], 404);
        }

        // Check if authenticated user already joined
        $alreadyJoined = false;
        if (Auth::check()) {
            $alreadyJoined = PlayoffPoolParticipant::where('pool_id', $pool->id)
                ->where('user_id', Auth::id())
                ->exists();
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $pool->id,
                'pool_number' => $pool->pool_number,
                'pool_name' => $pool->pool_name,
                'pool_description' => $pool->pool_description,
                'pool_status' => $pool->pool_status,
                'has_password' => !empty($pool->password),
                'max_brackets_per_user' => $pool->max_brackets_per_user,
                'credit_cost_per_bracket' => $pool->credit_cost_per_bracket,
                'initial_credits' => $pool->initial_credits,
                'is_locked' => $pool->is_locked,
                'participant_count' => $pool->participants()->count(),
                'already_joined' => $alreadyJoined,
            ],
        ]);
    }

    /**
     * Get standings for a pool. Accessible to participants, pool admin, and superadmin.
     */
    public function standings($id)
    {
        $pool = is_numeric($id)
            ? PlayoffPool::findOrFail($id)
            : PlayoffPool::where('pool_number', $id)->firstOrFail();

        $currentUser = Auth::user();
        $isSuperadmin = $currentUser && $currentUser->role_id === 1;
        $isPoolAdmin = $currentUser && $pool->admin_id === $currentUser->id;
        $isParticipant = $currentUser && $pool->participants()->where('user_id', $currentUser->id)->exists();

        if (!$isSuperadmin && !$isPoolAdmin && !$isParticipant) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Per-bracket rows — only PAID brackets appear in standings
        $standings = \App\Models\PlayoffBracket::whereHas('participant', function ($q) use ($pool) {
                $q->where('pool_id', $pool->id);
            })
            ->where('is_paid', true)
            ->with(['participant.user:id,name,username,avatar', 'picks'])
            ->orderByDesc('total_points')
            ->get()
            ->map(function ($b) {
                $rounds = $b->picks->groupBy('round')->map(function ($roundPicks) {
                    return [
                        'correct' => $roundPicks->filter(fn($pick) => $pick->base_points > 0)->count(),
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
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'pool' => $pool->only(['id', 'pool_name', 'pool_number', 'pool_status', 'close_datetime', 'locked_at']),
                'standings' => $standings,
            ],
        ]);
    }

    /**
     * Join a playoff pool with pool_number + optional password.
     */
    public function join(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pool_number' => 'required|string|size:6',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $pool = PlayoffPool::where('pool_number', $request->pool_number)->first();

        if (!$pool) {
            return response()->json(['status' => false, 'message' => 'Pool not found.'], 404);
        }

        if ($pool->is_locked) {
            return response()->json(['status' => false, 'message' => 'This pool is locked.'], 403);
        }

        // Password check
        if ($pool->password && !Hash::check($request->password ?? '', $pool->password)) {
            return response()->json(['status' => false, 'message' => 'Incorrect pool password.'], 403);
        }

        $userId = Auth::id();

        // Already a participant?
        $existing = PlayoffPoolParticipant::where('pool_id', $pool->id)->where('user_id', $userId)->first();
        if ($existing) {
            return response()->json(['status' => false, 'message' => 'You have already joined this pool.'], 400);
        }

        $participant = PlayoffPoolParticipant::create([
            'pool_id' => $pool->id,
            'user_id' => $userId,
            'credits_available' => $pool->initial_credits,
            'brackets_count' => 0,
            'total_points' => 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Joined pool successfully.',
            'data' => $participant,
        ], 201);
    }
}
