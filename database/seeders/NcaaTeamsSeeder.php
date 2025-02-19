<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NcaaTeamsSeeder extends Seeder
{
    public function run()
    {
        $teams = [
            // East Region
            ['region' => 'East', 'seed' => 1, 'school' => 'UConn', 'nickname' => 'Huskies'],
            ['region' => 'East', 'seed' => 2, 'school' => 'Iowa State', 'nickname' => 'Cyclones'],
            ['region' => 'East', 'seed' => 3, 'school' => 'Illinois', 'nickname' => 'Fighting Illini'],
            ['region' => 'East', 'seed' => 4, 'school' => 'Auburn', 'nickname' => 'Tigers'],
            ['region' => 'East', 'seed' => 5, 'school' => 'San Diego State', 'nickname' => 'Aztecs'],
            ['region' => 'East', 'seed' => 6, 'school' => 'BYU', 'nickname' => 'Cougars'],
            ['region' => 'East', 'seed' => 7, 'school' => 'Florida', 'nickname' => 'Gators'],
            ['region' => 'East', 'seed' => 8, 'school' => 'Nebraska', 'nickname' => 'Cornhuskers'],
            ['region' => 'East', 'seed' => 9, 'school' => 'Texas A&M', 'nickname' => 'Aggies'],
            ['region' => 'East', 'seed' => 10, 'school' => 'Drake', 'nickname' => 'Bulldogs'],
            ['region' => 'East', 'seed' => 11, 'school' => 'Washington State', 'nickname' => 'Cougars'],
            ['region' => 'East', 'seed' => 12, 'school' => 'South Florida', 'nickname' => 'Bulls'],
            ['region' => 'East', 'seed' => 13, 'school' => 'Vermont', 'nickname' => 'Catamounts'],
            ['region' => 'East', 'seed' => 14, 'school' => 'Colgate', 'nickname' => 'Raiders'],
            ['region' => 'East', 'seed' => 15, 'school' => 'Longwood', 'nickname' => 'Lancers'],
            ['region' => 'East', 'seed' => 16, 'school' => 'Howard', 'nickname' => 'Bison'],

            // West Region
            ['region' => 'West', 'seed' => 1, 'school' => 'Arizona', 'nickname' => 'Wildcats'],
            ['region' => 'West', 'seed' => 2, 'school' => 'Marquette', 'nickname' => 'Golden Eagles'],
            ['region' => 'West', 'seed' => 3, 'school' => 'Duke', 'nickname' => 'Blue Devils'],
            ['region' => 'West', 'seed' => 4, 'school' => 'Baylor', 'nickname' => 'Bears'],
            ['region' => 'West', 'seed' => 5, 'school' => 'Saint Mary\'s', 'nickname' => 'Gaels'],
            ['region' => 'West', 'seed' => 6, 'school' => 'USC', 'nickname' => 'Trojans'],
            ['region' => 'West', 'seed' => 7, 'school' => 'Mississippi State', 'nickname' => 'Bulldogs'],
            ['region' => 'West', 'seed' => 8, 'school' => 'Oklahoma', 'nickname' => 'Sooners'],
            ['region' => 'West', 'seed' => 9, 'school' => 'Michigan State', 'nickname' => 'Spartans'],
            ['region' => 'West', 'seed' => 10, 'school' => 'Memphis', 'nickname' => 'Tigers'],
            ['region' => 'West', 'seed' => 11, 'school' => 'Providence', 'nickname' => 'Friars'],
            ['region' => 'West', 'seed' => 12, 'school' => 'Grand Canyon', 'nickname' => 'Antelopes'],
            ['region' => 'West', 'seed' => 13, 'school' => 'UC Irvine', 'nickname' => 'Anteaters'],
            ['region' => 'West', 'seed' => 14, 'school' => 'Western Kentucky', 'nickname' => 'Hilltoppers'],
            ['region' => 'West', 'seed' => 15, 'school' => 'Northern Kentucky', 'nickname' => 'Norse'],
            ['region' => 'West', 'seed' => 16, 'school' => 'Fairleigh Dickinson', 'nickname' => 'Knights'],

            // Midwest Region
            ['region' => 'Midwest', 'seed' => 1, 'school' => 'Purdue', 'nickname' => 'Boilermakers'],
            ['region' => 'Midwest', 'seed' => 2, 'school' => 'Tennessee', 'nickname' => 'Volunteers'],
            ['region' => 'Midwest', 'seed' => 3, 'school' => 'Creighton', 'nickname' => 'Bluejays'],
            ['region' => 'Midwest', 'seed' => 4, 'school' => 'Kansas', 'nickname' => 'Jayhawks'],
            ['region' => 'Midwest', 'seed' => 5, 'school' => 'Gonzaga', 'nickname' => 'Bulldogs'],
            ['region' => 'Midwest', 'seed' => 6, 'school' => 'South Carolina', 'nickname' => 'Gamecocks'],
            ['region' => 'Midwest', 'seed' => 7, 'school' => 'Texas', 'nickname' => 'Longhorns'],
            ['region' => 'Midwest', 'seed' => 8, 'school' => 'Utah State', 'nickname' => 'Aggies'],
            ['region' => 'Midwest', 'seed' => 9, 'school' => 'TCU', 'nickname' => 'Horned Frogs'],
            ['region' => 'Midwest', 'seed' => 10, 'school' => 'Virginia', 'nickname' => 'Cavaliers'],
            ['region' => 'Midwest', 'seed' => 11, 'school' => 'Oregon', 'nickname' => 'Ducks'],
            ['region' => 'Midwest', 'seed' => 12, 'school' => 'McNeese', 'nickname' => 'Cowboys'],
            ['region' => 'Midwest', 'seed' => 13, 'school' => 'Samford', 'nickname' => 'Bulldogs'],
            ['region' => 'Midwest', 'seed' => 14, 'school' => 'Akron', 'nickname' => 'Zips'],
            ['region' => 'Midwest', 'seed' => 15, 'school' => 'Saint Peter\'s', 'nickname' => 'Peacocks'],
            ['region' => 'Midwest', 'seed' => 16, 'school' => 'Montana State', 'nickname' => 'Bobcats'],

            // South Region
            ['region' => 'South', 'seed' => 1, 'school' => 'Houston', 'nickname' => 'Cougars'],
            ['region' => 'South', 'seed' => 2, 'school' => 'Alabama', 'nickname' => 'Crimson Tide'],
            ['region' => 'South', 'seed' => 3, 'school' => 'Kentucky', 'nickname' => 'Wildcats'],
            ['region' => 'South', 'seed' => 4, 'school' => 'Miami', 'nickname' => 'Hurricanes'],
            ['region' => 'South', 'seed' => 5, 'school' => 'Indiana', 'nickname' => 'Hoosiers'],
            ['region' => 'South', 'seed' => 6, 'school' => 'Northwestern', 'nickname' => 'Wildcats'],
            ['region' => 'South', 'seed' => 7, 'school' => 'Maryland', 'nickname' => 'Terrapins'],
            ['region' => 'South', 'seed' => 8, 'school' => 'Missouri', 'nickname' => 'Tigers'],
            ['region' => 'South', 'seed' => 9, 'school' => 'Rutgers', 'nickname' => 'Scarlet Knights'],
            ['region' => 'South', 'seed' => 10, 'school' => 'Xavier', 'nickname' => 'Musketeers'],
            ['region' => 'South', 'seed' => 11, 'school' => 'Colorado', 'nickname' => 'Buffaloes'],
            ['region' => 'South', 'seed' => 12, 'school' => 'Louisiana', 'nickname' => 'Ragin\' Cajuns'],
            ['region' => 'South', 'seed' => 13, 'school' => 'Toledo', 'nickname' => 'Rockets'],
            ['region' => 'South', 'seed' => 14, 'school' => 'Liberty', 'nickname' => 'Flames'],
            ['region' => 'South', 'seed' => 15, 'school' => 'Yale', 'nickname' => 'Bulldogs'],
            ['region' => 'South', 'seed' => 16, 'school' => 'Texas Southern', 'nickname' => 'Tigers'],
        ];

        DB::table('ncaa_teams')->insert($teams);
    }
}
