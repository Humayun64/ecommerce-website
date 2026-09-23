<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments)
    {
    }

    public function index(Request $request)
    {
        // Unchecked payments first — they are the only ones that need you.
        $rows = Payment::with(['order', 'verifier'])
            ->status($request->input('status'))
            ->search($request->input('search'))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'rows'     => $rows,
            'counts'   => $this->payments->counts(),
            'takings'  => $this->payments->verifiedTotal(),
            'statuses' => Payment::STATUSES,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'action'     => ['required', 'in:verify,reject'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $note = $data['admin_note'] ?? null;

        if ($data['action'] === 'verify') {
            $this->payments->verify($payment, auth()->id(), $note);
            $said = __('Payment verified. The order is now marked paid.');
        } else {
            $this->payments->reject($payment, auth()->id(), $note);
            $said = __('Payment rejected. The order is back to unpaid.');
        }

        return back()->with('status', $said);
    }
}
