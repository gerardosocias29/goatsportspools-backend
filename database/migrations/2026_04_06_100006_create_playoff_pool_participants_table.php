<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playoff_pool_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('playoff_pools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('credits_available')->default(0);
            $table->tinyInteger('brackets_count')->default(0);
            $table->integer('total_points')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pool_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_pool_participants');
    }
};
