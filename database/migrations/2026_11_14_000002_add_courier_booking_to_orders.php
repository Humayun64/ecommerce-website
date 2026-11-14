<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** What a courier's API gave back when an order was booked with it. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_code', 30)->nullable()->after('tracking_number');
            $table->string('courier_ref', 80)->nullable()->after('courier_code');
            $table->string('courier_status', 60)->nullable()->after('courier_ref');
            $table->timestamp('courier_synced_at')->nullable()->after('courier_status');

            $table->index(['courier_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['courier_code', 'status']);
            $table->dropColumn(['courier_code', 'courier_ref', 'courier_status', 'courier_synced_at']);
        });
    }
};
