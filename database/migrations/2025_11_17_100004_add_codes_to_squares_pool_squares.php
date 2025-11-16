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
        Schema::table('squares_pool_squares', function (Blueprint $table) {
            // Add visual codes for X and Y axes
            if (!Schema::hasColumn('squares_pool_squares', 'x_code')) {
                $table->string('x_code', 10)->nullable()->after('x_number');
            }

            if (!Schema::hasColumn('squares_pool_squares', 'y_code')) {
                $table->string('y_code', 10)->nullable()->after('y_number');
            }

            // Add selection datetime for tracking
            if (!Schema::hasColumn('squares_pool_squares', 'selection_date')) {
                $table->timestamp('selection_date')->nullable()->after('claimed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pool_squares', function (Blueprint $table) {
            $table->dropColumn(['x_code', 'y_code', 'selection_date']);
        });
    }
};
