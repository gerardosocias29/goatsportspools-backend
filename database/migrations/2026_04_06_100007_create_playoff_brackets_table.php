<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playoff_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('playoff_pool_participants')->onDelete('cascade');
            $table->string('bracket_name')->default('Bracket 1');
            $table->tinyInteger('bracket_index'); // 1-8
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->integer('total_points')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['participant_id', 'bracket_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_brackets');
    }
};
