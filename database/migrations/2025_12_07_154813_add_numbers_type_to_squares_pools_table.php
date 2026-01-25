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
            if (!Schema::hasColumn('squares_pools', 'numbers_type')) {
                // AdminTrigger = manual trigger by admin
                // TimeSet = auto-assign at scheduled time
                // Ascending = 0-9 in order (no randomization)
                $table->string('numbers_type', 20)->default('AdminTrigger')->after('pool_type');
            }
        });

        // Update existing pools based on pool_type
        // A = Ascending, B = TimeSet, C/D = AdminTrigger
        \DB::statement("UPDATE squares_pools SET numbers_type = 'Ascending' WHERE pool_type = 'A' AND numbers_type = 'AdminTrigger'");
        \DB::statement("UPDATE squares_pools SET numbers_type = 'TimeSet' WHERE pool_type = 'B' AND numbers_type = 'AdminTrigger'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squares_pools', function (Blueprint $table) {
            if (Schema::hasColumn('squares_pools', 'numbers_type')) {
                $table->dropColumn('numbers_type');
            }
        });
    }
};
