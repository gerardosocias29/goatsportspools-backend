<?php

namespace App\Services;

use App\Models\SquaresPool;
use App\Mail\PoolClosedMail;
use App\Mail\NumbersAssignedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PoolEmailService
{
    /**
     * Send pool closed/numbers assigned emails to all players with squares
     *
     * @param SquaresPool $pool
     * @param array $xNumbers - X axis numbers [0-9]
     * @param array $yNumbers - Y axis numbers [0-9]
     * @return array - Results with counts
     */
    public function sendNumbersAssignedEmails(SquaresPool $pool, array $xNumbers, array $yNumbers): array
    {
        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            // Load pool with related data
            $pool->load(['admin', 'homeTeam', 'visitorTeam', 'squares.player']);

            $homeTeamName = $pool->homeTeam->name ?? 'Home Team';
            $visitorTeamName = $pool->visitorTeam->name ?? 'Visitor Team';
            $adminUsername = $pool->admin->username ?? $pool->admin->name ?? 'Pool Manager';
            $totalSquaresFilled = $pool->squares->whereNotNull('player_id')->count();
            $poolUrl = env('APP_FRONTEND_URL', env('APP_URL')) . '/v2/squares/pool/' . $pool->id;

            // Determine logo URL - use alpha if localhost
            $frontendUrl = env('APP_FRONTEND_URL', env('APP_URL'));
            $logoUrl = str_contains($frontendUrl, 'localhost')
                ? 'https://alpha.goatsportspools.com/img/v2_logo.png'
                : $frontendUrl . '/img/v2_logo.png';

            // Get all players who have claimed squares
            $playersWithSquares = $pool->squares
                ->whereNotNull('player_id')
                ->groupBy('player_id');

            foreach ($playersWithSquares as $playerId => $playerSquares) {
                $player = $playerSquares->first()->player;

                if (!$player || !$player->email) {
                    continue;
                }

                try {
                    // Get player's squares with assigned numbers
                    $squaresData = $playerSquares->map(function ($square) use ($xNumbers, $yNumbers) {
                        return [
                            'x_number' => $xNumbers[$square->x_coordinate] ?? $square->x_number,
                            'y_number' => $yNumbers[$square->y_coordinate] ?? $square->y_number,
                            'x_coordinate' => $square->x_coordinate,
                            'y_coordinate' => $square->y_coordinate,
                        ];
                    })->values()->toArray();

                    // Get first square's numbers for example
                    $exampleX = $squaresData[0]['x_number'] ?? 0;
                    $exampleY = $squaresData[0]['y_number'] ?? 0;

                    $emailData = [
                        'pool_name' => $pool->pool_name,
                        'admin_username' => $adminUsername,
                        'player_name' => $player->name ?? $player->username ?? 'Player',
                        'player_email' => $player->email,
                        'home_team' => $homeTeamName,
                        'visitor_team' => $visitorTeamName,
                        'squares_count' => count($squaresData),
                        'player_squares' => $squaresData,
                        'total_squares_filled' => $totalSquaresFilled,
                        'pool_url' => $poolUrl,
                        'example_x' => $exampleX,
                        'example_y' => $exampleY,
                        'numbers_assigned' => true,
                        'x_numbers' => $xNumbers, // Full X axis numbers for grid display
                        'y_numbers' => $yNumbers, // Full Y axis numbers for grid display
                        'logo_url' => $logoUrl,
                    ];

                    // Send email using NumbersAssignedMail
                    Mail::to($player->email)->send(new NumbersAssignedMail($emailData));
                    $sentCount++;

                    Log::info("Sent numbers assigned email to {$player->email} for pool #{$pool->pool_number}");

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Failed to send to {$player->email}: " . $e->getMessage();
                    Log::error("Failed to send email to {$player->email}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send pool numbers assigned emails: ' . $e->getMessage(), [
                'pool_id' => $pool->id,
                'error' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'sent' => $sentCount,
                'failed' => $failedCount,
                'errors' => array_merge($errors, [$e->getMessage()])
            ];
        }

        return [
            'success' => $failedCount === 0,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Send pool closed notification emails (when pool closes but numbers NOT yet assigned)
     *
     * @param SquaresPool $pool
     * @return array - Results with counts
     */
    public function sendPoolClosedEmails(SquaresPool $pool): array
    {
        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            // Load pool with related data
            $pool->load(['admin', 'homeTeam', 'visitorTeam', 'squares.player']);

            $claimedSquaresCount = $pool->squares->whereNotNull('player_id')->count();

            Log::info('sendPoolClosedEmails: Starting for pool #' . $pool->id, [
                'pool_name' => $pool->pool_name,
                'squares_count' => $pool->squares->count(),
                'claimed_squares' => $claimedSquaresCount,
            ]);

            if ($claimedSquaresCount === 0) {
                Log::info('sendPoolClosedEmails: No claimed squares - no emails to send');
                return ['success' => true, 'sent' => 0, 'failed' => 0, 'errors' => []];
            }

            $homeTeamName = $pool->homeTeam->name ?? 'Home Team';
            $visitorTeamName = $pool->visitorTeam->name ?? 'Visitor Team';
            $adminUsername = $pool->admin->username ?? $pool->admin->name ?? 'Pool Manager';
            $totalSquaresFilled = $claimedSquaresCount;
            $poolUrl = env('APP_FRONTEND_URL', env('APP_URL')) . '/v2/squares/pool/' . $pool->id;

            // Determine logo URL - use alpha if localhost
            $frontendUrl = env('APP_FRONTEND_URL', env('APP_URL'));
            $logoUrl = str_contains($frontendUrl, 'localhost')
                ? 'https://alpha.goatsportspools.com/img/v2_logo.png'
                : $frontendUrl . '/img/v2_logo.png';

            // Get all players who have claimed squares
            $playersWithSquares = $pool->squares
                ->whereNotNull('player_id')
                ->groupBy('player_id');

            foreach ($playersWithSquares as $playerId => $playerSquares) {
                $player = $playerSquares->first()->player;

                if (!$player || !$player->email) {
                    continue;
                }

                try {
                    // Get player's squares with their grid coordinates (numbers not yet assigned)
                    $squaresData = $playerSquares->map(function ($square) {
                        return [
                            'x_coordinate' => $square->x_coordinate,
                            'y_coordinate' => $square->y_coordinate,
                            // Show coordinates as position labels
                            'position_label' => '[' . $square->x_coordinate . ',' . $square->y_coordinate . ']',
                        ];
                    })->values()->toArray();

                    $emailData = [
                        'pool_name' => $pool->pool_name,
                        'admin_username' => $adminUsername,
                        'player_name' => $player->name ?? $player->username ?? 'Player',
                        'player_email' => $player->email,
                        'home_team' => $homeTeamName,
                        'visitor_team' => $visitorTeamName,
                        'squares_count' => count($squaresData),
                        'player_squares' => $squaresData,
                        'total_squares_filled' => $totalSquaresFilled,
                        'pool_url' => $poolUrl,
                        'numbers_assigned' => false, // Numbers not assigned yet - show grid positions only
                        'logo_url' => $logoUrl,
                    ];

                    // Send email
                    Mail::to($player->email)->send(new PoolClosedMail($emailData));
                    $sentCount++;

                    Log::info("Sent pool closed email to {$player->email} for pool #{$pool->pool_number}");

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Failed to send to {$player->email}: " . $e->getMessage();
                    Log::error("Failed to send pool closed email to {$player->email}: " . $e->getMessage());
                }
            }

            Log::info('sendPoolClosedEmails: Completed for pool #' . $pool->id, [
                'sent' => $sentCount,
                'failed' => $failedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send pool closed emails: ' . $e->getMessage(), [
                'pool_id' => $pool->id,
                'error' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'sent' => $sentCount,
                'failed' => $failedCount,
                'errors' => array_merge($errors, [$e->getMessage()])
            ];
        }

        return [
            'success' => $failedCount === 0,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }
}
