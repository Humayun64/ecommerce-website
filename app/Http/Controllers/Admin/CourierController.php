<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CourierManager;
use App\Services\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourierController extends Controller
{
    public function __construct(
        private CourierManager $couriers,
        private OrderStatus $statuses,
    ) {
    }

    public function index()
    {
        $rows = [];

        foreach ($this->couriers->all() as $code => $driver) {
            $creds = $this->couriers->creds($code);

            $rows[$code] = [
                'label'     => $driver->label(),
                'fields'    => $driver->fields(),
                'sandbox'   => $driver->hasSandbox(),
                'on'        => $creds['on'],
                'key'       => $creds['key'],
                'hasSecret' => $creds['secret'] !== '',
                'isSandbox' => $creds['sandbox'],
                'connected' => $this->couriers->connected($code),
            ];
        }

        return view('admin.couriers.index', [
            'couriers' => $rows,
            'inTransit' => Order::whereNotNull('courier_code')->where('status', 'shipped')->count(),
        ]);
    }

    public function update(Request $request, string $code)
    {
        abort_unless($this->couriers->has($code), 404);

        $data = $request->validate([
            'key'     => ['nullable', 'string', 'max:200'],
            'secret'  => ['nullable', 'string', 'max:2000'],
            'on'      => ['nullable', 'boolean'],
            'sandbox' => ['nullable', 'boolean'],
        ]);

        $this->couriers->save($code, $data);

        $label = $this->couriers->driver($code)->label();

        if ($request->boolean('on') && ! $this->couriers->connected($code)) {
            return back()->with('error', __(':name is switched on but its keys are not complete, so it will not be offered on orders yet.', ['name' => $label]));
        }

        return back()->with('status', __(':name saved.', ['name' => $label]));
    }

    /** Proves the saved keys work, without booking anything. */
    public function test(string $code)
    {
        abort_unless($this->couriers->has($code), 404);

        if (! $this->couriers->connected($code)) {
            return back()->with('error', __('Save the keys first, then test.'));
        }

        try {
            $message = $this->couriers->driver($code)->test($this->couriers->creds($code));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }

    /** Delivery areas for the picker on the order page. */
    public function areas(Request $request, string $code)
    {
        abort_unless($this->couriers->has($code), 404);

        try {
            $areas = $this->couriers->driver($code)->areas($this->couriers->creds($code), (string) $request->query('q', ''));
        } catch (\Throwable $e) {
            return response()->json(['areas' => [], 'error' => $e->getMessage()], 200);
        }

        return response()->json(['areas' => $areas]);
    }

    /** "Send to courier" on an order. */
    public function book(Request $request, Order $order)
    {
        $data = $request->validate([
            'courier_code' => ['required', 'string', 'max:30'],
            'cod'          => ['required', 'numeric', 'min:0', 'max:9999999'],
            'note'         => ['nullable', 'string', 'max:200'],
            'weight'       => ['nullable', 'integer', 'min:1', 'max:50000'],
            'area_id'      => ['nullable', 'integer'],
            'area_name'    => ['nullable', 'string', 'max:120'],
            'mark_shipped' => ['nullable', 'boolean'],
        ]);

        try {
            $this->couriers->book($order, $data['courier_code'], [
                'cod'       => (float) $data['cod'],
                'note'      => $data['note'] ?? null,
                'weight'    => (int) ($data['weight'] ?? 500),
                'area_id'   => $data['area_id'] ?? null,
                'area_name' => $data['area_name'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Courier booking failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', $e->getMessage());
        }

        $order->refresh();

        if ($request->boolean('mark_shipped') && in_array($order->status, ['pending', 'confirmed', 'processing'], true)) {
            $this->statuses->change($order, 'shipped');
        }

        return back()->with('status', __('Sent to :courier. Tracking code :code.', [
            'courier' => $order->courier, 'code' => $order->tracking_number,
        ]));
    }

    /** "Check status" on one order. */
    public function sync(Order $order)
    {
        try {
            $answer = $this->couriers->sync($order);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($answer['changed']) {
            return back()->with('status', __('The courier reports this parcel delivered, so the order is now Delivered.'));
        }

        if ($answer['state'] === 'problem') {
            return back()->with('error', __('The courier reports ":status". Check with them, then cancel or return the order here if needed.', ['status' => $answer['status']]));
        }

        return back()->with('status', __('Courier status: :status.', ['status' => $answer['status']]));
    }

    /** Check every parcel that is on the road, a batch at a time. */
    public function syncAll()
    {
        $orders = Order::whereNotNull('courier_code')
            ->where('status', 'shipped')
            ->orderBy('courier_synced_at')
            ->take(40)
            ->get();

        $delivered = 0;
        $problems  = 0;
        $failed    = 0;

        foreach ($orders as $order) {
            try {
                $answer = $this->couriers->sync($order);
                $delivered += $answer['changed'] ? 1 : 0;
                $problems  += $answer['state'] === 'problem' ? 1 : 0;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return back()->with('status', __('Checked :n parcel(s): :d delivered, :p needing attention, :f could not be checked.', [
            'n' => $orders->count(), 'd' => $delivered, 'p' => $problems, 'f' => $failed,
        ]));
    }
}
