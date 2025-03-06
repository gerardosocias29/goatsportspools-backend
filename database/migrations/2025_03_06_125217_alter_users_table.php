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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('escrow_amount');
            $table->dropColumn('total_budget');
        });

        Schema::table('auction_users', function (Blueprint $table) {
            $table->double('escrow_amount')->nullable();
            $table->double('total_budget')->nullable();
            $table->enum('status', ["joined","away"])->default('away')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
