<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Copied at request time so the row still reads correctly if the
            // product is renamed or deleted later.
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone', 32);
            $table->string('customer_email')->nullable();

            $table->unsignedInteger('quantity')->default(1);
            $table->string('reason');
            $table->text('note')->nullable();
            $table->string('photo')->nullable();

            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->boolean('restocked')->default(false);

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('customer_phone');
        });

        // One open request per line item: stops a customer sending the same
        // item three times while the first is still being looked at.
        Schema::table('return_requests', function (Blueprint $table) {
            $table->index(['order_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
