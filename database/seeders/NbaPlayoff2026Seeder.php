<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NbaPlayoff;
use App\Models\NbaPlayoffTeam;
use App\Models\Team;

class NbaPlayoff2026Seeder extends Seeder
{
    /**
     * Seed a 2026 NBA Playoffs entry with 16 seeded teams (8 East + 8 West).
     * Uses plausible 2025-26 standings. Requires NbaTeamsSeeder to have run first.
     *
     * Run: php artisan db:seed --class=NbaPlayoff2026Seeder
     */
    public function run(): void
    {
        // Create the 2026 playoff
        $playoff = NbaPlayoff::updateOrCreate(
            ['year' => 2026],
            ['name' => '2026 NBA Playoffs', 'status' => 'upcoming', 'current_round' => 0]
        );

        // Seeded teams — nickname => seed (8 per conference)
        // Conference values in teams table are "Eastern"/"Western"
        $eastSeeds = [
            'Cavaliers'  => 1,
            'Celtics'    => 2,
            'Knicks'     => 3,
            'Pacers'     => 4,
            'Bucks'      => 5,
            'Magic'      => 6,
            'Heat'       => 7,
            '76ers'      => 8,
        ];

        $westSeeds = [
            'Thunder'       => 1,
            'Rockets'       => 2,
            'Grizzlies'     => 3,
            'Lakers'        => 4,
            'Nuggets'       => 5,
            'Timberwolves'  => 6,
            'Warriors'      => 7,
            'Mavericks'     => 8,
        ];

        $seeded = 0;

        foreach ($eastSeeds as $nickname => $seed) {
            $team = Team::where('nickname', $nickname)->where('league', 'NBA')->first();
            if (!$team) {
                $this->command->warn("Team '{$nickname}' not found — skipping.");
                continue;
            }
            NbaPlayoffTeam::updateOrCreate(
                ['playoff_id' => $playoff->id, 'team_id' => $team->id],
                ['conference' => 'East', 'seed' => $seed]
            );
            $seeded++;
        }

        foreach ($westSeeds as $nickname => $seed) {
            $team = Team::where('nickname', $nickname)->where('league', 'NBA')->first();
            if (!$team) {
                $this->command->warn("Team '{$nickname}' not found — skipping.");
                continue;
            }
            NbaPlayoffTeam::updateOrCreate(
                ['playoff_id' => $playoff->id, 'team_id' => $team->id],
                ['conference' => 'West', 'seed' => $seed]
            );
            $seeded++;
        }

        $this->command->info("Seeded 2026 NBA Playoffs with {$seeded}/16 teams (playoff ID: {$playoff->id}).");
    }
}
