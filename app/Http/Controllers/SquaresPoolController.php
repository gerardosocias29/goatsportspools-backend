<?php

namespace App\Http\Controllers;

use App\Models\SquaresPool;
use App\Models\SquaresPoolSquare;
use App\Models\SquaresPoolPlayer;
use App\Models\Game;
use App\Services\WinnerCalculationService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SquaresPoolController extends Controller
{
    /**
     * Get all pools (with optional filters)
     * GET /api/squares-pools
     */
    public function index(Request $request)
    {
        $query = SquaresPool::with(['game', 'homeTeam', 'visitorTeam', 'admin'])
            ->whereNull('deleted_at');

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('pool_status', 'open');
            } elseif ($status === 'completed') {
                $query->where('pool_status', 'completed');
            }
        }

        // Filter by admin (my pools)
        if ($request->has('my_pools') && $request->get('my_pools') === 'true') {
            $query->where('admin_id', auth()->id());
        }

        $pools = $query->orderBy('created_at', 'desc')->get();

        // Append computed attributes
        $pools->each(function($pool) {
            $pool->total_pot = $pool->total_pot;
            $pool->claimed_squares = $pool->claimed_squares_count;
            $pool->available_squares = $pool->available_squares_count;
        });

        return response()->json($pools);
    }

    /**
     * Get single pool details
     * GET /api/squares-pools/{id}
     */
    public function show($id)
    {
        $pool = SquaresPool::with([
            'game',
            'homeTeam',
            'visitorTeam',
            'admin',
            'squares.player',
            'players.player',
            'winners.player'
        ])->findOrFail($id);

        $pool->total_pot = $pool->total_pot;
        $pool->claimed_squares = $pool->claimed_squares_count;
        $pool->available_squares = $pool->available_squares_count;

        return response()->json($pool);
    }

    /**
     * Create a new pool
     * POST /api/squares-pools
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
            'pool_name' => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'pool_type' => 'required|in:A,B,C,D',
            'player_pool_type' => 'required|in:OPEN,CREDIT',
            'home_team_id' => 'required|exists:teams,id',
            'visitor_team_id' => 'required|exists:teams,id',
            'entry_fee' => 'required|numeric|min:0',
            'max_squares_per_player' => 'nullable|integer|min:1|max:100',
            'credit_cost' => 'required_if:player_pool_type,CREDIT|nullable|integer|min:1|max:10',
            'close_datetime' => 'required_if:pool_type,B,C|nullable|date',
            'number_assign_datetime' => 'required_if:pool_type,B|nullable|date',
            'reward1_percent' => 'required|numeric|min:0|max:100',
            'reward2_percent' => 'required|numeric|min:0|max:100',
            'reward3_percent' => 'required|numeric|min:0|max:100',
            'reward4_percent' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate reward percentages add up to 100
        $totalReward = $request->reward1_percent + $request->reward2_percent +
                       $request->reward3_percent + $request->reward4_percent;
        if ($totalReward != 100) {
            return response()->json([
                'status' => false,
                'message' => 'Reward percentages must add up to 100%'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate unique pool number
            $poolNumber = SquaresPool::generatePoolNumber();

            // Determine grid fee type (first 10 grids are free)
            $adminPoolsCount = SquaresPool::where('admin_id', auth()->id())->count();
            $gridFeeType = 'Free';
            if ($adminPoolsCount >= 60) {
                $gridFeeType = 'Standard';
            } elseif ($adminPoolsCount >= 10) {
                $gridFeeType = $adminPoolsCount < 20 ? 'Min1' : 'Min2';
            }

            // Generate QR code
            $qrCodeService = new QRCodeService();
            $qrCodeUrl = $qrCodeService->generatePoolQRCode($poolNumber, $request->pool_name);

            // Create pool
            $pool = SquaresPool::create([
                'admin_id' => auth()->id(),
                'game_id' => $request->game_id,
                'pool_number' => $poolNumber,
                'password' => Hash::make($request->password),
                'pool_name' => $request->pool_name,
                'pool_description' => $request->pool_description,
                'pool_type' => $request->pool_type,
                'player_pool_type' => $request->player_pool_type,
                'reward_type' => $request->reward_type ?? 'CreditsRewards',
                'grid_fee_type' => $gridFeeType,
                'admin_grid_number' => $adminPoolsCount + 1,
                'home_team_id' => $request->home_team_id,
                'visitor_team_id' => $request->visitor_team_id,
                'entry_fee' => $request->entry_fee,
                'max_squares_per_player' => $request->max_squares_per_player,
                'credit_cost' => $request->credit_cost,
                'close_datetime' => $request->close_datetime,
                'number_assign_datetime' => $request->number_assign_datetime,
                'pool_status' => 'open',
                'qr_code_url' => $qrCodeUrl,
                'game_reward_type_id' => $request->game_reward_type_id ?? 1,
                'reward1_percent' => $request->reward1_percent,
                'reward2_percent' => $request->reward2_percent,
                'reward3_percent' => $request->reward3_percent,
                'reward4_percent' => $request->reward4_percent,
                'create_user_id' => auth()->id(),
                'create_date' => now()->toDateString(),
            ]);

            // Create 100 squares (10x10 grid)
            $squares = [];
            for ($y = 0; $y < 10; $y++) {
                for ($x = 0; $x < 10; $x++) {
                    $squares[] = [
                        'pool_id' => $pool->id,
                        'x_coordinate' => $x,
                        'y_coordinate' => $y,
                        'create_user_id' => auth()->id(),
                        'create_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            SquaresPoolSquare::insert($squares);

            // For Type A, assign numbers immediately
            if ($request->pool_type === 'A') {
                $this->assignNumbers($pool->id, 'random');
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pool created successfully',
                'data' => $pool->load(['game', 'homeTeam', 'visitorTeam']),
                'pool_number' => $poolNumber,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create pool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign numbers to pool (Type A immediate, Type B auto, Type C manual)
     * POST /api/squares-pools/{id}/assign-numbers
     */
    public function assignNumbers($poolId, $mode = 'random', $xNumbers = null, $yNumbers = null)
    {
        $pool = SquaresPool::findOrFail($poolId);

        // Check authorization
        if ($pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if already assigned
        if ($pool->numbers_assigned) {
            return response()->json([
                'status' => false,
                'message' => 'Numbers already assigned'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($mode === 'random') {
                // Generate random numbers 0-9
                $xNumbers = collect(range(0, 9))->shuffle()->values()->toArray();
                $yNumbers = collect(range(0, 9))->shuffle()->values()->toArray();
            } elseif ($mode === 'manual') {
                // Validate manual numbers
                if (!$xNumbers || !$yNumbers) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Manual numbers required'
                    ], 400);
                }

                // Ensure arrays have exactly 10 unique numbers from 0-9
                if (count($xNumbers) !== 10 || count($yNumbers) !== 10 ||
                    count(array_unique($xNumbers)) !== 10 || count(array_unique($yNumbers)) !== 10) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Numbers must be 10 unique values from 0-9'
                    ], 400);
                }
            }

            // Update pool with numbers
            $pool->update([
                'x_numbers' => $xNumbers,
                'y_numbers' => $yNumbers,
                'numbers_assigned' => true,
            ]);

            // Update all squares with their assigned numbers
            $squares = SquaresPoolSquare::where('pool_id', $poolId)->get();
            foreach ($squares as $square) {
                $square->update([
                    'x_number' => $xNumbers[$square->x_coordinate],
                    'y_number' => $yNumbers[$square->y_coordinate],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Numbers assigned successfully',
                'data' => [
                    'x_numbers' => $xNumbers,
                    'y_numbers' => $yNumbers,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to assign numbers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual number assignment endpoint
     * POST /api/squares-pools/{id}/assign-numbers-manual
     */
    public function assignNumbersManual(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'x_numbers' => 'required|array|size:10',
            'x_numbers.*' => 'required|integer|min:0|max:9',
            'y_numbers' => 'required|array|size:10',
            'y_numbers.*' => 'required|integer|min:0|max:9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->assignNumbers($id, 'manual', $request->x_numbers, $request->y_numbers);
    }

    /**
     * Close a pool (prevent new square claims)
     * POST /api/squares-pools/{id}/close
     */
    public function closePool($id)
    {
        $pool = SquaresPool::findOrFail($id);

        if ($pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $pool->update(['pool_status' => 'closed']);

        return response()->json([
            'status' => true,
            'message' => 'Pool closed successfully'
        ]);
    }

    /**
     * Delete a pool
     * DELETE /api/squares-pools/{id}
     */
    public function destroy($id)
    {
        $pool = SquaresPool::findOrFail($id);

        if ($pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $pool->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pool deleted successfully'
        ]);
    }

    /**
     * Calculate winners for a specific quarter
     * POST /api/squares-pools/{id}/calculate-winners
     */
    public function calculateWinners(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pool = SquaresPool::findOrFail($id);

        // Check authorization
        if ($pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $winnerService = new WinnerCalculationService();
            $result = $winnerService->calculateWinners($id, $request->quarter);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calculate winners for all quarters at once
     * POST /api/squares-pools/{id}/calculate-all-winners
     */
    public function calculateAllWinners($id)
    {
        $pool = SquaresPool::findOrFail($id);

        // Check authorization
        if ($pool->admin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $winnerService = new WinnerCalculationService();
            $results = $winnerService->calculateAllWinners($id);

            return response()->json([
                'status' => true,
                'message' => 'Winners calculated for all quarters',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get winners for a pool
     * GET /api/squares-pools/{id}/winners
     */
    public function getWinners($id)
    {
        $pool = SquaresPool::with(['winners.player', 'winners.square'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $pool->winners
        ]);
    }
}
