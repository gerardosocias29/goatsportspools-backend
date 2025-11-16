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
        Schema::table('squares_pool_players', function (Blueprint $table) {
            // Add join status for approval workflow
            if (!Schema::hasColumn('squares_pool_players', 'join_status')) {
                $table->enum('join_status', ['Pending', 'Approved', 'Denied'])
                    ->default('Approved')
                    ->after('player_id');
            }

            // Add status for active/inactive players
            if (!Schema::hasColumn('squares_pool_players', 'status')) {
                $table->enum('status', ['Active', 'Inactive'])
                    ->default('Active')
                    ->after('join_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pool_players', function (Blueprint $table) {
            $table->dropColumn(['join_status', 'status']);
        });
    }
};
