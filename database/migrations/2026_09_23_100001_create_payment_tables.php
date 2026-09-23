<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('tagline')->nullable();

            /**
             * cod      — pay the courier, nothing to enter
             * manual   — send money yourself, then type the transaction ID
             * gateway  — the customer is pushed to a payment page
             *
             * A gateway row can sit here switched off until the API is wired
             * up, which is why the column exists before any gateway does.
             */
            $table->string('driver', 20)->default('manual');
            $table->string('gateway', 40)->nullable();

            $table->string('account_number', 40)->nullable();
            $table->string('account_type', 40)->nullable();
            $table->text('instructions')->nullable();
            $table->string('logo')->nullable();
            $table->string('accent', 9)->nullable();

            $table->boolean('needs_sender')->default(true);
            $table->boolean('needs_txn')->default(true);

            $table->decimal('charge_percent', 5, 2)->default(0);
            $table->decimal('min_amount', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            // Gateway credentials later. Nothing is written here yet.
            $table->json('config')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            // Copied in, so the row still reads correctly if a method is renamed.
            $table->string('method_code', 40);
            $table->string('method_name');

            $table->decimal('amount', 10, 2)->default(0);
            $table->string('sender_number', 40)->nullable();
            $table->string('transaction_id', 80)->nullable();

            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            // For a gateway later: its own reference and whatever it sends back.
            $table->string('reference')->nullable();
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('transaction_id');

            // The same bKash transaction cannot be claimed on two orders.
            $table->unique(['method_code', 'transaction_id'], 'payments_txn_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};
