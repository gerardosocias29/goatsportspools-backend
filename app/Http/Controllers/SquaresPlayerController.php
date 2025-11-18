<?php

namespace App\Http\Controllers;

use App\Models\SquaresPool;
use App\Models\SquaresPoolSquare;
use App\Models\SquaresPoolPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SquaresPlayerController extends Controller
{
    /**
     * Join a pool with pool number and password
     * POST /api/squares-pools/join
     */
    public function joinPool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pool_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find pool by pool number
        $pool = SquaresPool::where('pool_number', $request->pool_number)->first();

        if (!$pool) {
            return response()->json([
                'status' => false,
                'message' => 'Pool not found'
            ], 404);
        }

        // Check password
        if (!Hash::check($request->password, $pool->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect password'
            ], 401);
        }

        // Check if pool is open
        if ($pool->pool_status !== 'open') {
            return response()->json([
                'status' => false,
                'message' => 'Pool is not open for joining'
            ], 400);
        }

        // Check if already joined
        $existingPlayer = SquaresPoolPlayer::where('pool_id', $pool->id)
            ->where('player_id', auth()->id())
            ->first();

        if ($existingPlayer) {
            return response()->json([
                'status' => true,
                'message' => 'Already joined this pool',
                'data' => $pool->load(['game', 'homeTeam', 'visitorTeam'])
            ]);
        }

        // Create player record
        // For CREDIT type pools, give initial credits based on entry fee or default to 10
        $initialCredits = 0;
        if ($pool->player_pool_type === 'CREDIT') {
            // Give credits based on entry fee (e.g., $1 = 1 credit) or default to 10 credits
            $initialCredits = $pool->entry_fee > 0 ? (int)$pool->entry_fee : 10;
        }

        $player = SquaresPoolPlayer::create([
            'pool_id' => $pool->id,
            'player_id' => auth()->id(),
            'credits_available' => $initialCredits,
            'squares_count' => 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Successfully joined pool',
            'data' => $pool->load(['game', 'homeTeam', 'visitorTeam'])
        ]);
    }

    /**
     * Get pool info by pool number (for public viewing before joining)
     * GET /api/squares-pools/by-number/{poolNumber}
     */
    public function getPoolByNumber($poolNumber)
    {
        $pool = SquaresPool::with(['game', 'homeTeam', 'visitorTeam', 'admin'])
            ->where('pool_number', $poolNumber)
            ->first();

        if (!$pool) {
            return response()->json([
                'status' => false,
                'message' => 'Pool not found'
            ], 404);
        }

        // Return limited info (don't expose password)
        return response()->json([
            'status' => true,
            'data' => [
                'id' => $pool->id,
                'pool_number' => $pool->pool_number,
                'pool_name' => $pool->pool_name,
                'pool_type' => $pool->pool_type,
                'player_pool_type' => $pool->player_pool_type,
                'entry_fee' => $pool->entry_fee,
                'max_squares_per_player' => $pool->max_squares_per_player,
                'credit_cost' => $pool->credit_cost,
                'pool_status' => $pool->pool_status,
                'game' => $pool->game,
                'home_team' => $pool->homeTeam,
                'visitor_team' => $pool->visitorTeam,
                'admin' => $pool->admin,
                'claimed_squares' => $pool->claimed_squares_count,
                'available_squares' => $pool->available_squares_count,
                'total_pot' => $pool->total_pot,
            ]
        ]);
    }

    /**
     * Get squares for a pool
     * GET /api/squares-pools/{poolId}/squares
     */
    public function getSquares($poolId)
    {
        $pool = SquaresPool::findOrFail($poolId);

        // Check if user is a member of this pool
        $isMember = SquaresPoolPlayer::where('pool_id', $poolId)
            ->where('player_id', auth()->id())
            ->exists();

        if (!$isMember && $pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You must join this pool first'
            ], 403);
        }

        $squares = SquaresPoolSquare::where('pool_id', $poolId)
            ->with('player')
            ->get();

        return response()->json($squares);
    }

    /**
     * Get my squares in a pool
     * GET /api/squares-pools/{poolId}/my-squares
     */
    public function getMySquares($poolId)
    {
        $squares = SquaresPoolSquare::where('pool_id', $poolId)
            ->where('player_id', auth()->id())
            ->with('player')
            ->get();

        return response()->json($squares);
    }

    /**
     * Claim a square
     * POST /api/squares-pools/{poolId}/claim-square
     */
    public function claimSquare(Request $request, $poolId)
    {
        $validator = Validator::make($request->all(), [
            'x_coordinate' => 'required|integer|min:0|max:9',
            'y_coordinate' => 'required|integer|min:0|max:9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pool = SquaresPool::findOrFail($poolId);

        // Check if pool is open
        if ($pool->pool_status !== 'open') {
            return response()->json([
                'status' => false,
                'message' => 'Pool is not open for claiming squares'
            ], 400);
        }

        // Check if user is a member
        $playerRecord = SquaresPoolPlayer::where('pool_id', $poolId)
            ->where('player_id', auth()->id())
            ->first();

        if (!$playerRecord) {
            return response()->json([
                'status' => false,
                'message' => 'You must join this pool first'
            ], 403);
        }

        // Check max squares limit
        if ($pool->max_squares_per_player && $playerRecord->squares_count >= $pool->max_squares_per_player) {
            return response()->json([
                'status' => false,
                'message' => "You have reached the maximum of {$pool->max_squares_per_player} squares per player"
            ], 400);
        }

        // For CREDIT type, check credits
        if ($pool->player_pool_type === 'CREDIT') {
            if ($playerRecord->credits_available < $pool->credit_cost) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient credits'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Find the square
            $square = SquaresPoolSquare::where('pool_id', $poolId)
                ->where('x_coordinate', $request->x_coordinate)
                ->where('y_coordinate', $request->y_coordinate)
                ->firstOrFail();

            // Check if already claimed
            if ($square->player_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Square already claimed'
                ], 400);
            }

            // Claim the square
            $square->update([
                'player_id' => auth()->id(),
                'claimed_at' => now(),
            ]);

            // Update player record
            if ($pool->player_pool_type === 'CREDIT') {
                $playerRecord->decrement('credits_available', $pool->credit_cost);
            }
            $playerRecord->increment('squares_count');

            // Check if pool is now full
            $claimedCount = SquaresPoolSquare::where('pool_id', $poolId)
                ->whereNotNull('player_id')
                ->count();

            if ($claimedCount === 100) {
                $pool->update(['pool_status' => 'closed']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Square claimed successfully',
                'data' => $square->fresh()->load('player')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to claim square',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Release a square
     * POST /api/squares-pools/{poolId}/release-square
     */
    public function releaseSquare(Request $request, $poolId)
    {
        $validator = Validator::make($request->all(), [
            'square_id' => 'required|exists:squares_pool_squares,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pool = SquaresPool::findOrFail($poolId);
        $square = SquaresPoolSquare::findOrFail($request->square_id);

        // Check ownership
        if ($square->player_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'This is not your square'
            ], 403);
        }

        // Check if pool allows releasing
        if ($pool->pool_status !== 'open') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot release squares after pool is closed'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Release the square
            $square->update([
                'player_id' => null,
                'claimed_at' => null,
            ]);

            // Update player record
            $playerRecord = SquaresPoolPlayer::where('pool_id', $poolId)
                ->where('player_id', auth()->id())
                ->first();

            if ($playerRecord) {
                if ($pool->player_pool_type === 'CREDIT') {
                    $playerRecord->increment('credits_available', $pool->credit_cost);
                }
                $playerRecord->decrement('squares_count');
            }

            // If pool was closed/full, reopen it
            if ($pool->pool_status === 'closed') {
                $pool->update(['pool_status' => 'open']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Square released successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to release square',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get my joined pools
     * GET /api/squares-pools/my-joined
     */
    public function getMyJoinedPools()
    {
        $playerRecords = SquaresPoolPlayer::where('player_id', auth()->id())
            ->with(['pool.game', 'pool.homeTeam', 'pool.visitorTeam'])
            ->get();

        $pools = $playerRecords->map(function($record) {
            $pool = $record->pool;
            $pool->my_squares_count = $record->squares_count;
            $pool->my_credits = $record->credits_available;
            $pool->total_pot = $pool->total_pot;
            $pool->claimed_squares = $pool->claimed_squares_count;
            return $pool;
        });

        return response()->json($pools);
    }

    /**
     * Add credits to a player (admin only)
     * POST /api/squares-pools/{poolId}/add-credits
     */
    public function addCredits(Request $request, $poolId)
    {
        $pool = SquaresPool::findOrFail($poolId);

        // Check if user is admin
        if ($pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'player_id' => 'required|exists:users,id',
            'credits' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $playerRecord = SquaresPoolPlayer::where('pool_id', $poolId)
            ->where('player_id', $request->player_id)
            ->first();

        if (!$playerRecord) {
            return response()->json([
                'status' => false,
                'message' => 'Player not in this pool'
            ], 404);
        }

        $playerRecord->increment('credits_available', $request->credits);

        return response()->json([
            'status' => true,
            'message' => 'Credits added successfully',
            'data' => $playerRecord->fresh()
        ]);
    }
}
