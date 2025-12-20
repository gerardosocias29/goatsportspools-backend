<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateGameStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squares:update-game-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update game status to started/ended based on game datetime and scores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $startedCount = 0;
        $endedCount = 0;

        // 1. Find games that should be started but aren't marked as started yet
        // Check for: 'scheduled', 'not_started', null, or empty string
        $gamesToStart = Game::where('game_datetime', '<=', $now)
            ->where(function ($query) {
                $query->whereIn('game_status', ['scheduled', 'not_started'])
                      ->orWhereNull('game_status')
                      ->orWhere('game_status', '');
            })
            ->get();

        foreach ($gamesToStart as $game) {
            $game->game_status = 'started';
            $game->save();
            $startedCount++;

            $this->info("Updated game #{$game->id} ({$game->game_nickname}) to 'started' status");
            Log::info("[UpdateGameStatus] Game #{$game->id} ({$game->game_nickname}) status updated to 'started'");
        }

        // 2. Find games that have final scores and should be marked as ended
        // The database has: q1_home, q1_visitor, half_home, half_visitor, q3_home, q3_visitor, final_home, final_visitor
        $gamesToEnd = Game::whereIn('game_status', ['started', 'scheduled'])
            ->where(function ($query) {
                // Check if Final score exists (both home and visitor)
                $query->where(function ($q) {
                    $q->whereNotNull('final_home')
                      ->whereNotNull('final_visitor');
                })
                // OR check if all quarters have scores
                ->orWhere(function ($q) {
                    $q->whereNotNull('q1_home')
                      ->whereNotNull('q1_visitor')
                      ->whereNotNull('half_home')
                      ->whereNotNull('half_visitor')
                      ->whereNotNull('q3_home')
                      ->whereNotNull('q3_visitor');
                });
            })
            ->get();

        foreach ($gamesToEnd as $game) {
            $game->game_status = 'ended';
            $game->save();
            $endedCount++;

            $this->info("Updated game #{$game->id} ({$game->game_nickname}) to 'ended' status");
            Log::info("[UpdateGameStatus] Game #{$game->id} ({$game->game_nickname}) status updated to 'ended'");
        }

        if ($startedCount === 0 && $endedCount === 0) {
            $this->info('No games to update.');
            Log::info('[UpdateGameStatus] No games to update at ' . $now->toDateTimeString());
        } else {
            $this->info("Successfully updated {$startedCount} game(s) to 'started' and {$endedCount} game(s) to 'ended' status.");
            Log::info("[UpdateGameStatus] Updated {$startedCount} to 'started' and {$endedCount} to 'ended' at " . $now->toDateTimeString());
        }

        return 0;
    }
}
