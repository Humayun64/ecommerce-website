<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            // A cart belongs to a user once they log in, or to a session token
            // while they are still a guest. Never both.
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('token', 64)->nullable()->unique();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // One line per product/variant pair — adding the same thing twice
            // increases the quantity instead of creating a second row.
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_line_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
