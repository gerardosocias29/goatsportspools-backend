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
        Schema::table('squares_pools', function (Blueprint $table) {
            // Add initial_credits - amount to auto-credit when user joins pool
            if (!Schema::hasColumn('squares_pools', 'initial_credits')) {
                $table->integer('initial_credits')->default(0)->after('credit_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            if (Schema::hasColumn('squares_pools', 'initial_credits')) {
                $table->dropColumn('initial_credits');
            }
        });
    }
};
