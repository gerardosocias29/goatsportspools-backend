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
            ['school' => 'UConn', 'nickname' => 'Huskies'],
            ['school' => 'Iowa State', 'nickname' => 'Cyclones'],
            ['school' => 'Illinois', 'nickname' => 'Fighting Illini'],
            ['school' => 'Auburn', 'nickname' => 'Tigers'],
            ['school' => 'San Diego State', 'nickname' => 'Aztecs'],
            ['school' => 'BYU', 'nickname' => 'Cougars'],
            ['school' => 'Florida', 'nickname' => 'Gators'],
            ['school' => 'Nebraska', 'nickname' => 'Cornhuskers'],
            ['school' => 'Texas A&M', 'nickname' => 'Aggies'],
            ['school' => 'Drake', 'nickname' => 'Bulldogs'],
            ['school' => 'Washington State', 'nickname' => 'Cougars'],
            ['school' => 'South Florida', 'nickname' => 'Bulls'],
            ['school' => 'Vermont', 'nickname' => 'Catamounts'],
            ['school' => 'Colgate', 'nickname' => 'Raiders'],
            ['school' => 'Longwood', 'nickname' => 'Lancers'],
            ['school' => 'Howard', 'nickname' => 'Bison'],

            // West Region
            ['school' => 'Arizona', 'nickname' => 'Wildcats'],
            ['school' => 'Marquette', 'nickname' => 'Golden Eagles'],
            ['school' => 'Duke', 'nickname' => 'Blue Devils'],
            ['school' => 'Baylor', 'nickname' => 'Bears'],
            ['school' => 'Saint Mary\'s', 'nickname' => 'Gaels'],
            ['school' => 'USC', 'nickname' => 'Trojans'],
            ['school' => 'Mississippi State', 'nickname' => 'Bulldogs'],
            ['school' => 'Oklahoma', 'nickname' => 'Sooners'],
            ['school' => 'Michigan State', 'nickname' => 'Spartans'],
            ['school' => 'Memphis', 'nickname' => 'Tigers'],
            ['school' => 'Providence', 'nickname' => 'Friars'],
            ['school' => 'Grand Canyon', 'nickname' => 'Antelopes'],
            ['school' => 'UC Irvine', 'nickname' => 'Anteaters'],
            ['school' => 'Western Kentucky', 'nickname' => 'Hilltoppers'],
            ['school' => 'Northern Kentucky', 'nickname' => 'Norse'],
            ['school' => 'Fairleigh Dickinson', 'nickname' => 'Knights'],

            // Midwest Region
            ['school' => 'Purdue', 'nickname' => 'Boilermakers'],
            ['school' => 'Tennessee', 'nickname' => 'Volunteers'],
            ['school' => 'Creighton', 'nickname' => 'Bluejays'],
            ['school' => 'Kansas', 'nickname' => 'Jayhawks'],
            ['school' => 'Gonzaga', 'nickname' => 'Bulldogs'],
            ['school' => 'South Carolina', 'nickname' => 'Gamecocks'],
            ['school' => 'Texas', 'nickname' => 'Longhorns'],
            ['school' => 'Utah State', 'nickname' => 'Aggies'],
            ['school' => 'TCU', 'nickname' => 'Horned Frogs'],
            ['school' => 'Virginia', 'nickname' => 'Cavaliers'],
            ['school' => 'Oregon', 'nickname' => 'Ducks'],
            ['school' => 'McNeese', 'nickname' => 'Cowboys'],
            ['school' => 'Samford', 'nickname' => 'Bulldogs'],
            ['school' => 'Akron', 'nickname' => 'Zips'],
            ['school' => 'Saint Peter\'s', 'nickname' => 'Peacocks'],
            ['school' => 'Montana State', 'nickname' => 'Bobcats'],

            // South Region
            ['school' => 'Houston', 'nickname' => 'Cougars'],
            ['school' => 'Alabama', 'nickname' => 'Crimson Tide'],
            ['school' => 'Kentucky', 'nickname' => 'Wildcats'],
            ['school' => 'Miami', 'nickname' => 'Hurricanes'],
            ['school' => 'Indiana', 'nickname' => 'Hoosiers'],
            ['school' => 'Northwestern', 'nickname' => 'Wildcats'],
            ['school' => 'Maryland', 'nickname' => 'Terrapins'],
            ['school' => 'Missouri', 'nickname' => 'Tigers'],
            ['school' => 'Rutgers', 'nickname' => 'Scarlet Knights'],
            ['school' => 'Xavier', 'nickname' => 'Musketeers'],
            ['school' => 'Colorado', 'nickname' => 'Buffaloes'],
            ['school' => 'Louisiana', 'nickname' => 'Ragin\' Cajuns'],
            ['school' => 'Toledo', 'nickname' => 'Rockets'],
            ['school' => 'Liberty', 'nickname' => 'Flames'],
            ['school' => 'Yale', 'nickname' => 'Bulldogs'],
            ['school' => 'Texas Southern', 'nickname' => 'Tigers'],
        ];

        DB::table('ncaa_teams')->insert($teams);
    }
}
