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
        // First, modify game_datetime to be nullable to avoid default value issues
        Schema::table('games', function (Blueprint $table) {
            $table->timestamp('game_datetime')->nullable()->change();
        });

        // Then add the new columns
        Schema::table('games', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('games', 'home_q1_score')) {
                $table->integer('home_q1_score')->nullable()->after('home_team_score');
                $table->integer('home_q2_score')->nullable()->after('home_q1_score');
                $table->integer('home_q3_score')->nullable()->after('home_q2_score');
                $table->integer('home_q4_score')->nullable()->after('home_q3_score');
            }

            if (!Schema::hasColumn('games', 'visitor_q1_score')) {
                $table->integer('visitor_q1_score')->nullable()->after('visitor_team_score');
                $table->integer('visitor_q2_score')->nullable()->after('visitor_q1_score');
                $table->integer('visitor_q3_score')->nullable()->after('visitor_q2_score');
                $table->integer('visitor_q4_score')->nullable()->after('visitor_q3_score');
            }

            if (!Schema::hasColumn('games', 'game_description')) {
                $table->string('game_description')->nullable()->after('game_datetime');
            }

            if (!Schema::hasColumn('games', 'game_status')) {
                $table->string('game_status')->default('not_started')->after('game_description');
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
                'home_q1_score',
                'home_q2_score',
                'home_q3_score',
                'home_q4_score',
                'visitor_q1_score',
                'visitor_q2_score',
                'visitor_q3_score',
                'visitor_q4_score',
                'game_description',
                'game_status',
            ]);
        });
    }
};
