<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Set when the reviewer's phone matches a delivered order
            // containing this product. That is what earns the badge.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reviewer_name');
            $table->string('reviewer_phone', 40)->nullable();
            $table->string('reviewer_email')->nullable();

            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');

            $table->boolean('is_verified')->default(false);
            $table->string('status', 20)->default('pending');
            $table->text('admin_reply')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
