<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playoff_brackets', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('status');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
            $table->foreignId('paid_by_admin_id')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();

            $table->index(['is_paid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('playoff_brackets', function (Blueprint $table) {
            $table->dropForeign(['paid_by_admin_id']);
            $table->dropIndex(['is_paid', 'status']);
            $table->dropColumn(['is_paid', 'paid_at', 'paid_by_admin_id']);
        });
    }
};
