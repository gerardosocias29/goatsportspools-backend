<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WagerType;

class WagerTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WagerType::truncate();

        $wagerTypes = [
            ["name" => "Spread", "description" => "Spread Wagers", "no_of_teams" => 1],
            ["name" => "TotalPoints", "description" => "Total Points Wagers", "no_of_teams" => 1],
            ["name" => "MoneyLine", "description" => "Money Line Wagers", "no_of_teams" => 1],
            ["name" => "2TP", "description" => "Two Team Parlay", "no_of_teams" => 2],
            ["name" => "2TT", "description" => "Two Team Teaser", "no_of_teams" => 2],
            ["name" => "3TP", "description" => "Three Team Parlay", "no_of_teams" => 3],
            ["name" => "3TT", "description" => "Three Team Teaser", "no_of_teams" => 3],
            ["name" => "4TP", "description" => "Four Team Parlay", "no_of_teams" => 4],
            ["name" => "4TT", "description" => "Four Team Teaser", "no_of_teams" => 4],
            ["name" => "5TP", "description" => "Five Team Parlay", "no_of_teams" => 5],
            ["name" => "5TT", "description" => "Five Team Teaser", "no_of_teams" => 5],
            ["name" => "6TP", "description" => "Six Team Parlay", "no_of_teams" => 6],
            ["name" => "6TT", "description" => "Six Team Teaser", "no_of_teams" => 6],
            ["name" => "7TP", "description" => "Seven Team Parlay", "no_of_teams" => 7],
            ["name" => "7TT", "description" => "Seven Team Teaser", "no_of_teams" => 7],
            ["name" => "8TP", "description" => "Eight Team Parlay", "no_of_teams" => 8],
            ["name" => "8TT", "description" => "Eight Team Teaser", "no_of_teams" => 8],
        ];

        foreach ($wagerTypes as $data) {
            WagerType::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'no_of_teams' => $data['no_of_teams'],
            ]);
        }
    }
}
