<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds an aamarPay row to the payment methods, switched off. It only becomes
 * usable once a store ID and signature key are entered in the admin panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('payment_methods')->where('code', 'aamarpay')->exists()) {
            return;
        }

        DB::table('payment_methods')->insert([
            'code'         => 'aamarpay',
            'name'         => 'aamarPay',
            'tagline'      => 'Cards, bKash, Nagad, Rocket and internet banking.',
            'driver'       => 'gateway',
            'gateway'      => 'aamarpay',
            'needs_sender' => false,
            'needs_txn'    => false,
            'is_active'    => false,
            'is_default'   => false,
            'sort_order'   => 60,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('payment_methods')
            ->where('code', 'aamarpay')
            ->whereNotExists(fn ($q) => $q->from('payments')->whereColumn('payments.payment_method_id', 'payment_methods.id'))
            ->delete();
    }
};
