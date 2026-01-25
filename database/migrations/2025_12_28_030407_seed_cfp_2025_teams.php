<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the 8 College Football Playoff 2025 Quarterfinal teams.
     */
    public function up(): void
    {
        $teams = [
            [
                'name' => 'Oregon Ducks',
                'league' => 'NCAAF',
                'nickname' => 'Ducks',
                'code' => 'ORE',
                'conference' => 'Big Ten',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/2483.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Texas Tech Red Raiders',
                'league' => 'NCAAF',
                'nickname' => 'Red Raiders',
                'code' => 'TTU',
                'conference' => 'Big 12',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/2641.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Alabama Crimson Tide',
                'league' => 'NCAAF',
                'nickname' => 'Crimson Tide',
                'code' => 'ALA',
                'conference' => 'SEC',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/333.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Indiana Hoosiers',
                'league' => 'NCAAF',
                'nickname' => 'Hoosiers',
                'code' => 'IND',
                'conference' => 'Big Ten',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/84.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Georgia Bulldogs',
                'league' => 'NCAAF',
                'nickname' => 'Bulldogs',
                'code' => 'UGA',
                'conference' => 'SEC',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/61.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Ole Miss Rebels',
                'league' => 'NCAAF',
                'nickname' => 'Rebels',
                'code' => 'MISS',
                'conference' => 'SEC',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/145.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Ohio State Buckeyes',
                'league' => 'NCAAF',
                'nickname' => 'Buckeyes',
                'code' => 'OSU',
                'conference' => 'Big Ten',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/194.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Miami Hurricanes',
                'league' => 'NCAAF',
                'nickname' => 'Hurricanes',
                'code' => 'MIA',
                'conference' => 'ACC',
                'image_url' => 'https://a.espncdn.com/i/teamlogos/ncaa/500/2390.png',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($teams as $team) {
            // Only insert if team doesn't already exist
            $exists = DB::table('teams')
                ->where('name', $team['name'])
                ->where('league', 'NCAAF')
                ->exists();

            if (!$exists) {
                DB::table('teams')->insert($team);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('teams')
            ->where('league', 'NCAAF')
            ->whereIn('code', ['ORE', 'TTU', 'ALA', 'IND', 'UGA', 'MISS', 'OSU', 'MIA'])
            ->delete();
    }
};
