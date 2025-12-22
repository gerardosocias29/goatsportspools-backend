<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set credit_cost to 0 for all FREE pools
     */
    public function up(): void
    {
        DB::table('squares_pools')
            ->where('player_pool_type', 'FREE')
            ->update(['credit_cost' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback - we don't know what the original values were
    }
};
