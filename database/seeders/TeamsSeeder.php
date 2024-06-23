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
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/zglirxlttuukonnz1il0',
            ],
            [
                'name' => 'Atlanta Falcons',
                'code' => 'ATL',
                'conference' => 'NFC',
                'nickname' => 'Falcons',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/dgksdhs7zhmrioliyq9c',
            ],
            [
                'name' => 'Baltimore Ravens',
                'code' => 'BAL',
                'conference' => 'AFC',
                'nickname' => 'Ravens',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/o2k317ev82s6pa26dos7',
            ],
            [
                'name' => 'Buffalo Bills',
                'code' => 'BUF',
                'conference' => 'AFC',
                'nickname' => 'Bills',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/kujtrvt65vrfbzvlp9p7',
            ],
            [
                'name' => 'Carolina Panthers',
                'code' => 'CAR',
                'conference' => 'NFC',
                'nickname' => 'Panthers',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/eyotndxveoeyibcbag5k',
            ],
            [
                'name' => 'Chicago Bears',
                'code' => 'CHI',
                'conference' => 'NFC',
                'nickname' => 'Bears',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/egkztdkxkhpio4a3unxg',
            ],
            [
                'name' => 'Cincinnati Bengals',
                'code' => 'CIN',
                'conference' => 'AFC',
                'nickname' => 'Bengals',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/maacoshfmktzddy1ob56',
            ],
            [
                'name' => 'Cleveland Browns',
                'code' => 'CLE',
                'conference' => 'AFC',
                'nickname' => 'Browns',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/skdxhpn1dmksuyrtwc7q',
            ],
            [
                'name' => 'Dallas Cowboys',
                'code' => 'DAL',
                'conference' => 'NFC',
                'nickname' => 'Cowboys',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/q7qakjbkb5wt7q943epi',
            ],
            [
                'name' => 'Denver Broncos',
                'code' => 'DEN',
                'conference' => 'AFC',
                'nickname' => 'Broncos',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/zclpmokppqisomcdnnsh',
            ],
            [
                'name' => 'Detroit Lions',
                'code' => 'DET',
                'conference' => 'NFC',
                'nickname' => 'Lions',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/dlyxxmgt52d7anojsknk',
            ],
            [
                'name' => 'Green Bay Packers',
                'code' => 'GB',
                'conference' => 'NFC',
                'nickname' => 'Packers',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/nn9yhffph5ty4qj2gqwu',
            ],
            [
                'name' => 'Houston Texans',
                'code' => 'HOU',
                'conference' => 'AFC',
                'nickname' => 'Texans',
                'background_url' => 'https://static.www.nfl.com/image/upload/f_auto/league/j9yzmmknc8qsfhwr62o1',
            ],
            [
                'name' => 'Indianapolis Colts',
                'code' => 'IND',
                'conference' => 'AFC',
                'nickname' => 'Colts',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/q1v6xhz44symvucjoxym',
            ],
            [
                'name' => 'Jacksonville Jaguars',
                'code' => 'JAX',
                'conference' => 'AFC',
                'nickname' => 'Jaguars',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/wok03vphgjx4knlyggx4',
            ],
            [
                'name' => 'Kansas City Chiefs',
                'code' => 'KC',
                'conference' => 'AFC',
                'nickname' => 'Chiefs',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/ginftk4rv6gflay6mwur',
            ],
            [
                'name' => 'Las Vegas Raiders',
                'code' => 'LV',
                'conference' => 'AFC',
                'nickname' => 'Raiders',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/rthdytrwns8g5aed2uou',
            ],
            [
                'name' => 'Los Angeles Chargers',
                'code' => 'LAC',
                'conference' => 'AFC',
                'nickname' => 'Chargers',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/mkla4gj5w7882z6q0ywz',
            ],
            [
                'name' => 'Los Angeles Rams',
                'code' => 'LAR',
                'conference' => 'NFC',
                'nickname' => 'Rams',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/eo223qd5mlxrna3bbdiv',
            ],
            [
                'name' => 'Miami Dolphins',
                'code' => 'MIA',
                'conference' => 'AFC',
                'nickname' => 'Dolphins',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/xfxniandqn2wglltlxgm',
            ],
            [
                'name' => 'Minnesota Vikings',
                'code' => 'MIN',
                'conference' => 'NFC',
                'nickname' => 'Vikings',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/fnmak0n2pitv59qqmaen',
            ],
            [
                'name' => 'New England Patriots',
                'code' => 'NE',
                'conference' => 'AFC',
                'nickname' => 'Patriots',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/slea5gcxabohzknqnexo',
            ],
            [
                'name' => 'New Orleans Saints',
                'code' => 'NO',
                'conference' => 'NFC',
                'nickname' => 'Saints',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/qk0qesy5im61qccr5vp0',
            ],
            [
                'name' => 'New York Giants',
                'code' => 'NYG',
                'conference' => 'NFC',
                'nickname' => 'Giants',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/i94f24mjvgoc64hnqw3o',
            ],
            [
                'name' => 'New York Jets',
                'code' => 'NYJ',
                'conference' => 'AFC',
                'nickname' => 'Jets',
                'background_url' => 'https://static.www.nfl.com/image/upload/f_auto/league/exgaakjbdsxmk77dzsed',
            ],
            [
                'name' => 'Philadelphia Eagles',
                'code' => 'PHI',
                'conference' => 'NFC',
                'nickname' => 'Eagles',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/p8xl6x8jfe3acs71jtit',
            ],
            [
                'name' => 'Pittsburgh Steelers',
                'code' => 'PIT',
                'conference' => 'AFC',
                'nickname' => 'Steelers',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/jwzyzxrdfzpmlw35kn9u',
            ],
            [
                'name' => 'San Francisco 49ers',
                'code' => 'SF',
                'conference' => 'NFC',
                'nickname' => '49ers',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/xtuljknt1jfrerdujovg',
            ],
            [
                'name' => 'Seattle Seahawks',
                'code' => 'SEA',
                'conference' => 'NFC',
                'nickname' => 'Seahawks',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/eh8w95lu2enod7bb2jhe',
            ],
            [
                'name' => 'Tampa Bay Buccaneers',
                'code' => 'TB',
                'conference' => 'NFC',
                'nickname' => 'Buccaneers',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/a0fljvgs9wctbwkivftj',
            ],
            [
                'name' => 'Tennessee Titans',
                'code' => 'TEN',
                'conference' => 'AFC',
                'nickname' => 'Titans',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/gyav7vqi3lgt0zjtiz1g',
            ],
            [
                'name' => 'Washington Commanders',
                'code' => 'WAS',
                'conference' => 'NFC',
                'nickname' => 'Commanders',
                'background_url' => 'https://static.www.nfl.com/image/private/f_auto/league/um4nljrwmnb8xd01ywp0',
            ],
        ];

        foreach ($teams as $teamData) {
            Team::create([
                'name' => $teamData['name'],
                'nickname' => $teamData['nickname'],
                'code' => $teamData['code'],
                'conference' => $teamData['conference'],
                'image_url' => 'https://static.www.nfl.com/t_q-best/league/api/clubs/logos/'.$teamData['code'].'.png',
                'background_url' => $teamData['background_url'],
            ]);
        }
    }
}
