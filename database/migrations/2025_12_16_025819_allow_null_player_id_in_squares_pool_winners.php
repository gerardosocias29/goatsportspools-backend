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
        Schema::table('squares_pool_winners', function (Blueprint $table) {
            // Allow player_id to be null for unclaimed winning squares
            $table->unsignedBigInteger('player_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pool_winners', function (Blueprint $table) {
            // Revert player_id to NOT NULL (only if all player_id values are not null)
            $table->unsignedBigInteger('player_id')->nullable(false)->change();
        });
    }
};
