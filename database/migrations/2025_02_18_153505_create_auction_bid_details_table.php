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
        Schema::create('auction_bid_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('auction_id')->nullable();
            $table->integer('ncaa_team_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->decimal('starting_bid', 10, 2)->default(1)->nullable();
            $table->decimal('minimum_bid', 10, 2)->default(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_bid_details');
    }
};
