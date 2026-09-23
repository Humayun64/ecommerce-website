<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\ImageService;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        private ReturnService $returns,
        private ImageService $images,
    ) {
    }

    /**
     * Who is allowed to open a return on this order.
     *
     * Either the signed-in owner, or someone who has just proved they know
     * the order number and the phone number on the tracking page. Most of
     * our customers are cash-on-delivery guests with no account, so the
     * second route matters more than the first.
     */
    private function mayTouch(Order $order): bool
    {
        if (auth()->check() && $order->user_id === auth()->id()) {
            return true;
        }

        return in_array($order->id, session('tracked_orders', []), true);
    }

    public function create(Order $order)
    {
        abort_unless($this->mayTouch($order), 403);

        $order->load('items');

        return view('site.returns.create', [
            'order'   => $order,
            'items'   => $this->returns->returnableItems($order),
            'blocked' => $this->returns->blockedReason($order),
            'closes'  => $this->returns->closesAt($order),
            'reasons' => ReturnRequest::REASONS,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        abort_unless($this->mayTouch($order), 403);

        $data = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'quantity'      => ['required', 'integer', 'min:1', 'max:99'],
            'reason'        => ['required', 'string'],
            'note'          => ['nullable', 'string', 'max:1500'],
            'photo'         => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->images->store($request->file('photo'), 'returns');
        }

        $return = $this->returns->open($order, $data);

        return redirect()
            ->route('returns.done', $return)
            ->with('status', __('Your return request has been sent.'));
    }

    public function done(ReturnRequest $return)
    {
        abort_unless($this->mayTouch($return->order), 403);

        return view('site.returns.done', ['row' => $return]);
    }

    /** The customer's own list, for the account dashboard. */
    public function mine()
    {
        $phone = auth()->user()->phone;

        $rows = ReturnRequest::with('order')
            ->where(function ($q) use ($phone) {
                $q->where('user_id', auth()->id());

                // Orders placed as a guest before they made the account.
                if ($phone) {
                    $q->orWhere('customer_phone', $phone);
                }
            })
            ->latest()
            ->paginate(10);

        return view('site.returns.mine', ['rows' => $rows]);
    }
}
