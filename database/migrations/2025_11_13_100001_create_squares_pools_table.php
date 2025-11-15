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
        Schema::create('squares_pools', function (Blueprint $table) {
            $table->id();

            // Admin and Game
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');

            // Pool Identity
            $table->string('pool_number')->unique(); // For joining
            $table->string('password'); // For joining
            $table->string('pool_name');

            // Pool Type Configuration
            // pool_type: A (immediate numbers), B (auto assign after close), C (manual assign), D (winner/loser)
            $table->enum('pool_type', ['A', 'B', 'C', 'D'])->default('A');

            // Player Pool Type: OPEN (free) or CREDIT (requires credits)
            $table->enum('player_pool_type', ['OPEN', 'CREDIT'])->default('OPEN');

            // Teams
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('visitor_team_id')->nullable()->constrained('teams')->onDelete('cascade');

            // Numbers (JSON arrays of 10 numbers each)
            $table->json('x_numbers')->nullable(); // Array of 10 numbers [0-9]
            $table->json('y_numbers')->nullable(); // Array of 10 numbers [0-9]
            $table->boolean('numbers_assigned')->default(false);

            // Cost and Limits
            $table->decimal('entry_fee', 10, 2)->default(0.00);
            $table->integer('max_squares_per_player')->nullable(); // null = no limit
            $table->integer('credit_cost')->nullable(); // For CREDIT type, cost per square (1-10)

            // Timing
            $table->timestamp('close_datetime')->nullable(); // For types B and C
            $table->timestamp('number_assign_datetime')->nullable(); // For type B (auto assign)

            // Status
            $table->enum('pool_status', ['open', 'closed', 'in_progress', 'completed'])->default('open');

            // QR Code
            $table->string('qr_code_url')->nullable();

            // Rewards (percentages for each quarter)
            $table->decimal('reward1_percent', 5, 2)->default(25.00); // Q1
            $table->decimal('reward2_percent', 5, 2)->default(25.00); // Q2 (Halftime)
            $table->decimal('reward3_percent', 5, 2)->default(25.00); // Q3
            $table->decimal('reward4_percent', 5, 2)->default(25.00); // Q4 (Final)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('squares_pools');
    }
};
