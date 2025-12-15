<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add cumulative score fields for Squares Pool feature
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Q1 cumulative scores
            if (!Schema::hasColumn('games', 'q1_home')) {
                $table->integer('q1_home')->nullable()->after('home_q4_score');
            }
            if (!Schema::hasColumn('games', 'q1_visitor')) {
                $table->integer('q1_visitor')->nullable()->after('q1_home');
            }

            // Halftime (Q1+Q2) cumulative scores
            if (!Schema::hasColumn('games', 'half_home')) {
                $table->integer('half_home')->nullable()->after('q1_visitor');
            }
            if (!Schema::hasColumn('games', 'half_visitor')) {
                $table->integer('half_visitor')->nullable()->after('half_home');
            }

            // Q3 (Q1+Q2+Q3) cumulative scores
            if (!Schema::hasColumn('games', 'q3_home')) {
                $table->integer('q3_home')->nullable()->after('half_visitor');
            }
            if (!Schema::hasColumn('games', 'q3_visitor')) {
                $table->integer('q3_visitor')->nullable()->after('q3_home');
            }

            // Final (full game) scores
            if (!Schema::hasColumn('games', 'final_home')) {
                $table->integer('final_home')->nullable()->after('q3_visitor');
            }
            if (!Schema::hasColumn('games', 'final_visitor')) {
                $table->integer('final_visitor')->nullable()->after('final_home');
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
                'q1_home',
                'q1_visitor',
                'half_home',
                'half_visitor',
                'q3_home',
                'q3_visitor',
                'final_home',
                'final_visitor',
            ]);
        });
    }
};
