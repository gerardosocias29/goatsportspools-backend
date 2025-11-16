<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            // Add pool description
            if (!Schema::hasColumn('squares_pools', 'pool_description')) {
                $table->text('pool_description')->nullable()->after('pool_name');
            }

            // Add reward type enum
            if (!Schema::hasColumn('squares_pools', 'reward_type')) {
                $table->enum('reward_type', ['ComputeOnly', 'CreditsRewards'])
                    ->default('CreditsRewards')
                    ->after('player_pool_type');
            }

            // Add grid fee type for tracking free grids
            if (!Schema::hasColumn('squares_pools', 'grid_fee_type')) {
                $table->enum('grid_fee_type', ['Free', 'Min1', 'Min2', 'Standard'])
                    ->default('Free')
                    ->after('entry_fee');
            }

            // Add game nickname and description
            if (!Schema::hasColumn('squares_pools', 'game_nickname')) {
                $table->string('game_nickname')->nullable()->after('pool_description');
            }

            // Track which grid number this is for the admin (for fee calculation)
            if (!Schema::hasColumn('squares_pools', 'admin_grid_number')) {
                $table->integer('admin_grid_number')->default(1)->after('grid_fee_type');
            }

            // Add external pool ID for integrations
            if (!Schema::hasColumn('squares_pools', 'external_pool_id')) {
                $table->string('external_pool_id')->nullable()->after('pool_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            $table->dropColumn([
                'pool_description',
                'reward_type',
                'grid_fee_type',
                'game_nickname',
                'admin_grid_number',
                'external_pool_id',
            ]);
        });
    }
};
