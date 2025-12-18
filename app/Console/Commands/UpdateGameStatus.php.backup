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
    protected $description = 'Update game status to "started" when game datetime is reached';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Find games that should be started but aren't marked as started yet
        // Game status: 'scheduled', 'started', 'completed', 'postponed', 'cancelled'
        $games = Game::where('game_datetime', '<=', $now)
            ->whereIn('game_status', ['scheduled', null, ''])
            ->get();

        if ($games->isEmpty()) {
            $this->info('No games to update.');
            Log::info('[UpdateGameStatus] No games to update at ' . $now->toDateTimeString());
            return 0;
        }

        $count = 0;
        foreach ($games as $game) {
            $game->game_status = 'started';
            $game->save();
            $count++;

            $this->info("Updated game #{$game->id} ({$game->game_nickname}) to 'started' status");
            Log::info("[UpdateGameStatus] Game #{$game->id} ({$game->game_nickname}) status updated to 'started'");
        }

        $this->info("Successfully updated {$count} game(s) to 'started' status.");
        Log::info("[UpdateGameStatus] Updated {$count} game(s) to 'started' status at " . $now->toDateTimeString());

        return 0;
    }
}
