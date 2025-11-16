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
        Schema::table('games', function (Blueprint $table) {
            // Add external game ID for API integrations
            if (!Schema::hasColumn('games', 'external_game_id')) {
                $table->string('external_game_id')->nullable()->after('id');
                $table->index('external_game_id');
            }

            // Add game nickname (e.g., "Super Bowl LVIII")
            if (!Schema::hasColumn('games', 'game_nickname')) {
                $table->string('game_nickname')->nullable()->after('game_description');
            }

            // Add game date description (e.g., "Week 10", "Championship Game")
            if (!Schema::hasColumn('games', 'game_date_description')) {
                $table->string('game_date_description')->nullable()->after('game_nickname');
            }

            // Add league field if it doesn't exist
            if (!Schema::hasColumn('games', 'league')) {
                $table->string('league')->nullable()->after('league_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'external_game_id',
                'game_nickname',
                'game_date_description',
                'league',
            ]);
        });
    }
};
