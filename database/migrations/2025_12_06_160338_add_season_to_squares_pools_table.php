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
            $table->string('season', 10)->default('2025')->after('pool_name');
            $table->string('league', 10)->nullable()->after('season'); // NFL, NBA, PBA
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            $table->dropColumn(['season', 'league']);
        });
    }
};
