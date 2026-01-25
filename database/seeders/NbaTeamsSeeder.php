<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class NbaTeamsSeeder extends Seeder
{
    /**
     * Seed all 30 NBA teams with logos and backgrounds.
     * Uses official NBA CDN for logos.
     */
    public function run(): void
    {
        $teams = [
            // Eastern Conference - Atlantic Division
            [
                'name' => 'Boston Celtics',
                'code' => 'BOS',
                'conference' => 'Eastern',
                'nickname' => 'Celtics',
                'nba_id' => 1610612738,
            ],
            [
                'name' => 'Brooklyn Nets',
                'code' => 'BKN',
                'conference' => 'Eastern',
                'nickname' => 'Nets',
                'nba_id' => 1610612751,
            ],
            [
                'name' => 'New York Knicks',
                'code' => 'NYK',
                'conference' => 'Eastern',
                'nickname' => 'Knicks',
                'nba_id' => 1610612752,
            ],
            [
                'name' => 'Philadelphia 76ers',
                'code' => 'PHI',
                'conference' => 'Eastern',
                'nickname' => '76ers',
                'nba_id' => 1610612755,
            ],
            [
                'name' => 'Toronto Raptors',
                'code' => 'TOR',
                'conference' => 'Eastern',
                'nickname' => 'Raptors',
                'nba_id' => 1610612761,
            ],

            // Eastern Conference - Central Division
            [
                'name' => 'Chicago Bulls',
                'code' => 'CHI',
                'conference' => 'Eastern',
                'nickname' => 'Bulls',
                'nba_id' => 1610612741,
            ],
            [
                'name' => 'Cleveland Cavaliers',
                'code' => 'CLE',
                'conference' => 'Eastern',
                'nickname' => 'Cavaliers',
                'nba_id' => 1610612739,
            ],
            [
                'name' => 'Detroit Pistons',
                'code' => 'DET',
                'conference' => 'Eastern',
                'nickname' => 'Pistons',
                'nba_id' => 1610612765,
            ],
            [
                'name' => 'Indiana Pacers',
                'code' => 'IND',
                'conference' => 'Eastern',
                'nickname' => 'Pacers',
                'nba_id' => 1610612754,
            ],
            [
                'name' => 'Milwaukee Bucks',
                'code' => 'MIL',
                'conference' => 'Eastern',
                'nickname' => 'Bucks',
                'nba_id' => 1610612749,
            ],

            // Eastern Conference - Southeast Division
            [
                'name' => 'Atlanta Hawks',
                'code' => 'ATL',
                'conference' => 'Eastern',
                'nickname' => 'Hawks',
                'nba_id' => 1610612737,
            ],
            [
                'name' => 'Charlotte Hornets',
                'code' => 'CHA',
                'conference' => 'Eastern',
                'nickname' => 'Hornets',
                'nba_id' => 1610612766,
            ],
            [
                'name' => 'Miami Heat',
                'code' => 'MIA',
                'conference' => 'Eastern',
                'nickname' => 'Heat',
                'nba_id' => 1610612748,
            ],
            [
                'name' => 'Orlando Magic',
                'code' => 'ORL',
                'conference' => 'Eastern',
                'nickname' => 'Magic',
                'nba_id' => 1610612753,
            ],
            [
                'name' => 'Washington Wizards',
                'code' => 'WAS',
                'conference' => 'Eastern',
                'nickname' => 'Wizards',
                'nba_id' => 1610612764,
            ],

            // Western Conference - Northwest Division
            [
                'name' => 'Denver Nuggets',
                'code' => 'DEN',
                'conference' => 'Western',
                'nickname' => 'Nuggets',
                'nba_id' => 1610612743,
            ],
            [
                'name' => 'Minnesota Timberwolves',
                'code' => 'MIN',
                'conference' => 'Western',
                'nickname' => 'Timberwolves',
                'nba_id' => 1610612750,
            ],
            [
                'name' => 'Oklahoma City Thunder',
                'code' => 'OKC',
                'conference' => 'Western',
                'nickname' => 'Thunder',
                'nba_id' => 1610612760,
            ],
            [
                'name' => 'Portland Trail Blazers',
                'code' => 'POR',
                'conference' => 'Western',
                'nickname' => 'Trail Blazers',
                'nba_id' => 1610612757,
            ],
            [
                'name' => 'Utah Jazz',
                'code' => 'UTA',
                'conference' => 'Western',
                'nickname' => 'Jazz',
                'nba_id' => 1610612762,
            ],

            // Western Conference - Pacific Division
            [
                'name' => 'Golden State Warriors',
                'code' => 'GSW',
                'conference' => 'Western',
                'nickname' => 'Warriors',
                'nba_id' => 1610612744,
            ],
            [
                'name' => 'Los Angeles Clippers',
                'code' => 'LAC',
                'conference' => 'Western',
                'nickname' => 'Clippers',
                'nba_id' => 1610612746,
            ],
            [
                'name' => 'Los Angeles Lakers',
                'code' => 'LAL',
                'conference' => 'Western',
                'nickname' => 'Lakers',
                'nba_id' => 1610612747,
            ],
            [
                'name' => 'Phoenix Suns',
                'code' => 'PHX',
                'conference' => 'Western',
                'nickname' => 'Suns',
                'nba_id' => 1610612756,
            ],
            [
                'name' => 'Sacramento Kings',
                'code' => 'SAC',
                'conference' => 'Western',
                'nickname' => 'Kings',
                'nba_id' => 1610612758,
            ],

            // Western Conference - Southwest Division
            [
                'name' => 'Dallas Mavericks',
                'code' => 'DAL',
                'conference' => 'Western',
                'nickname' => 'Mavericks',
                'nba_id' => 1610612742,
            ],
            [
                'name' => 'Houston Rockets',
                'code' => 'HOU',
                'conference' => 'Western',
                'nickname' => 'Rockets',
                'nba_id' => 1610612745,
            ],
            [
                'name' => 'Memphis Grizzlies',
                'code' => 'MEM',
                'conference' => 'Western',
                'nickname' => 'Grizzlies',
                'nba_id' => 1610612763,
            ],
            [
                'name' => 'New Orleans Pelicans',
                'code' => 'NOP',
                'conference' => 'Western',
                'nickname' => 'Pelicans',
                'nba_id' => 1610612740,
            ],
            [
                'name' => 'San Antonio Spurs',
                'code' => 'SAS',
                'conference' => 'Western',
                'nickname' => 'Spurs',
                'nba_id' => 1610612759,
            ],
        ];

        foreach ($teams as $teamData) {
            // NBA CDN logo URL
            $logoUrl = "https://cdn.nba.com/logos/nba/{$teamData['nba_id']}/global/L/logo.svg";

            Team::updateOrCreate(
                [
                    'name' => $teamData['name'],
                    'league' => 'NBA'
                ],
                [
                    'nickname' => $teamData['nickname'],
                    'code' => $teamData['code'],
                    'conference' => $teamData['conference'],
                    'league' => 'NBA',
                    'image_url' => $logoUrl,
                    'background_url' => null, // NBA doesn't have standard background images
                ]
            );
        }

        $this->command->info('Seeded 30 NBA teams successfully!');
    }
}
