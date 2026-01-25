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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Emoji or icon name
            $table->string('variant')->default('primary'); // primary, success, warning, info, promo
            $table->string('page')->default('all'); // all, home, squares, pools, dashboard, etc.
            $table->datetime('start_date')->nullable(); // null = show immediately
            $table->datetime('end_date')->nullable(); // null = show permanently
            $table->enum('status', ['active', 'hidden'])->default('active');
            $table->integer('priority')->default(0); // Higher priority shown first
            $table->boolean('dismissible')->default(false);
            $table->string('action_text')->nullable(); // Button text
            $table->string('action_url')->nullable(); // Button link
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'page']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
