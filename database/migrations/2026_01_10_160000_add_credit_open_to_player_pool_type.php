<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add CREDIT_OPEN to player_pool_type enum
        DB::statement("ALTER TABLE squares_pools MODIFY COLUMN player_pool_type ENUM('OPEN', 'CREDIT', 'FREE', 'CREDIT_OPEN') DEFAULT 'OPEN'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert player_pool_type enum (remove CREDIT_OPEN)
        DB::statement("ALTER TABLE squares_pools MODIFY COLUMN player_pool_type ENUM('OPEN', 'CREDIT', 'FREE') DEFAULT 'OPEN'");
    }
};
