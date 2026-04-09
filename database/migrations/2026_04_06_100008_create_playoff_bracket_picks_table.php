<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playoff_bracket_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bracket_id')->constrained('playoff_brackets')->onDelete('cascade');
            $table->enum('pick_type', ['round', 'champion']);
            $table->tinyInteger('round'); // 1, 2, 3 for conference rounds; 4 for champion
            $table->enum('conference', ['East', 'West'])->nullable(); // null for champion
            $table->foreignId('picked_team_id')->constrained('teams')->onDelete('cascade');
            $table->tinyInteger('picked_games')->nullable(); // 4-7, nullable for drafts

            // Scoring (populated by scoring service later, default 0)
            $table->integer('base_points')->default(0);
            $table->integer('games_bonus')->default(0);
            $table->integer('seed_bonus')->default(0);
            $table->timestamp('scored_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate team in same round/conference within a bracket
            $table->unique(['bracket_id', 'round', 'conference', 'picked_team_id'], 'pick_unique_team_round_conf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_bracket_picks');
    }
};
