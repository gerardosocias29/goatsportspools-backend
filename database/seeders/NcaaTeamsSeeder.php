<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NcaaTeamsSeeder extends Seeder
{
    public function run()
    {
        DB::table('ncaa_teams')->truncate();
        
        $teams = [
            // South Region
            ['school' => 'Auburn', 'nickname' => 'Tigers'],
            ['school' => 'Louisville', 'nickname' => 'Cardinals'],
            ['school' => 'Creighton', 'nickname' => 'Bluejays'],
            ['school' => 'Michigan', 'nickname' => 'Wolverines'],
            ['school' => 'UC San Diego', 'nickname' => 'Tritons'],
            ['school' => 'Texas A&M', 'nickname' => 'Aggies'],
            ['school' => 'Yale', 'nickname' => 'Bulldogs'],
            ['school' => 'Ole Miss', 'nickname' => 'Rebels'],
            ['school' => 'Iowa State', 'nickname' => 'Cyclones'],
            ['school' => 'Lipscomb', 'nickname' => 'Bisons'],
            ['school' => 'Marquette', 'nickname' => 'Golden Eagles'],
            ['school' => 'New Mexico', 'nickname' => 'Lobos'],
            ['school' => 'Michigan State', 'nickname' => 'Spartans'],
            ['school' => 'Bryant', 'nickname' => 'Bulldogs'],
            
            // East Region
            ['school' => 'Duke', 'nickname' => 'Blue Devils'],
            ['school' => 'Mississippi State', 'nickname' => 'Bulldogs'],
            ['school' => 'Baylor', 'nickname' => 'Bears'],
            ['school' => 'Oregon', 'nickname' => 'Ducks'],
            ['school' => 'Liberty', 'nickname' => 'Flames'],
            ['school' => 'Arizona', 'nickname' => 'Wildcats'],
            ['school' => 'Akron', 'nickname' => 'Zips'],
            ['school' => 'BYU', 'nickname' => 'Cougars'],
            ['school' => 'VCU', 'nickname' => 'Rams'],
            ['school' => 'Wisconsin', 'nickname' => 'Badgers'],
            ['school' => 'Montana', 'nickname' => 'Grizzlies'],
            ['school' => 'Saint Mary\'s', 'nickname' => 'Gaels'],
            ['school' => 'Vanderbilt', 'nickname' => 'Commodores'],
            ['school' => 'Alabama', 'nickname' => 'Crimson Tide'],
            ['school' => 'Robert Morris', 'nickname' => 'Colonials'],
            
            // West Region
            ['school' => 'Florida', 'nickname' => 'Gators'],
            ['school' => 'Norfolk State', 'nickname' => 'Spartans'],
            ['school' => 'UConn', 'nickname' => 'Huskies'],
            ['school' => 'Oklahoma', 'nickname' => 'Sooners'],
            ['school' => 'Memphis', 'nickname' => 'Tigers'],
            ['school' => 'Colorado State', 'nickname' => 'Rams'],
            ['school' => 'Maryland', 'nickname' => 'Terrapins'],
            ['school' => 'Grand Canyon', 'nickname' => 'Lopes'],
            ['school' => 'Missouri', 'nickname' => 'Tigers'],
            ['school' => 'Drake', 'nickname' => 'Bulldogs'],
            ['school' => 'Texas Tech', 'nickname' => 'Red Raiders'],
            ['school' => 'UNC Wilmington', 'nickname' => 'Seahawks'],
            ['school' => 'Kansas', 'nickname' => 'Jayhawks'],
            ['school' => 'Arkansas', 'nickname' => 'Razorbacks'],
            ['school' => 'Saint John\'s', 'nickname' => 'Red Storm'],
            ['school' => 'Omaha', 'nickname' => 'Mavericks'],
            
            // Midwest Region
            ['school' => 'Houston', 'nickname' => 'Cougars'],
            ['school' => 'SIU Edwardsville', 'nickname' => 'Cougars'],
            ['school' => 'Gonzaga', 'nickname' => 'Bulldogs'],
            ['school' => 'Georgia', 'nickname' => 'Bulldogs'],
            ['school' => 'Clemson', 'nickname' => 'Tigers'],
            ['school' => 'McNeese', 'nickname' => 'Cowboys'],
            ['school' => 'Purdue', 'nickname' => 'Boilermakers'],
            ['school' => 'High Point', 'nickname' => 'Panthers'],
            ['school' => 'Illinois', 'nickname' => 'Fighting Illini'],
            ['school' => 'Kentucky', 'nickname' => 'Wildcats'],
            ['school' => 'Troy', 'nickname' => 'Trojans'],
            ['school' => 'UCLA', 'nickname' => 'Bruins'],
            ['school' => 'Utah State', 'nickname' => 'Aggies'],
            ['school' => 'Tennessee', 'nickname' => 'Volunteers'],
            ['school' => 'Wofford', 'nickname' => 'Terriers'],
            
            // First Four (Play-in Games)
            ['school' => 'Texas', 'nickname' => 'Longhorns'],
            ['school' => 'Xavier', 'nickname' => 'Musketeers'],
            ['school' => 'American', 'nickname' => 'Eagles'],
            ['school' => 'Mount St. Mary\'s', 'nickname' => 'Mountaineers'],
            ['school' => 'Alabama State', 'nickname' => 'Hornets'],
            ['school' => 'Saint Francis U', 'nickname' => 'Red Flash'],
            ['school' => 'San Diego State', 'nickname' => 'Aztecs'],
            ['school' => 'North Carolina', 'nickname' => 'Tar Heels'],
        ];
        
        DB::table('ncaa_teams')->insert($teams);
    }
}
