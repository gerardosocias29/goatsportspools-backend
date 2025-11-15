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
        Schema::create('squares_pool_winners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pool_id')->constrained('squares_pools')->onDelete('cascade');
            $table->foreignId('square_id')->constrained('squares_pool_squares')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('users')->onDelete('cascade');

            // Which quarter this win is for (1, 2, 3, 4)
            $table->integer('quarter');

            // Prize amount for this win
            $table->decimal('prize_amount', 10, 2);

            // Scores at this quarter
            $table->integer('home_score')->nullable();
            $table->integer('visitor_score')->nullable();

            $table->timestamps();

            // A pool can only have one winner per quarter
            $table->unique(['pool_id', 'quarter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('squares_pool_winners');
    }
};
