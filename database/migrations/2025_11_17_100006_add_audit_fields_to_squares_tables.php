<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: created_at and updated_at already exist from Laravel's timestamps()
     * We're adding user ID tracking and date-only fields for compatibility
     */
    public function up(): void
    {
        // Add audit fields to squares_pools
        Schema::table('squares_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('squares_pools', 'create_user_id')) {
                $table->unsignedBigInteger('create_user_id')->default(1)->after('id');
                $table->date('create_date')->nullable()->after('create_user_id');
                $table->unsignedBigInteger('modify_user_id')->nullable()->after('create_date');
                $table->date('modify_date')->nullable()->after('modify_user_id');

                $table->foreign('create_user_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('modify_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Add audit fields to squares_pool_squares
        Schema::table('squares_pool_squares', function (Blueprint $table) {
            if (!Schema::hasColumn('squares_pool_squares', 'create_user_id')) {
                $table->unsignedBigInteger('create_user_id')->default(1)->after('id');
                $table->date('create_date')->nullable()->after('create_user_id');
                $table->unsignedBigInteger('modify_user_id')->nullable()->after('create_date');
                $table->date('modify_date')->nullable()->after('modify_user_id');

                $table->foreign('create_user_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('modify_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Add audit fields to squares_pool_players
        Schema::table('squares_pool_players', function (Blueprint $table) {
            if (!Schema::hasColumn('squares_pool_players', 'create_user_id')) {
                $table->unsignedBigInteger('create_user_id')->default(1)->after('id');
                $table->date('create_date')->nullable()->after('create_user_id');
                $table->unsignedBigInteger('modify_user_id')->nullable()->after('create_date');
                $table->date('modify_date')->nullable()->after('modify_user_id');

                $table->foreign('create_user_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('modify_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Add audit fields to squares_pool_winners
        Schema::table('squares_pool_winners', function (Blueprint $table) {
            if (!Schema::hasColumn('squares_pool_winners', 'create_user_id')) {
                $table->unsignedBigInteger('create_user_id')->default(1)->after('id');
                $table->date('create_date')->nullable()->after('create_user_id');
                $table->unsignedBigInteger('modify_user_id')->nullable()->after('create_date');
                $table->date('modify_date')->nullable()->after('modify_user_id');

                $table->foreign('create_user_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('modify_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['squares_pools', 'squares_pool_squares', 'squares_pool_players', 'squares_pool_winners'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'create_user_id')) {
                    $blueprint->dropForeign(['create_user_id']);
                    $blueprint->dropForeign(['modify_user_id']);
                    $blueprint->dropColumn([
                        'create_user_id',
                        'create_date',
                        'modify_user_id',
                        'modify_date',
                    ]);
                }
            });
        }
    }
};
