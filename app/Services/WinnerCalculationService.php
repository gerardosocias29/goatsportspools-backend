<?php

namespace App\Services;

use App\Models\SquaresPool;
use App\Models\SquaresPoolSquare;
use App\Models\SquaresPoolWinner;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class WinnerCalculationService
{
    /**
     * Calculate and record winners for a specific quarter
     *
     * @param int $poolId
     * @param int $quarter (1, 2, 3, 4)
     * @return array
     */
    public function calculateWinners($poolId, $quarter)
    {
        $pool = SquaresPool::with('game', 'gameRewardType')->findOrFail($poolId);
        $game = $pool->game;

        if (!$game) {
            throw new \Exception('Game not found for this pool');
        }

        // Check if numbers are assigned
        if (!$pool->numbers_assigned) {
            throw new \Exception('Numbers must be assigned before calculating winners');
        }

        // Get the scores for the specified quarter
        $scores = $this->getQuarterScores($game, $quarter);

        if ($scores === null) {
            throw new \Exception("Scores not available for quarter {$quarter}");
        }

        [$homeScore, $visitorScore] = $scores;

        // Get last digit of each score
        $homeLastDigit = $homeScore % 10;
        $visitorLastDigit = $visitorScore % 10;

        // Find which square wins
        $winningSquare = $this->findWinningSquare($pool, $homeLastDigit, $visitorLastDigit);

        if (!$winningSquare) {
            throw new \Exception('No winning square found');
        }

        if (!$winningSquare->player_id) {
            throw new \Exception('Winning square is not claimed by any player');
        }

        // Calculate prize amount
        $prizeAmount = $this->calculatePrizeAmount($pool, $quarter);

        DB::beginTransaction();
        try {
            // Check if winner already exists for this quarter
            $existingWinner = SquaresPoolWinner::where('pool_id', $poolId)
                ->where('quarter', $quarter)
                ->first();

            if ($existingWinner) {
                // Update existing winner
                $existingWinner->update([
                    'square_id' => $winningSquare->id,
                    'player_id' => $winningSquare->player_id,
                    'prize_amount' => $prizeAmount,
                    'home_score' => $homeScore,
                    'visitor_score' => $visitorScore,
                    'modify_user_id' => auth()->id(),
                    'modify_date' => now()->toDateString(),
                    'updated_at' => now(),
                ]);
                $winner = $existingWinner;
            } else {
                // Create new winner record
                $winner = SquaresPoolWinner::create([
                    'pool_id' => $poolId,
                    'square_id' => $winningSquare->id,
                    'player_id' => $winningSquare->player_id,
                    'quarter' => $quarter,
                    'prize_amount' => $prizeAmount,
                    'home_score' => $homeScore,
                    'visitor_score' => $visitorScore,
                    'create_user_id' => auth()->id() ?? 1,
                    'create_date' => now()->toDateString(),
                ]);
            }

            DB::commit();

            return [
                'status' => true,
                'winner' => $winner->load('player', 'square'),
                'winning_numbers' => [
                    'home' => $homeLastDigit,
                    'visitor' => $visitorLastDigit,
                ],
                'prize_amount' => $prizeAmount,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get scores for a specific quarter
     */
    protected function getQuarterScores(Game $game, int $quarter)
    {
        switch ($quarter) {
            case 1:
                return ($game->home_q1_score !== null && $game->visitor_q1_score !== null)
                    ? [$game->home_q1_score, $game->visitor_q1_score]
                    : null;
            case 2:
                return ($game->home_q2_score !== null && $game->visitor_q2_score !== null)
                    ? [$game->home_q2_score, $game->visitor_q2_score]
                    : null;
            case 3:
                return ($game->home_q3_score !== null && $game->visitor_q3_score !== null)
                    ? [$game->home_q3_score, $game->visitor_q3_score]
                    : null;
            case 4:
                return ($game->home_q4_score !== null && $game->visitor_q4_score !== null)
                    ? [$game->home_q4_score, $game->visitor_q4_score]
                    : null;
            default:
                return null;
        }
    }

    /**
     * Find the winning square based on last digits
     */
    protected function findWinningSquare(SquaresPool $pool, int $homeLastDigit, int $visitorLastDigit)
    {
        $xNumbers = $pool->x_numbers;
        $yNumbers = $pool->y_numbers;

        // Find X coordinate where x_number matches home last digit
        $xCoordinate = array_search($homeLastDigit, $xNumbers);

        // Find Y coordinate where y_number matches visitor last digit
        $yCoordinate = array_search($visitorLastDigit, $yNumbers);

        if ($xCoordinate === false || $yCoordinate === false) {
            return null;
        }

        // Find the square at these coordinates
        return SquaresPoolSquare::where('pool_id', $pool->id)
            ->where('x_coordinate', $xCoordinate)
            ->where('y_coordinate', $yCoordinate)
            ->with('player')
            ->first();
    }

    /**
     * Calculate prize amount for a quarter
     */
    protected function calculatePrizeAmount(SquaresPool $pool, int $quarter)
    {
        $totalPot = $pool->total_pot;

        // Get reward percentage for this quarter
        $rewardPercent = match($quarter) {
            1 => $pool->reward1_percent,
            2 => $pool->reward2_percent,
            3 => $pool->reward3_percent,
            4 => $pool->reward4_percent,
            default => 0,
        };

        return ($totalPot * $rewardPercent) / 100;
    }

    /**
     * Calculate winners for all quarters at once
     */
    public function calculateAllWinners($poolId)
    {
        $results = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            try {
                $result = $this->calculateWinners($poolId, $quarter);
                $results[] = $result;
            } catch (\Exception $e) {
                $results[] = [
                    'status' => false,
                    'quarter' => $quarter,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
