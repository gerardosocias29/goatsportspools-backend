<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bet_groups', function(Blueprint $table){
            if (!Schema::hasColumn('bet_groups', 'wager_win_amount')) {
                $table->double('wager_win_amount')->after('wager_amount')->nullable();
            }
        });
        
        // MariaDB/MySQL compatible column rename
        if (Schema::hasColumn('bet_groups', 'bet_type') && !Schema::hasColumn('bet_groups', 'wager_type_id')) {
            DB::statement('ALTER TABLE bet_groups CHANGE bet_type wager_type_id VARCHAR(255)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bet_groups', function(Blueprint $table){
            if (Schema::hasColumn('bet_groups', 'wager_win_amount')) {
                $table->dropColumn('wager_win_amount');
            }
        });
        
        if (Schema::hasColumn('bet_groups', 'wager_type_id') && !Schema::hasColumn('bet_groups', 'bet_type')) {
            DB::statement('ALTER TABLE bet_groups CHANGE wager_type_id bet_type VARCHAR(255)');
        }
    }
};
