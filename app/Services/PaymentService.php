<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /** Everything a customer may actually choose at checkout. */
    public function choices()
    {
        return PaymentMethod::active()->get()->filter(fn ($m) => $m->is_usable)->values();
    }

    public function find(?string $code): ?PaymentMethod
    {
        if (! $code) {
            return null;
        }

        return PaymentMethod::where('code', $code)->first();
    }

    /**
     * The method the customer picked, or a clear refusal.
     *
     * A gateway row that someone switched on before the API exists is
     * refused here rather than silently falling back to cash on delivery —
     * a customer who thinks they paid online and has not is a much worse
     * problem than a rejected checkout.
     */
    public function resolve(?string $code, float $orderTotal): PaymentMethod
    {
        $method = $this->find($code);

        if (! $method || ! $method->is_active) {
            throw ValidationException::withMessages([
                'payment_method_code' => __('Choose how you want to pay.'),
            ]);
        }

        if (! $method->is_usable) {
            throw ValidationException::withMessages([
                'payment_method_code' => __(':name is not available yet. Please pick another way to pay.', [
                    'name' => $method->name,
                ]),
            ]);
        }

        if ($method->min_amount !== null && $orderTotal < (float) $method->min_amount) {
            throw ValidationException::withMessages([
                'payment_method_code' => __('Orders under ৳:amount cannot use :name.', [
                    'amount' => number_format((float) $method->min_amount),
                    'name'   => $method->name,
                ]),
            ]);
        }

        return $method;
    }

    /**
     * What the customer must type in for this method. Cash on delivery asks
     * for nothing; a manual method asks for whatever it was set up to ask for.
     */
    public function rulesFor(PaymentMethod $method): array
    {
        if (! $method->is_manual) {
            return [];
        }

        $rules = [];

        if ($method->needs_sender) {
            $rules['sender_number'] = ['required', 'string', 'max:40'];
        }

        if ($method->needs_txn) {
            $rules['transaction_id'] = ['required', 'string', 'max:80'];
        }

        return $rules;
    }

    /**
     * Everything that can be checked before the order exists.
     *
     * Called first, because an order created and then refused for a duplicate
     * transaction ID would have taken stock for a sale that never happened.
     */
    public function precheck(PaymentMethod $method, array $input): void
    {
        $txn = $this->normaliseTxn($input['transaction_id'] ?? null);

        if ($method->is_manual && $method->needs_txn && $txn) {
            $this->guardDuplicate($method->code, $txn);
        }
    }

    /**
     * Record what the customer says they sent.
     *
     * Nothing here marks the order paid — a transaction ID is a claim, not a
     * receipt. It waits in the queue until someone opens the bKash app and
     * agrees the money arrived.
     */
    public function record(Order $order, PaymentMethod $method, array $input): Payment
    {
        $txn = $this->normaliseTxn($input['transaction_id'] ?? null);

        if ($method->is_manual && $method->needs_txn && $txn) {
            $this->guardDuplicate($method->code, $txn);
        }

        return DB::transaction(function () use ($order, $method, $input, $txn) {
            $payment = Payment::create([
                'order_id'          => $order->id,
                'payment_method_id' => $method->id,
                'method_code'       => $method->code,
                'method_name'       => $method->name,
                'amount'            => $order->total,
                'sender_number'     => $this->normalisePhone($input['sender_number'] ?? null),
                'transaction_id'    => $txn,
                // Cash on delivery has nothing to check until the courier
                // comes back, so it is not in the verification queue.
                'status'            => $method->is_manual ? 'pending' : 'verified',
            ]);

            $order->update([
                'payment_method' => $method->code,
                'payment_status' => 'pending',
            ]);

            return $payment;
        });
    }

    /** Yes, the money arrived. */
    public function verify(Payment $payment, ?int $userId = null, ?string $note = null): void
    {
        if ($payment->status === 'verified') {
            return;
        }

        DB::transaction(function () use ($payment, $userId, $note) {
            $payment->update([
                'status'      => 'verified',
                'admin_note'  => $note ?: $payment->admin_note,
                'verified_at' => now(),
                'verified_by' => $userId,
            ]);

            $payment->order?->update(['payment_status' => 'paid']);
        });
    }

    /** No, it did not — wrong amount, wrong number, or nothing there at all. */
    public function reject(Payment $payment, ?int $userId = null, ?string $note = null): void
    {
        DB::transaction(function () use ($payment, $userId, $note) {
            $payment->update([
                'status'      => 'rejected',
                'admin_note'  => $note ?: $payment->admin_note,
                'verified_at' => now(),
                'verified_by' => $userId,
            ]);

            // The order is unpaid again, but it is not cancelled — you will
            // usually be calling the customer about it, not binning it.
            $payment->order?->update(['payment_status' => 'pending']);
        });
    }

    /**
     * A transaction ID belongs to exactly one payment. Two orders claiming
     * the same one means either a typo or someone trying it on, and both
     * want a human to look rather than a quiet accept.
     */
    private function guardDuplicate(string $code, string $txn): void
    {
        $exists = Payment::where('method_code', $code)
            ->where('transaction_id', $txn)
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'transaction_id' => __('That transaction ID has already been used on another order. Check the ID and try again.'),
            ]);
        }
    }

    /** bKash prints them upper case; people type them however they like. */
    private function normaliseTxn(?string $txn): ?string
    {
        $txn = strtoupper(trim((string) $txn));

        return $txn === '' ? null : preg_replace('/\s+/', '', $txn);
    }

    private function normalisePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D/', '', (string) $phone);

        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }

    public function counts(): array
    {
        $rows = Payment::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return [
            'pending'  => (int) ($rows['pending'] ?? 0),
            'verified' => (int) ($rows['verified'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
        ];
    }

    public function verifiedTotal(): float
    {
        return (float) Payment::where('status', 'verified')->sum('amount');
    }
}
