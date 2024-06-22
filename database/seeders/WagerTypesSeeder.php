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
            ["name" => "Straight", "description" => "Straight Wagers", "no_of_teams" => 1],
            ["name" => "TotalPoints", "description" => "Total Points Wagers", "no_of_teams" => 1],
            ["name" => "MoneyLine", "description" => "Money Line Wagers", "no_of_teams" => 1],
            ["name" => "2TP", "description" => "Two Team Parlay", "no_of_teams" => 2],
            ["name" => "2TT", "description" => "Two Team Teaser", "no_of_teams" => 2],
            ["name" => "3TP", "description" => "Three Team Parlay", "no_of_teams" => 3],
            ["name" => "3TT", "description" => "Three Team Teaser", "no_of_teams" => 3],
            ["name" => "4TP", "description" => "Four Team Parlay", "no_of_teams" => 4],
            ["name" => "4TT", "description" => "Four Team Teaser", "no_of_teams" => 4],
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
