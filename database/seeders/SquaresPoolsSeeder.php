<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SquaresPool;
use App\Models\SquaresPoolSquare;
use App\Models\SquaresPoolPlayer;
use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SquaresPoolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user (first user)
        $admin = User::first();
        if (!$admin) {
            $this->command->error('No users found. Please create at least one user first.');
            return;
        }

        // Get some teams
        $chiefs = Team::where('code', 'KC')->first();
        $niners = Team::where('code', 'SF')->first();
        $bills = Team::where('code', 'BUF')->first();
        $eagles = Team::where('code', 'PHI')->first();

        if (!$chiefs || !$niners) {
            $this->command->error('Teams not found. Please run TeamsSeeder first.');
            return;
        }

        // Create some sample games if they don't exist
        $game1 = Game::create([
            'game_datetime' => Carbon::now()->addWeeks(2)->setTime(18, 30, 0),
            'time_zone' => -5,
            'league_id' => 1,
            'home_team_id' => $chiefs->id,
            'visitor_team_id' => $niners->id,
            'location' => 'Allegiant Stadium',
            'city' => 'Las Vegas',
            'state' => 'NV',
            'home_team_score' => 0,
            'visitor_team_score' => 0,
            'game_description' => 'Super Bowl LIX',
            'game_status' => 'not_started',
            'standard_odd' => 100,
        ]);

        $game2 = Game::create([
            'game_datetime' => Carbon::now()->next('Sunday')->setTime(13, 0, 0),
            'time_zone' => -5,
            'league_id' => 1,
            'home_team_id' => $bills->id,
            'visitor_team_id' => $eagles->id,
            'location' => 'Highmark Stadium',
            'city' => 'Buffalo',
            'state' => 'NY',
            'home_team_score' => 0,
            'visitor_team_score' => 0,
            'game_description' => 'AFC Championship',
            'game_status' => 'not_started',
            'standard_odd' => 100,
        ]);

        // Create Pool 1: Type A (immediate numbers), OPEN select, 30 squares claimed
        $this->createPool([
            'admin_id' => $admin->id,
            'game_id' => $game1->id,
            'pool_name' => 'Super Bowl LIX - Open Pool',
            'password' => 'password123',
            'pool_type' => 'A',
            'player_pool_type' => 'OPEN',
            'home_team_id' => $chiefs->id,
            'visitor_team_id' => $niners->id,
            'entry_fee' => 10.00,
            'max_squares_per_player' => null, // No limit
            'credit_cost' => null,
            'reward1_percent' => 25.00,
            'reward2_percent' => 25.00,
            'reward3_percent' => 25.00,
            'reward4_percent' => 25.00,
        ], 30, true);

        // Create Pool 2: Type B (auto assign after close), OPEN select, 50 squares claimed
        $this->createPool([
            'admin_id' => $admin->id,
            'game_id' => $game1->id,
            'pool_name' => 'Super Bowl LIX - Numbers After Close',
            'password' => 'test456',
            'pool_type' => 'B',
            'player_pool_type' => 'OPEN',
            'home_team_id' => $chiefs->id,
            'visitor_team_id' => $niners->id,
            'entry_fee' => 20.00,
            'max_squares_per_player' => 5,
            'credit_cost' => null,
            'close_datetime' => Carbon::now()->addDays(7),
            'number_assign_datetime' => Carbon::now()->addDays(7)->addHours(1),
            'reward1_percent' => 20.00,
            'reward2_percent' => 30.00,
            'reward3_percent' => 20.00,
            'reward4_percent' => 30.00,
        ], 50, false);

        // Create Pool 3: Type C (manual assign), CREDIT select, 15 squares claimed
        $this->createPool([
            'admin_id' => $admin->id,
            'game_id' => $game2->id,
            'pool_name' => 'AFC Championship - Credit Pool',
            'password' => 'credits789',
            'pool_type' => 'C',
            'player_pool_type' => 'CREDIT',
            'home_team_id' => $bills->id,
            'visitor_team_id' => $eagles->id,
            'entry_fee' => 5.00,
            'max_squares_per_player' => 10,
            'credit_cost' => 2,
            'close_datetime' => Carbon::now()->addDays(3),
            'reward1_percent' => 25.00,
            'reward2_percent' => 25.00,
            'reward3_percent' => 25.00,
            'reward4_percent' => 25.00,
        ], 15, false, true);

        // Create Pool 4: Type A, OPEN, Full pool (100 squares)
        $this->createPool([
            'admin_id' => $admin->id,
            'game_id' => $game2->id,
            'pool_name' => 'AFC Championship - Full Pool',
            'password' => 'full100',
            'pool_type' => 'A',
            'player_pool_type' => 'OPEN',
            'home_team_id' => $bills->id,
            'visitor_team_id' => $eagles->id,
            'entry_fee' => 2.00,
            'max_squares_per_player' => null,
            'credit_cost' => null,
            'reward1_percent' => 25.00,
            'reward2_percent' => 25.00,
            'reward3_percent' => 25.00,
            'reward4_percent' => 25.00,
        ], 100, true);

        $this->command->info('Squares pools seeded successfully!');
    }

    /**
     * Create a pool with squares and optional claims
     */
    private function createPool(array $poolData, int $squaresClaimed = 0, bool $assignNumbers = false, bool $isCredit = false)
    {
        // Generate pool number
        $poolNumber = SquaresPool::generatePoolNumber();

        // Hash password
        $poolData['password'] = Hash::make($poolData['password']);
        $poolData['pool_number'] = $poolNumber;
        $poolData['pool_status'] = $squaresClaimed === 100 ? 'closed' : 'open';

        // Create the pool
        $pool = SquaresPool::create($poolData);

        // Create 100 squares
        $squares = [];
        for ($y = 0; $y < 10; $y++) {
            for ($x = 0; $x < 10; $x++) {
                $squares[] = [
                    'pool_id' => $pool->id,
                    'x_coordinate' => $x,
                    'y_coordinate' => $y,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        SquaresPoolSquare::insert($squares);

        // Assign numbers if requested (Type A)
        if ($assignNumbers) {
            $xNumbers = collect(range(0, 9))->shuffle()->values()->toArray();
            $yNumbers = collect(range(0, 9))->shuffle()->values()->toArray();

            $pool->update([
                'x_numbers' => $xNumbers,
                'y_numbers' => $yNumbers,
                'numbers_assigned' => true,
            ]);

            // Update squares with numbers
            $allSquares = SquaresPoolSquare::where('pool_id', $pool->id)->get();
            foreach ($allSquares as $square) {
                $square->update([
                    'x_number' => $xNumbers[$square->x_coordinate],
                    'y_number' => $yNumbers[$square->y_coordinate],
                ]);
            }
        }

        // Claim some squares
        if ($squaresClaimed > 0) {
            $users = User::limit(10)->get();
            if ($users->isEmpty()) {
                return;
            }

            $allSquares = SquaresPoolSquare::where('pool_id', $pool->id)->get();
            $squaresToClaim = $allSquares->random(min($squaresClaimed, 100));

            foreach ($squaresToClaim as $square) {
                $user = $users->random();

                // Create or get player record
                $playerRecord = SquaresPoolPlayer::firstOrCreate(
                    [
                        'pool_id' => $pool->id,
                        'player_id' => $user->id,
                    ],
                    [
                        'credits_available' => $isCredit ? 20 : 0, // Give 20 credits for credit pools
                        'squares_count' => 0,
                    ]
                );

                // Claim the square
                $square->update([
                    'player_id' => $user->id,
                    'claimed_at' => now(),
                ]);

                // Update player record
                $playerRecord->increment('squares_count');
                if ($isCredit) {
                    $playerRecord->decrement('credits_available', $pool->credit_cost);
                }
            }
        }

        $this->command->info("Created pool '{$pool->pool_name}' (#{$poolNumber}) with {$squaresClaimed} squares claimed");
    }
}
