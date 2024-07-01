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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('game_datetime');
            $table->integer('time_zone');
            $table->integer('league_id');
            $table->integer('home_team_id');
            $table->integer('visitor_team_id');
            $table->string('location');
            $table->string('city');
            $table->string('state');
            $table->integer('home_team_score');
            $table->integer('visitor_team_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
