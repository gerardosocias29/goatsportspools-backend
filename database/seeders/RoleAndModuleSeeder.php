<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Role, RoleModule};

class RoleAndModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing records to start fresh
        Role::truncate();
        RoleModule::truncate();

        // Define role modules first
        $modules = [
            // ['name' => 'Dashboard', 'page' => 'dashboard', 'icon' => 'icon-dashboard'],
            // ['name' => 'Games', 'page' => '', 'icon' => 'icon-running'],
            ['name' => 'NFL', 'page' => 'games/nfl', 'icon' => 'icon-nfl'],
            ['name' => 'Open Bets', 'page' => 'open-bets', 'icon' => 'icon-bet-history'],
            ['name' => 'Bet History', 'page' => 'bet-history', 'icon' => 'icon-bet-history'],
            ['name' => 'Users', 'page' => 'users', 'icon' => 'icon-users'],
            ['name' => 'Standings', 'page' => 'league-standings', 'icon' => 'icon-league-standings'],
            ['name' => 'Leagues', 'page' => 'leagues', 'icon' => 'icon-leagues'],

            ['name' => 'Settings', 'page' => '', 'icon' => 'icon-settings'],
            ['name' => 'How It Works', 'page' => 'how-it-works', 'icon' => 'icon-hiw'],

            ['name' => 'Manage Games', 'page' => 'settings/game-management', 'icon' => 'icon-game-management', 'parent_id' => 7],
            ['name' => 'Manage Teams', 'page' => 'settings/teams', 'icon' => 'icon-teams', 'parent_id' => 7],
        ];

        // Create the modules and store the IDs
        $moduleIds = [];
        foreach ($modules as $module) {
            $createdModule = RoleModule::create($module);
            if(empty($createdModule->parent_id)){
                $moduleIds[] = $createdModule->id;
            }
        }

        // Define roles with their respective descriptions and allowed module IDs
        $roles = [
            [
                'name' => 'Superadmin',
                'description' => 'Has access to all system features and settings.',
                'allowed_modules' => $moduleIds // Assign all module IDs to Superadmin
            ],
            [
                'name' => 'League Admin',
                'description' => 'Manages league settings and user permissions.',
                'allowed_modules' => [1,2,3,5,6,7,8] // Assign specific module IDs
            ],
            [
                'name' => 'Normal User',
                'description' => 'Has access to participate in NFL pool and view scores.',
                'allowed_modules' => [1,2,3,5,8]  // Assign specific module IDs
            ]
        ];

        // Create roles with the defined attributes
        foreach ($roles as $roleData) {
            Role::create([
                'name' => $roleData['name'],
                'description' => $roleData['description'],
                'allowed_modules' => $roleData['allowed_modules']
            ]);
        }
    }
}
