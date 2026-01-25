<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix "Default - One Winner" reward distribution
        // Should be 100% to Final (Q4), not Q1
        DB::table('game_reward_types')
            ->where('id', 1)
            ->update([
                'reward1_percent' => 0,      // Q1: 0%
                'reward2_percent' => 0,      // Half/Q2: 0%
                'reward3_percent' => 0,      // Q3: 0%
                'reward4_percent' => 1.0000, // Final/Q4: 100%
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original (incorrect) state
        DB::table('game_reward_types')
            ->where('id', 1)
            ->update([
                'reward1_percent' => 1.0000, // Q1: 100%
                'reward2_percent' => 0,
                'reward3_percent' => 0,
                'reward4_percent' => 0,
                'updated_at' => now(),
            ]);
    }
};
