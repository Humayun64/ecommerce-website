<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('phone', 32);
            $table->string('event', 40)->nullable();
            $table->text('message');

            $table->string('status', 20)->default('queued');
            $table->text('response')->nullable();
            $table->unsignedSmallInteger('parts')->default(1);

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('phone');

            // One message per order per event: a status saved twice by a
            // double-click must not send the customer two texts.
            $table->unique(['order_id', 'event'], 'sms_once_per_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
