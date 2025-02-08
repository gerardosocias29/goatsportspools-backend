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
        Schema::create('auction_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('auction_id');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('sold_to')->nullable();
            $table->double('starting_bid')->nullable();
            $table->double('minimum_bid')->nullable();
            $table->double('target_bid')->nullable();
            $table->enum('status', ['pending', 'active', 'sold', 'unsold', 'canceled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_items');
    }
};
