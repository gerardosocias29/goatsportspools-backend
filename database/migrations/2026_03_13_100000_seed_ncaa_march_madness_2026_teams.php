<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Soft-delete old ncaa_teams and seed 2026 March Madness 64 teams.
     * Based on Lunardi Bracketology projection (March 12, 2026).
     * Update with official bracket after Selection Sunday (March 15, 2026).
     */
    public function up(): void
    {
        // Step 1: Soft-delete all existing ncaa_teams (preserves old auction FKs)
        DB::table('ncaa_teams')
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        // Step 2: Insert 64 new teams for 2026 March Madness
        $teams = [
            // EAST REGION (Washington D.C.)
            ['school' => 'Duke', 'nickname' => 'Blue Devils'],                  // East 1
            ['school' => 'Michigan State', 'nickname' => 'Spartans'],           // East 2
            ['school' => 'Alabama', 'nickname' => 'Crimson Tide'],              // East 3
            ['school' => 'Kansas', 'nickname' => 'Jayhawks'],                   // East 4
            ['school' => 'Vanderbilt', 'nickname' => 'Commodores'],             // East 5
            ['school' => 'North Carolina', 'nickname' => 'Tar Heels'],          // East 6
            ['school' => 'Villanova', 'nickname' => 'Wildcats'],                // East 7
            ['school' => 'Utah State', 'nickname' => 'Aggies'],                 // East 8
            ['school' => 'Iowa', 'nickname' => 'Hawkeyes'],                     // East 9
            ['school' => 'Santa Clara', 'nickname' => 'Broncos'],               // East 10
            ['school' => 'UCF', 'nickname' => 'Knights'],                       // East 11
            ['school' => 'High Point', 'nickname' => 'Panthers'],               // East 12
            ['school' => 'Hofstra', 'nickname' => 'Pride'],                     // East 13
            ['school' => 'Troy', 'nickname' => 'Trojans'],                      // East 14
            ['school' => 'UMBC', 'nickname' => 'Retrievers'],                   // East 15
            ['school' => 'Howard', 'nickname' => 'Bison'],                      // East 16

            // SOUTH REGION (Houston)
            ['school' => 'Florida', 'nickname' => 'Gators'],                    // South 1
            ['school' => 'Houston', 'nickname' => 'Cougars'],                   // South 2
            ['school' => 'Nebraska', 'nickname' => 'Cornhuskers'],              // South 3
            ['school' => 'Purdue', 'nickname' => 'Boilermakers'],               // South 4
            ['school' => 'St. John\'s', 'nickname' => 'Red Storm'],             // South 5
            ['school' => 'Louisville', 'nickname' => 'Cardinals'],              // South 6
            ['school' => 'Saint Mary\'s', 'nickname' => 'Gaels'],               // South 7
            ['school' => 'Miami', 'nickname' => 'Hurricanes'],                  // South 8
            ['school' => 'TCU', 'nickname' => 'Horned Frogs'],                  // South 9
            ['school' => 'Texas', 'nickname' => 'Longhorns'],                   // South 10
            ['school' => 'Missouri', 'nickname' => 'Tigers'],                   // South 11
            ['school' => 'USF', 'nickname' => 'Bulls'],                         // South 12
            ['school' => 'Liberty', 'nickname' => 'Flames'],                    // South 13
            ['school' => 'Wright State', 'nickname' => 'Raiders'],              // South 14
            ['school' => 'Furman', 'nickname' => 'Paladins'],                   // South 15
            ['school' => 'Bethune-Cookman', 'nickname' => 'Wildcats'],          // South 16

            // MIDWEST REGION (Chicago)
            ['school' => 'Michigan', 'nickname' => 'Wolverines'],               // Midwest 1
            ['school' => 'UConn', 'nickname' => 'Huskies'],                     // Midwest 2
            ['school' => 'Iowa State', 'nickname' => 'Cyclones'],               // Midwest 3
            ['school' => 'Texas Tech', 'nickname' => 'Red Raiders'],            // Midwest 4
            ['school' => 'Tennessee', 'nickname' => 'Volunteers'],              // Midwest 5
            ['school' => 'Wisconsin', 'nickname' => 'Badgers'],                 // Midwest 6
            ['school' => 'Georgia', 'nickname' => 'Bulldogs'],                  // Midwest 7
            ['school' => 'Ohio State', 'nickname' => 'Buckeyes'],               // Midwest 8
            ['school' => 'Clemson', 'nickname' => 'Tigers'],                    // Midwest 9
            ['school' => 'NC State', 'nickname' => 'Wolfpack'],                 // Midwest 10
            ['school' => 'SMU', 'nickname' => 'Mustangs'],                      // Midwest 11
            ['school' => 'Northern Iowa', 'nickname' => 'Panthers'],            // Midwest 12
            ['school' => 'Utah Valley', 'nickname' => 'Wolverines'],            // Midwest 13
            ['school' => 'North Dakota State', 'nickname' => 'Bison'],          // Midwest 14
            ['school' => 'Queens', 'nickname' => 'Royals'],                     // Midwest 15
            ['school' => 'Long Island', 'nickname' => 'Sharks'],                // Midwest 16

            // WEST REGION (San Jose)
            ['school' => 'Arizona', 'nickname' => 'Wildcats'],                  // West 1
            ['school' => 'Illinois', 'nickname' => 'Fighting Illini'],          // West 2
            ['school' => 'Gonzaga', 'nickname' => 'Bulldogs'],                  // West 3
            ['school' => 'Virginia', 'nickname' => 'Cavaliers'],                // West 4
            ['school' => 'Arkansas', 'nickname' => 'Razorbacks'],               // West 5
            ['school' => 'BYU', 'nickname' => 'Cougars'],                       // West 6
            ['school' => 'Kentucky', 'nickname' => 'Wildcats'],                 // West 7
            ['school' => 'UCLA', 'nickname' => 'Bruins'],                       // West 8
            ['school' => 'Texas A&M', 'nickname' => 'Aggies'],                  // West 9
            ['school' => 'Saint Louis', 'nickname' => 'Billikens'],             // West 10
            ['school' => 'Miami (OH)', 'nickname' => 'RedHawks'],               // West 11
            ['school' => 'Yale', 'nickname' => 'Bulldogs'],                     // West 12
            ['school' => 'SF Austin', 'nickname' => 'Lumberjacks'],             // West 13
            ['school' => 'UC Irvine', 'nickname' => 'Anteaters'],              // West 14
            ['school' => 'Tennessee State', 'nickname' => 'Tigers'],            // West 15
            ['school' => 'Siena', 'nickname' => 'Saints'],                      // West 16
        ];

        foreach ($teams as $team) {
            DB::table('ncaa_teams')->insert([
                'school' => $team['school'],
                'nickname' => $team['nickname'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the 2026 teams (they have no deleted_at)
        DB::table('ncaa_teams')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', '2026-03-13')
            ->delete();

        // Restore old teams
        DB::table('ncaa_teams')
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null]);
    }
};
