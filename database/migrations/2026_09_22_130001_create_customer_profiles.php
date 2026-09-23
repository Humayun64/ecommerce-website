<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * A customer here is a phone number, not an account — most orders are
         * placed as guests, so the phone is the only thing that identifies the
         * same person across them. This table only holds what the shop decides
         * about them; the facts come from their orders.
         */
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 40)->unique();
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
