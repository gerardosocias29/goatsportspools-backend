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
        Schema::create('squares_pool_squares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pool_id')->constrained('squares_pools')->onDelete('cascade');

            // Position on grid (0-9)
            $table->integer('x_coordinate'); // 0-9
            $table->integer('y_coordinate'); // 0-9

            // Assigned numbers (nullable until numbers are assigned)
            $table->integer('x_number')->nullable(); // 0-9
            $table->integer('y_number')->nullable(); // 0-9

            // Player who claimed this square
            $table->foreignId('player_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('claimed_at')->nullable();

            $table->timestamps();

            // Unique constraint: each coordinate can only exist once per pool
            $table->unique(['pool_id', 'x_coordinate', 'y_coordinate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('squares_pool_squares');
    }
};
