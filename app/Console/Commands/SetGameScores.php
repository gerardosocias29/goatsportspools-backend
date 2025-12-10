<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;

class SetGameScores extends Command
{
    protected $signature = 'game:set-scores {game_id} {--q1_home=} {--q1_visitor=} {--q2_home=} {--q2_visitor=} {--q3_home=} {--q3_visitor=} {--q4_home=} {--q4_visitor=}';
    protected $description = 'Set quarter scores for a game';

    public function handle()
    {
        $gameId = $this->argument('game_id');
        $game = Game::find($gameId);

        if (!$game) {
            $this->error("Game {$gameId} not found");
            return 1;
        }

        $homeTeamName = $game->home_team ? $game->home_team->name : 'Home';
        $visitorTeamName = $game->visitor_team ? $game->visitor_team->name : 'Visitor';
        $this->info("Updating game {$gameId}: {$homeTeamName} vs {$visitorTeamName}");

        $updates = [];

        if ($this->option('q1_home') !== null) {
            $updates['home_q1_score'] = (int) $this->option('q1_home');
        }
        if ($this->option('q1_visitor') !== null) {
            $updates['visitor_q1_score'] = (int) $this->option('q1_visitor');
        }
        if ($this->option('q2_home') !== null) {
            $updates['home_q2_score'] = (int) $this->option('q2_home');
        }
        if ($this->option('q2_visitor') !== null) {
            $updates['visitor_q2_score'] = (int) $this->option('q2_visitor');
        }
        if ($this->option('q3_home') !== null) {
            $updates['home_q3_score'] = (int) $this->option('q3_home');
        }
        if ($this->option('q3_visitor') !== null) {
            $updates['visitor_q3_score'] = (int) $this->option('q3_visitor');
        }
        if ($this->option('q4_home') !== null) {
            $updates['home_q4_score'] = (int) $this->option('q4_home');
        }
        if ($this->option('q4_visitor') !== null) {
            $updates['visitor_q4_score'] = (int) $this->option('q4_visitor');
        }

        if (empty($updates)) {
            $this->warn("No scores provided");
            return 1;
        }

        // Calculate cumulative scores
        $homeTotal = 0;
        $visitorTotal = 0;

        if (isset($updates['home_q1_score'])) $homeTotal += $updates['home_q1_score'];
        elseif ($game->home_q1_score) $homeTotal += $game->home_q1_score;

        if (isset($updates['visitor_q1_score'])) $visitorTotal += $updates['visitor_q1_score'];
        elseif ($game->visitor_q1_score) $visitorTotal += $game->visitor_q1_score;

        if (isset($updates['home_q2_score'])) $homeTotal += $updates['home_q2_score'];
        elseif ($game->home_q2_score) $homeTotal += $game->home_q2_score;

        if (isset($updates['visitor_q2_score'])) $visitorTotal += $updates['visitor_q2_score'];
        elseif ($game->visitor_q2_score) $visitorTotal += $game->visitor_q2_score;

        if (isset($updates['home_q3_score'])) $homeTotal += $updates['home_q3_score'];
        elseif ($game->home_q3_score) $homeTotal += $game->home_q3_score;

        if (isset($updates['visitor_q3_score'])) $visitorTotal += $updates['visitor_q3_score'];
        elseif ($game->visitor_q3_score) $visitorTotal += $game->visitor_q3_score;

        if (isset($updates['home_q4_score'])) $homeTotal += $updates['home_q4_score'];
        elseif ($game->home_q4_score) $homeTotal += $game->home_q4_score;

        if (isset($updates['visitor_q4_score'])) $visitorTotal += $updates['visitor_q4_score'];
        elseif ($game->visitor_q4_score) $visitorTotal += $game->visitor_q4_score;

        $updates['home_team_score'] = $homeTotal;
        $updates['visitor_team_score'] = $visitorTotal;

        $game->update($updates);
        $game->refresh();

        $this->info("Scores updated:");
        $this->table(
            ['Quarter', 'Home', 'Visitor'],
            [
                ['Q1', $game->home_q1_score !== null ? $game->home_q1_score : '-', $game->visitor_q1_score !== null ? $game->visitor_q1_score : '-'],
                ['Q2', $game->home_q2_score !== null ? $game->home_q2_score : '-', $game->visitor_q2_score !== null ? $game->visitor_q2_score : '-'],
                ['Q3', $game->home_q3_score !== null ? $game->home_q3_score : '-', $game->visitor_q3_score !== null ? $game->visitor_q3_score : '-'],
                ['Q4', $game->home_q4_score !== null ? $game->home_q4_score : '-', $game->visitor_q4_score !== null ? $game->visitor_q4_score : '-'],
                ['Total', $game->home_team_score, $game->visitor_team_score],
            ]
        );

        return 0;
    }
}
