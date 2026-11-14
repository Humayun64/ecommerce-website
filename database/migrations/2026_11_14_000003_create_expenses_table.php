<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('spent_on');
            $table->string('category', 30);
            $table->decimal('amount', 12, 2);
            $table->string('note', 200)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('spent_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
