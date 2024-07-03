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
        Schema::create('bet_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('user_id');
            $table->enum('bet_type', ['Teaser', 'Parlay']);
            $table->double('wager_amount');
            $table->double('adjustment')->comment('Only used for Teasers');
            $table->string('wager_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_groups');
    }
};
