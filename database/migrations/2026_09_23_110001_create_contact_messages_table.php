<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('topic')->nullable();
            $table->string('order_number', 40)->nullable();
            $table->text('message');

            $table->string('status', 20)->default('new');
            $table->text('admin_note')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();

            // Kept so a flood of junk can be traced to one source.
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
