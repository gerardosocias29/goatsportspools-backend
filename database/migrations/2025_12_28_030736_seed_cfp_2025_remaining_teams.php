<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the remaining 4 CFP teams (Oregon, Indiana, Georgia, Ohio State).
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
            ->whereIn('code', ['ORE', 'IND', 'UGA', 'OSU'])
            ->delete();
    }
};
