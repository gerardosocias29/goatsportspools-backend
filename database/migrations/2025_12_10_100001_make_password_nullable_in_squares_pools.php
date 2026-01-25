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
        Schema::table('squares_pools', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        // Update player_pool_type enum to include FREE
        DB::statement("ALTER TABLE squares_pools MODIFY COLUMN player_pool_type ENUM('OPEN', 'CREDIT', 'FREE') DEFAULT 'OPEN'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });

        // Revert player_pool_type enum
        DB::statement("ALTER TABLE squares_pools MODIFY COLUMN player_pool_type ENUM('OPEN', 'CREDIT') DEFAULT 'OPEN'");
    }
};
