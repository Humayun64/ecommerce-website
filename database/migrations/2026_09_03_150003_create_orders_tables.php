<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();

            // Null for guest orders — the phone number is what identifies them.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone', 40);
            $table->string('customer_email')->nullable();

            $table->text('shipping_address');
            $table->string('shipping_area')->nullable();
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_zone_name')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Snapshot of what the goods cost us, so margin reporting stays
            // correct even after prices or supplier costs change.
            $table->decimal('cost_total', 12, 2)->nullable();

            $table->string('payment_method', 20)->default('cod');
            $table->string('payment_status', 20)->default('pending');

            $table->string('status', 20)->default('pending');
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();

            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('customer_phone');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Kept for reporting, but nulled rather than cascading if a
            // product is ever hard-deleted. The snapshot below is the record.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('variant_label')->nullable();
            $table->string('sku')->nullable();
            $table->string('brand_name')->nullable();

            $table->decimal('unit_price', 10, 2);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
