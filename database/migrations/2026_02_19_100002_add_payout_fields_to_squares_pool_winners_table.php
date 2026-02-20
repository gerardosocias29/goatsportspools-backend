<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('squares_pool_winners', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('visitor_score');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
            $table->string('proof_image')->nullable()->after('paid_at');
            $table->unsignedBigInteger('paid_by')->nullable()->after('proof_image');
            $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('squares_pool_winners', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['is_paid', 'paid_at', 'proof_image', 'paid_by']);
        });
    }
};
