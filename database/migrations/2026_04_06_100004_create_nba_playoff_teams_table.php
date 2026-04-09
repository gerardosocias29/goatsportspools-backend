<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nba_playoff_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playoff_id')->constrained('nba_playoffs')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->enum('conference', ['East', 'West']);
            $table->tinyInteger('seed'); // 1-8

            // Per-round results: "seed of loser" — null means team lost that round (or round not yet played)
            $table->tinyInteger('r1_beat_seed')->nullable();
            $table->tinyInteger('r1_games')->nullable(); // 4-7
            $table->tinyInteger('r2_beat_seed')->nullable();
            $table->tinyInteger('r2_games')->nullable();
            $table->tinyInteger('r3_beat_seed')->nullable(); // conference finals
            $table->tinyInteger('r3_games')->nullable();
            $table->tinyInteger('finals_beat_seed')->nullable(); // NBA Finals (opponent from other conference)
            $table->tinyInteger('finals_games')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['playoff_id', 'conference', 'seed']);
            $table->unique(['playoff_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nba_playoff_teams');
    }
};
