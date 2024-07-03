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
        Schema::table('odds', function (Blueprint $table) {
            $table->double('favored_points')->change();
            $table->double('underdog_points')->change();
            $table->double('favored_ml')->change();
            $table->double('underdog_ml')->change();
            $table->double('over_total')->change();
            $table->double('under_total')->change();
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
