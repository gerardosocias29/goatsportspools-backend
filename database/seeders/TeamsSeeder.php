<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::truncate();

        $teams = [
            [
                'name' => 'Arizona Cardinals',
                'code' => 'ARI',
                'conference' => 'NFC',
                'nickname' => 'Cardinals',
            ],
            [
                'name' => 'Atlanta Falcons',
                'code' => 'ATL',
                'conference' => 'NFC',
                'nickname' => 'Falcons',
            ],
            [
                'name' => 'Baltimore Ravens',
                'code' => 'BAL',
                'conference' => 'AFC',
                'nickname' => 'Ravens',
            ],
            [
                'name' => 'Buffalo Bills',
                'code' => 'BUF',
                'conference' => 'AFC',
                'nickname' => 'Bills',
            ],
            [
                'name' => 'Carolina Panthers',
                'code' => 'CAR',
                'conference' => 'NFC',
                'nickname' => 'Panthers',
            ],
            [
                'name' => 'Chicago Bears',
                'code' => 'CHI',
                'conference' => 'NFC',
                'nickname' => 'Bears',
            ],
            [
                'name' => 'Cincinnati Bengals',
                'code' => 'CIN',
                'conference' => 'AFC',
                'nickname' => 'Bengals',
            ],
            [
                'name' => 'Cleveland Browns',
                'code' => 'CLE',
                'conference' => 'AFC',
                'nickname' => 'Browns',
            ],
            [
                'name' => 'Dallas Cowboys',
                'code' => 'DAL',
                'conference' => 'NFC',
                'nickname' => 'Cowboys',
            ],
            [
                'name' => 'Denver Broncos',
                'code' => 'DEN',
                'conference' => 'AFC',
                'nickname' => 'Broncos',
            ],
            [
                'name' => 'Detroit Lions',
                'code' => 'DET',
                'conference' => 'NFC',
                'nickname' => 'Lions',
            ],
            [
                'name' => 'Green Bay Packers',
                'code' => 'GB',
                'conference' => 'NFC',
                'nickname' => 'Packers',
            ],
            [
                'name' => 'Houston Texans',
                'code' => 'HOU',
                'conference' => 'AFC',
                'nickname' => 'Texans',
            ],
            [
                'name' => 'Indianapolis Colts',
                'code' => 'IND',
                'conference' => 'AFC',
                'nickname' => 'Colts',
            ],
            [
                'name' => 'Jacksonville Jaguars',
                'code' => 'JAX',
                'conference' => 'AFC',
                'nickname' => 'Jaguars',
            ],
            [
                'name' => 'Kansas City Chiefs',
                'code' => 'KC',
                'conference' => 'AFC',
                'nickname' => 'Chiefs',
            ],
            [
                'name' => 'Las Vegas Raiders',
                'code' => 'LV',
                'conference' => 'AFC',
                'nickname' => 'Raiders',
            ],
            [
                'name' => 'Los Angeles Chargers',
                'code' => 'LAC',
                'conference' => 'AFC',
                'nickname' => 'Chargers',
            ],
            [
                'name' => 'Los Angeles Rams',
                'code' => 'LAR',
                'conference' => 'NFC',
                'nickname' => 'Rams',
            ],
            [
                'name' => 'Miami Dolphins',
                'code' => 'MIA',
                'conference' => 'AFC',
                'nickname' => 'Dolphins',
            ],
            [
                'name' => 'Minnesota Vikings',
                'code' => 'MIN',
                'conference' => 'NFC',
                'nickname' => 'Vikings',
            ],
            [
                'name' => 'New England Patriots',
                'code' => 'NE',
                'conference' => 'AFC',
                'nickname' => 'Patriots',
            ],
            [
                'name' => 'New Orleans Saints',
                'code' => 'NO',
                'conference' => 'NFC',
                'nickname' => 'Saints',
            ],
            [
                'name' => 'New York Giants',
                'code' => 'NYG',
                'conference' => 'NFC',
                'nickname' => 'Giants',
            ],
            [
                'name' => 'New York Jets',
                'code' => 'NYJ',
                'conference' => 'AFC',
                'nickname' => 'Jets',
            ],
            [
                'name' => 'Philadelphia Eagles',
                'code' => 'PHI',
                'conference' => 'NFC',
                'nickname' => 'Eagles',
            ],
            [
                'name' => 'Pittsburgh Steelers',
                'code' => 'PIT',
                'conference' => 'AFC',
                'nickname' => 'Steelers',
            ],
            [
                'name' => 'San Francisco 49ers',
                'code' => 'SF',
                'conference' => 'NFC',
                'nickname' => '49ers',
            ],
            [
                'name' => 'Seattle Seahawks',
                'code' => 'SEA',
                'conference' => 'NFC',
                'nickname' => 'Seahawks',
            ],
            [
                'name' => 'Tampa Bay Buccaneers',
                'code' => 'TB',
                'conference' => 'NFC',
                'nickname' => 'Buccaneers',
            ],
            [
                'name' => 'Tennessee Titans',
                'code' => 'TEN',
                'conference' => 'AFC',
                'nickname' => 'Titans',
            ],
            [
                'name' => 'Washington Commanders',
                'code' => 'WAS',
                'conference' => 'NFC',
                'nickname' => 'Commanders',
            ],
        ];        
        
        foreach ($teams as $teamData) {
            Team::create([
                'name' => $teamData['name'],
                'nickname' => $teamData['nickname'],
                'code' => $teamData['code'],
                'conference' => $teamData['conference'],
                'image_url' => 'https://static.www.nfl.com/t_q-best/league/api/clubs/logos/'.$teamData['code'].'.png',
            ]);
        }
    }
}
