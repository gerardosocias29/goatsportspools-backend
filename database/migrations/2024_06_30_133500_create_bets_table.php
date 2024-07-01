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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('user_id');
            $table->integer('pool_id');
            $table->integer('league_id');
            $table->integer('wager_type_id');
            $table->integer('odd_id');
            $table->string('team_pick');
            $table->string('picked_odd');
            $table->double('wager_amount');
            $table->string('wager_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
