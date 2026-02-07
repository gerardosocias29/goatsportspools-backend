<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SquaresPool;
use App\Models\SquaresPoolPlayer;
use App\Models\SquaresPoolSquare;
use App\Models\SquaresPoolWinner;
use App\Models\Auction;
use App\Models\AuctionItemBid;
use App\Models\League;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard stats for the authenticated user.
     * Returns: pools joined, squares owned, total winnings, active auctions
     */
    public function stats(Request $request)
    {
        $userId = Auth::id();

        // Pools the user has joined
        $poolsJoined = SquaresPoolPlayer::where('player_id', $userId)->count();

        // Total squares owned across all pools
        $squaresOwned = SquaresPoolSquare::where('player_id', $userId)->count();

        // Total winnings from all pools
        $totalWinnings = SquaresPoolWinner::where('player_id', $userId)
            ->sum('prize_amount');

        // Active pools the user is in
        $activePools = SquaresPoolPlayer::where('player_id', $userId)
            ->whereHas('pool', function ($query) {
                $query->whereIn('pool_status', ['open', 'closed']);
            })
            ->count();

        // Active auctions count
        $activeAuctions = 0;
        try {
            $activeAuctions = Auction::whereIn('status', ['upcoming', 'live'])->count();
        } catch (\Exception $e) {
            // Auction table may not exist yet
        }

        return response()->json([
            'status' => true,
            'data' => [
                'pools_joined' => $poolsJoined,
                'squares_owned' => $squaresOwned,
                'total_winnings' => (float) $totalWinnings,
                'active_pools' => $activePools,
                'active_auctions' => $activeAuctions,
            ],
        ]);
    }

    /**
     * Get user winnings breakdown.
     * Returns winnings grouped by pool with pool details.
     */
    public function winnings(Request $request)
    {
        $userId = Auth::id();

        $winnings = SquaresPoolWinner::where('player_id', $userId)
            ->with(['pool:id,pool_name,pool_number,pool_status', 'square:id,x_number,y_number'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($winner) {
                return [
                    'id' => $winner->id,
                    'pool_id' => $winner->pool_id,
                    'pool_name' => $winner->pool->pool_name ?? 'Unknown Pool',
                    'pool_number' => $winner->pool->pool_number ?? null,
                    'quarter' => $winner->quarter,
                    'prize_amount' => (float) $winner->prize_amount,
                    'home_score' => $winner->home_score,
                    'visitor_score' => $winner->visitor_score,
                    'x_number' => $winner->square->x_number ?? null,
                    'y_number' => $winner->square->y_number ?? null,
                    'won_at' => $winner->created_at,
                ];
            });

        $totalWinnings = $winnings->sum('prize_amount');

        return response()->json([
            'status' => true,
            'data' => [
                'total' => $totalWinnings,
                'winnings' => $winnings,
            ],
        ]);
    }

    /**
     * Get recent activity for the dashboard feed.
     * Combines recent pool joins, square claims, and wins.
     */
    public function activity(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->get('limit', 20);
        $activities = [];

        // Recent pool joins
        $recentJoins = SquaresPoolPlayer::where('player_id', $userId)
            ->with('pool:id,pool_name,pool_number')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($recentJoins as $join) {
            $activities[] = [
                'type' => 'pool_join',
                'message' => 'Joined pool "' . ($join->pool->pool_name ?? 'Unknown') . '"',
                'pool_id' => $join->pool_id,
                'pool_name' => $join->pool->pool_name ?? null,
                'timestamp' => $join->created_at,
            ];
        }

        // Recent wins
        $recentWins = SquaresPoolWinner::where('player_id', $userId)
            ->with('pool:id,pool_name,pool_number')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($recentWins as $win) {
            $activities[] = [
                'type' => 'win',
                'message' => 'Won $' . number_format($win->prize_amount, 2) . ' in Q' . $win->quarter . ' of "' . ($win->pool->pool_name ?? 'Unknown') . '"',
                'pool_id' => $win->pool_id,
                'pool_name' => $win->pool->pool_name ?? null,
                'prize_amount' => (float) $win->prize_amount,
                'quarter' => $win->quarter,
                'timestamp' => $win->created_at,
            ];
        }

        // Sort by timestamp descending and limit
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        $activities = array_slice($activities, 0, $limit);

        return response()->json([
            'status' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get user's active pools with details for dashboard widget.
     */
    public function activePools(Request $request)
    {
        $userId = Auth::id();

        $pools = SquaresPoolPlayer::where('player_id', $userId)
            ->with(['pool' => function ($query) {
                $query->select('id', 'pool_name', 'pool_number', 'pool_status', 'entry_fee', 'game_id', 'home_team_id', 'visitor_team_id', 'admin_id')
                    ->with(['homeTeam:id,name,logo', 'visitorTeam:id,name,logo']);
            }])
            ->get()
            ->filter(function ($player) {
                return $player->pool !== null;
            })
            ->map(function ($player) use ($userId) {
                $pool = $player->pool;
                $totalSquares = SquaresPoolSquare::where('pool_id', $pool->id)->count();
                $claimedSquares = SquaresPoolSquare::where('pool_id', $pool->id)
                    ->whereNotNull('player_id')
                    ->count();
                $mySquares = SquaresPoolSquare::where('pool_id', $pool->id)
                    ->where('player_id', $userId)
                    ->count();

                return [
                    'pool_id' => $pool->id,
                    'pool_name' => $pool->pool_name,
                    'pool_number' => $pool->pool_number,
                    'pool_status' => $pool->pool_status,
                    'entry_fee' => $pool->entry_fee,
                    'total_squares' => $totalSquares,
                    'claimed_squares' => $claimedSquares,
                    'my_squares' => $mySquares,
                    'fill_percentage' => $totalSquares > 0 ? round(($claimedSquares / $totalSquares) * 100) : 0,
                    'home_team' => $pool->homeTeam,
                    'visitor_team' => $pool->visitorTeam,
                    'credits_available' => $player->credits_available,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $pools,
        ]);
    }
}
