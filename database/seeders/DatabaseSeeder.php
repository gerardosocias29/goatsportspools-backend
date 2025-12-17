<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndModuleSeeder::class,
            WagerTypesSeeder::class,
            NcaaTeamsSeeder::class,
            SquaresPoolsSeeder::class, // Uncomment if you want to seed squares pools
            TeamsSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
