<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /** Statuses that count as real orders rather than cancelled ones. */
    private const LIVE = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    public function dashboard()
    {
        $orders = Order::where('user_id', auth()->id())->get();
        $live   = $orders->whereIn('status', self::LIVE);

        return view('site.account.dashboard', [
            'orderCount'  => $live->count(),
            'spent'       => (float) $live->sum('total'),
            'inProgress'  => $orders->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count(),
            'recent'      => Order::withCount('items')
                                ->where('user_id', auth()->id())
                                ->latest()->take(3)->get(),
            'address'     => Address::where('user_id', auth()->id())
                                ->orderByDesc('is_default')->first(),
            'unclaimed'   => $this->unclaimedCount(),
        ]);
    }

    public function orders()
    {
        return view('site.account.orders', [
            'orders' => Order::withCount('items')
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate(10),
            'unclaimed' => $this->unclaimedCount(),
        ]);
    }

    public function order(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load('items.product.primaryImage');

        return view('site.account.order', compact('order'));
    }

    /**
     * Guest orders carry no user_id. If the phone matches, the customer
     * can claim them so their history is complete.
     */
    public function claim()
    {
        $phone = $this->phone();

        if (! $phone) {
            return back()->with('error', __('Add your mobile number to your profile first.'));
        }

        $claimed = Order::whereNull('user_id')
            ->where('customer_phone', $phone)
            ->update(['user_id' => auth()->id()]);

        return back()->with('status', $claimed
            ? __(':n past order(s) added to your account.', ['n' => $claimed])
            : __('No guest orders found for your number.'));
    }

    /* ---------- addresses ---------- */

    public function addresses()
    {
        return view('site.account.addresses', [
            'addresses' => Address::where('user_id', auth()->id())
                ->orderByDesc('is_default')->get(),
            'zones' => ShippingZone::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $data = $this->validateAddress($request);

        if ($request->boolean('is_default')) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        Address::create($data + ['user_id' => auth()->id()]);

        return back()->with('status', __('Address saved.'));
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $data = $this->validateAddress($request);

        if ($request->boolean('is_default')) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('status', __('Address updated.'));
    }

    public function destroyAddress(Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $address->delete();

        return back()->with('status', __('Address removed.'));
    }

    /* ---------- helpers ---------- */

    private function phone(): ?string
    {
        $phone = preg_replace('/\D/', '', (string) auth()->user()->phone);

        return $phone ?: null;
    }

    private function unclaimedCount(): int
    {
        $phone = $this->phone();

        return $phone
            ? Order::whereNull('user_id')->where('customer_phone', $phone)->count()
            : 0;
    }

    private function validateAddress(Request $request): array
    {
        if ($request->phone) {
            $digits = preg_replace('/\D/', '', $request->phone);

            if (str_starts_with($digits, '880')) {
                $digits = '0' . substr($digits, 3);
            }

            $request->merge(['phone' => $digits]);
        }

        $data = $request->validate([
            'label'            => ['nullable', 'string', 'max:40'],
            'name'             => ['required', 'string', 'max:120'],
            'phone'            => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
            'address'          => ['required', 'string', 'max:500'],
            'area'             => ['nullable', 'string', 'max:120'],
            'shipping_zone_id' => ['nullable', 'exists:shipping_zones,id'],
        ], [
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, like 01347419040.'),
        ]);

        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }
}
