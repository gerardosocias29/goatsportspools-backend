<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'admin@example.com',
            'password' => bcrypt('P@ssW0rd!!'),
            'role_id' => 1
        ]);
    }
}
