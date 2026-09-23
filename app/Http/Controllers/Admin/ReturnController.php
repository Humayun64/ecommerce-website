<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(private ReturnService $returns)
    {
    }

    public function index(Request $request)
    {
        $rows = ReturnRequest::with('order')
            ->status($request->input('status'))
            ->search($request->input('search'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.returns.index', [
            'rows'     => $rows,
            'counts'   => $this->returns->counts(),
            'refunded' => $this->returns->refundedTotal(),
            'statuses' => ReturnRequest::STATUSES,
        ]);
    }

    public function show(ReturnRequest $return)
    {
        $return->load(['order.items', 'item', 'user']);

        return view('admin.returns.show', [
            'row'      => $return,
            'statuses' => ReturnRequest::STATUSES,
        ]);
    }

    public function update(Request $request, ReturnRequest $return)
    {
        $data = $request->validate([
            'action'        => ['required', 'in:approve,reject,refund'],
            'admin_note'    => ['nullable', 'string', 'max:2000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'restock'       => ['nullable', 'boolean'],
        ]);

        $note = $data['admin_note'] ?? null;

        match ($data['action']) {
            'approve' => $this->returns->approve($return, $note),
            'reject'  => $this->returns->reject($return, $note),
            'refund'  => $this->returns->refund(
                $return,
                (float) ($data['refund_amount'] ?? $return->refund_amount),
                (bool) ($data['restock'] ?? false),
                $note
            ),
        };

        $said = [
            'approve' => __('Return approved. The customer can send the item back.'),
            'reject'  => __('Return rejected.'),
            'refund'  => __('Refund recorded.'),
        ];

        return redirect()
            ->route('admin.returns.show', $return)
            ->with('status', $said[$data['action']]);
    }
}
