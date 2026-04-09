<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playoff_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('playoff_id')->constrained('nba_playoffs')->onDelete('cascade');

            // Pool identity
            $table->string('pool_number', 6)->unique();
            $table->string('password')->nullable();
            $table->string('pool_name');
            $table->text('pool_description')->nullable();

            // Credits & brackets
            $table->integer('initial_credits')->default(0);
            $table->integer('credit_cost_per_bracket')->default(0); // 0 = free brackets
            $table->tinyInteger('max_brackets_per_user')->default(8);

            // Timing
            $table->timestamp('close_datetime')->nullable();
            $table->timestamp('locked_at')->nullable();

            // Status
            $table->enum('pool_status', ['open', 'locked', 'in_progress', 'completed'])->default('open');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_pools');
    }
};
