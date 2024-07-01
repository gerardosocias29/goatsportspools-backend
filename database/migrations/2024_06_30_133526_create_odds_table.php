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
        Schema::create('odds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('game_id');
            $table->integer('favored_team_id');
            $table->integer('underdog_team_id');
            $table->integer('favored_points');
            $table->integer('underdog_points');
            $table->integer('favored_ml');
            $table->integer('underdog_ml');
            $table->integer('over_total');
            $table->integer('under_total');
            $table->integer('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};
