<?php

namespace App\Services;

use App\Models\NbaPlayoff;
use App\Models\NbaPlayoffTeam;
use App\Models\PlayoffBracket;
use App\Models\PlayoffBracketPick;
use App\Models\PlayoffPool;
use App\Models\PlayoffPoolParticipant;
use Illuminate\Support\Facades\DB;

class PlayoffScoringService
{
    /**
     * Score a specific round across all finalized brackets in all pools for a playoff.
     *
     * @param int $playoffId
     * @param int $round  1-4
     * @return array  Summary of scoring results
     */
    public function scoreRound(int $playoffId, int $round): array
    {
        $playoff = NbaPlayoff::findOrFail($playoffId);

        // Load all playoff teams to build a lookup: team_id → NbaPlayoffTeam row
        $playoffTeams = NbaPlayoffTeam::where('playoff_id', $playoffId)->get()->keyBy('team_id');

        // Get the round-specific column names
        $beatSeedCol = $round === 4 ? 'finals_beat_seed' : "r{$round}_beat_seed";
        $gamesCol = $round === 4 ? 'finals_games' : "r{$round}_games";

        // Round index for the games bonus matrix (0-based)
        $roundIndex = $round - 1;

        // Load scoring config
        $basePoints = config("playoff_scoring.base_points.{$round}", 0);
        $gamesMatrix = config('playoff_scoring.games_bonus', []);

        // Find all pools for this playoff
        $poolIds = PlayoffPool::where('playoff_id', $playoffId)->pluck('id');

        // Only score PAID + finalized brackets (PAID = officially entered in pool)
        $brackets = PlayoffBracket::whereHas('participant', function ($q) use ($poolIds) {
            $q->whereIn('pool_id', $poolIds);
        })
            ->where('status', 'finalized')
            ->where('is_paid', true)
            ->pluck('id');

        // Get all picks for this round across those brackets
        $picks = PlayoffBracketPick::whereIn('bracket_id', $brackets)
            ->where('round', $round)
            ->get();

        $scored = 0;
        $correct = 0;
        $incorrect = 0;

        DB::transaction(function () use (
            $picks, $playoffTeams, $beatSeedCol, $gamesCol,
            $roundIndex, $basePoints, $gamesMatrix,
            &$scored, &$correct, &$incorrect
        ) {
            foreach ($picks as $pick) {
                $teamRow = $playoffTeams->get($pick->picked_team_id);

                if (!$teamRow) {
                    // Team not found in playoff seeding — skip
                    continue;
                }

                $actualBeatSeed = $teamRow->$beatSeedCol;
                $actualGames = $teamRow->$gamesCol;

                if ($actualBeatSeed === null) {
                    // Round not yet played for this team, or team lost — 0 points
                    $pick->update([
                        'base_points' => 0,
                        'games_bonus' => 0,
                        'seed_bonus' => 0,
                        'scored_at' => now(),
                    ]);
                    $incorrect++;
                } else {
                    // Team won this round — award base points
                    $base = $basePoints;

                    // Seed bonus: max(0, picked_team_seed - beaten_seed)
                    $pickedSeed = $teamRow->seed;
                    $seedBonus = max(0, $pickedSeed - $actualBeatSeed);

                    // Games bonus: lookup from matrix
                    $gamesBonus = 0;
                    if ($actualGames && $pick->picked_games) {
                        $gamesBonus = $gamesMatrix[$actualGames][$pick->picked_games][$roundIndex] ?? 0;
                    }

                    $pick->update([
                        'base_points' => $base,
                        'games_bonus' => $gamesBonus,
                        'seed_bonus' => $seedBonus,
                        'scored_at' => now(),
                    ]);
                    $correct++;
                }

                $scored++;
            }
        });

        // Recalculate bracket totals for all affected brackets
        $affectedBracketIds = $picks->pluck('bracket_id')->unique();
        $this->recalcBracketTotals($affectedBracketIds);

        return [
            'playoff_id' => $playoffId,
            'round' => $round,
            'picks_scored' => $scored,
            'correct' => $correct,
            'incorrect' => $incorrect,
        ];
    }

    /**
     * Score ALL rounds (1-4) for a playoff. Useful for full rescore.
     */
    public function scoreAll(int $playoffId): array
    {
        $results = [];
        for ($round = 1; $round <= 4; $round++) {
            $results[] = $this->scoreRound($playoffId, $round);
        }
        return $results;
    }

    /**
     * Score a single bracket (all its picks against current results).
     */
    public function scoreBracket(int $bracketId): array
    {
        $bracket = PlayoffBracket::with('picks', 'participant.pool')->findOrFail($bracketId);
        $playoffId = $bracket->participant->pool->playoff_id;

        $playoffTeams = NbaPlayoffTeam::where('playoff_id', $playoffId)->get()->keyBy('team_id');

        $gamesMatrix = config('playoff_scoring.games_bonus', []);
        $scored = 0;
        $totalBase = 0;
        $totalGames = 0;
        $totalSeed = 0;

        DB::transaction(function () use (
            $bracket, $playoffTeams, $gamesMatrix,
            &$scored, &$totalBase, &$totalGames, &$totalSeed
        ) {
            foreach ($bracket->picks as $pick) {
                $round = $pick->round;
                $beatSeedCol = $round === 4 ? 'finals_beat_seed' : "r{$round}_beat_seed";
                $gamesCol = $round === 4 ? 'finals_games' : "r{$round}_games";
                $roundIndex = $round - 1;
                $basePoints = config("playoff_scoring.base_points.{$round}", 0);

                $teamRow = $playoffTeams->get($pick->picked_team_id);

                if (!$teamRow) {
                    continue;
                }

                $actualBeatSeed = $teamRow->$beatSeedCol;
                $actualGames = $teamRow->$gamesCol;

                if ($actualBeatSeed === null) {
                    $pick->update([
                        'base_points' => 0,
                        'games_bonus' => 0,
                        'seed_bonus' => 0,
                        'scored_at' => now(),
                    ]);
                } else {
                    $base = $basePoints;
                    $seedBonus = max(0, $teamRow->seed - $actualBeatSeed);
                    $gamesBonus = 0;
                    if ($actualGames && $pick->picked_games) {
                        $gamesBonus = $gamesMatrix[$actualGames][$pick->picked_games][$roundIndex] ?? 0;
                    }

                    $pick->update([
                        'base_points' => $base,
                        'games_bonus' => $gamesBonus,
                        'seed_bonus' => $seedBonus,
                        'scored_at' => now(),
                    ]);

                    $totalBase += $base;
                    $totalGames += $gamesBonus;
                    $totalSeed += $seedBonus;
                }

                $scored++;
            }
        });

        // Recalculate this bracket's total
        $this->recalcBracketTotals(collect([$bracketId]));

        $bracket->refresh();

        return [
            'bracket_id' => $bracketId,
            'picks_scored' => $scored,
            'total_base' => $totalBase,
            'total_games_bonus' => $totalGames,
            'total_seed_bonus' => $totalSeed,
            'total_points' => $bracket->total_points,
        ];
    }

    /**
     * Recalculate total_points on brackets and their participants.
     */
    private function recalcBracketTotals($bracketIds): void
    {
        foreach ($bracketIds as $bracketId) {
            $total = PlayoffBracketPick::where('bracket_id', $bracketId)
                ->selectRaw('COALESCE(SUM(base_points + games_bonus + seed_bonus), 0) as total')
                ->value('total');

            PlayoffBracket::where('id', $bracketId)->update(['total_points' => $total]);
        }

        // Recalculate participant totals (sum of all their brackets' total_points)
        $participantIds = PlayoffBracket::whereIn('id', $bracketIds)
            ->pluck('participant_id')
            ->unique();

        foreach ($participantIds as $participantId) {
            $total = PlayoffBracket::where('participant_id', $participantId)
                ->sum('total_points');

            PlayoffPoolParticipant::where('id', $participantId)
                ->update(['total_points' => $total]);
        }
    }
}
