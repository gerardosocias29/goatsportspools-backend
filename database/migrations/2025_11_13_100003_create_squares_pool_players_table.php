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
        Schema::create('squares_pool_players', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pool_id')->constrained('squares_pools')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('users')->onDelete('cascade');

            // For CREDIT type pools
            $table->integer('credits_available')->default(0);

            // Track how many squares this player has claimed
            $table->integer('squares_count')->default(0);

            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // A player can only join a pool once
            $table->unique(['pool_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('squares_pool_players');
    }
};
