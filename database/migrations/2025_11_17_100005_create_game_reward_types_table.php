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
        Schema::create('game_reward_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();

            // Reward percentages (stored as decimals 0.0000 to 1.0000)
            $table->decimal('reward1_percent', 6, 4)->default(0); // Q1
            $table->decimal('reward2_percent', 6, 4)->default(0); // Q2/Half
            $table->decimal('reward3_percent', 6, 4)->default(0); // Q3
            $table->decimal('reward4_percent', 6, 4)->default(0); // Q4/Final
            $table->decimal('reward5_percent', 6, 4)->default(0); // Extra 1
            $table->decimal('reward6_percent', 6, 4)->default(0); // Extra 2
            $table->decimal('reward7_percent', 6, 4)->default(0); // Extra 3
            $table->decimal('reward8_percent', 6, 4)->default(0); // Extra 4
            $table->decimal('reward9_percent', 6, 4)->default(0); // Extra 5

            // Additional reward categories
            $table->decimal('reward_other_percent', 6, 4)->default(0);
            $table->decimal('reward_misc_percent', 6, 4)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });

        // Seed default reward types
        DB::table('game_reward_types')->insert([
            [
                'id' => 1,
                'name' => 'Default - One Winner',
                'description' => 'Single winner takes all',
                'reward1_percent' => 1.0000,
                'reward2_percent' => 0,
                'reward3_percent' => 0,
                'reward4_percent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Half and Final - 50/50',
                'description' => 'Half time and final score split evenly',
                'reward1_percent' => 0,
                'reward2_percent' => 0.5000,
                'reward3_percent' => 0,
                'reward4_percent' => 0.5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'All Quarters Equal',
                'description' => 'Each quarter gets 25%',
                'reward1_percent' => 0.2500,
                'reward2_percent' => 0.2500,
                'reward3_percent' => 0.2500,
                'reward4_percent' => 0.2500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Progressive - Growing Rewards',
                'description' => 'Q1: 10%, Q2: 20%, Q3: 30%, Q4: 40%',
                'reward1_percent' => 0.1000,
                'reward2_percent' => 0.2000,
                'reward3_percent' => 0.3000,
                'reward4_percent' => 0.4000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Add foreign key to squares_pools
        Schema::table('squares_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('squares_pools', 'game_reward_type_id')) {
                $table->foreignId('game_reward_type_id')
                    ->default(1)
                    ->after('reward_type')
                    ->constrained('game_reward_types')
                    ->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            if (Schema::hasColumn('squares_pools', 'game_reward_type_id')) {
                $table->dropForeign(['game_reward_type_id']);
                $table->dropColumn('game_reward_type_id');
            }
        });

        Schema::dropIfExists('game_reward_types');
    }
};
