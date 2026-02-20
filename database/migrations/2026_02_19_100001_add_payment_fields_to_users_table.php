<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('clerk_id'); // 'gcash', 'maya', 'bank'
            $table->string('payment_account_name')->nullable()->after('payment_type');
            $table->string('payment_account_number')->nullable()->after('payment_account_name');
            $table->string('payment_bank_name')->nullable()->after('payment_account_number'); // only for bank type
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'payment_account_name', 'payment_account_number', 'payment_bank_name']);
        });
    }
};
