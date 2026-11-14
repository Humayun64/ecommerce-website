<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\GatewayManager;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Paying online: sending the customer to the provider, and dealing with
 * what comes back.
 */
class GatewayPaymentController extends Controller
{
    public function __construct(
        private GatewayManager $gateways,
        private PaymentService $payments,
    ) {
    }

    /**
     * "Pay now" on an order that is not paid yet — a first try that failed,
     * or a customer who closed the provider's page.
     */
    public function start(string $number)
    {
        $order = Order::where('order_number', $number)->firstOrFail();

        abort_unless($this->mayPay($order), 403);

        $method = PaymentMethod::where('code', $order->payment_method)->first();

        if ($order->payment_status === 'paid' || in_array($order->status, ['cancelled', 'returned'], true)
            || ! $method || ! $method->is_gateway || ! $method->is_usable) {
            return redirect()->route('checkout.done', $order->order_number)
                ->with('status', __('This order cannot be paid online right now.'));
        }

        return $this->send($order, $method);
    }

    /** Open a session with the provider and send the customer there. */
    public function send(Order $order, PaymentMethod $method)
    {
        $payment = $this->payments->openAttempt($order, $method);
        $token   = self::token($payment);

        $back = fn (string $outcome) => route('pay.back', [
            'payment' => $payment->id, 'token' => $token, 'outcome' => $outcome,
        ]);

        try {
            $url = $this->gateways->for($method)->start($method->credentials(), $order, $payment, [
                'success' => $back('success'),
                'fail'    => $back('fail'),
                'cancel'  => $back('cancel'),
                'ipn'     => route('pay.ipn', ['gateway' => $method->gateway]),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Payment gateway would not start', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            $this->payments->reject($payment, null, 'Could not open the payment page: ' . $e->getMessage());

            return redirect()->route('checkout.done', $order->order_number)
                ->with('status', __('We could not open the payment page. Your order is saved — please try paying again.'));
        }

        return redirect()->away($url);
    }

    /**
     * Where the provider sends the customer afterwards.
     *
     * This arrives as a POST from another website, so the browser does not
     * send our session cookie with it. The route therefore runs without the
     * session at all (see routes/web.php) and identifies the payment from
     * the signed token in its own address. Whatever the outcome in the
     * address says, the provider's server is asked before anything is paid.
     */
    public function back(Request $request, string $payment, string $token, string $outcome)
    {
        $payment = Payment::with('order', 'method')->findOrFail((int) $payment);

        abort_unless(hash_equals(self::token($payment), $token), 403);

        $order = $payment->order;

        if ($payment->status !== 'verified' && $outcome === 'success') {
            $this->settle($payment, $request->all());
        } elseif ($payment->status === 'pending') {
            $this->payments->reject($payment, null, $outcome === 'cancel'
                ? 'The customer cancelled on the payment page.'
                : 'The payment failed on the provider\'s page.');
        }

        // A signed link, so the confirmation page opens even if the browser
        // dropped the session on the way back.
        return redirect()->to(URL::temporarySignedRoute('pay.result', now()->addMinutes(30), [
            'number' => $order->order_number,
        ]));
    }

    /** The server-to-server notice, for when the customer never comes back. */
    public function ipn(Request $request, string $gateway)
    {
        $txn = (string) ($request->input('tran_id') ?? $request->input('mer_txnid') ?? '');

        $payment = $txn === '' ? null : Payment::with('order', 'method')
            ->where('transaction_id', $txn)
            ->whereHas('method', fn ($q) => $q->where('gateway', $gateway))
            ->first();

        if ($payment && $payment->status !== 'verified') {
            $this->settle($payment, $request->all());
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /** Lands the customer back on their confirmation page. */
    public function result(string $number)
    {
        $order = Order::where('order_number', $number)->firstOrFail();

        session()->put('recent_order', $order->id);

        return redirect()->route('checkout.done', $order->order_number);
    }

    /** Ask the provider, then mark the payment paid or leave it for a person. */
    private function settle(Payment $payment, array $input): void
    {
        $method  = $payment->method;
        $gateway = $method ? $this->gateways->for($method) : null;

        if (! $gateway) {
            return;
        }

        try {
            $answer = $gateway->confirm($method->credentials(), $payment, $input);
        } catch (\Throwable $e) {
            // The provider did not answer. The payment stays "waiting to be
            // checked" so it shows up on the admin Payments screen.
            Log::warning('Payment gateway would not confirm', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->note($payment, null, 'Could not confirm with the provider yet: ' . $e->getMessage(), []);

            return;
        }

        $this->payments->note($payment, $answer['reference'], $answer['note'], $answer['raw']);

        if ($answer['paid']) {
            $this->payments->verify($payment->fresh(), null, $answer['note']);
        }
        // Not confirmed: left as "waiting to be checked" rather than rejected,
        // because money may have moved and a person should look.
    }

    private function mayPay(Order $order): bool
    {
        return session('recent_order') === $order->id
            || (auth()->check() && $order->user_id === auth()->id())
            || in_array($order->id, session('tracked_orders', []), true);
    }

    public static function token(Payment $payment): string
    {
        return substr(hash_hmac('sha256', $payment->id . '|' . $payment->transaction_id, (string) config('app.key')), 0, 40);
    }
}
