<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nba_playoffs', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year');
            $table->string('name')->default('NBA Playoffs');
            $table->enum('status', ['upcoming', 'in_progress', 'completed'])->default('upcoming');
            $table->tinyInteger('current_round')->default(0); // 0=not started, 1-4
            $table->timestamps();
            $table->softDeletes();

            $table->unique('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nba_playoffs');
    }
};
