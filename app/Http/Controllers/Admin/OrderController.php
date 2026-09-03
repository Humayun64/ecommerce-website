<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\OrderMailer;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orders,
        private OrderMailer $mailer,
    ) {
    }

    public function index(Request $request)
    {
        $orders = Order::withCount('items')
            ->when($request->search, fn ($q, $term) => $q->where(fn ($sub) => $sub
                ->where('order_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_phone', 'like', "%{$term}%")))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->from, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->to, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    /** Status changes, with the timestamp and any restock they imply. */
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $from = $order->status;
        $to   = $data['status'];

        if ($from === $to) {
            return back();
        }

        $wasLive  = ! in_array($from, ['cancelled', 'returned']);
        $nowDead  = in_array($to, ['cancelled', 'returned']);
        $wasDead  = ! $wasLive;
        $nowLive  = ! $nowDead;

        $stamps = [
            'confirmed' => 'confirmed_at',
            'shipped'   => 'shipped_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
        ];

        $payload = ['status' => $to];

        if (isset($stamps[$to]) && ! $order->{$stamps[$to]}) {
            $payload[$stamps[$to]] = now();
        }

        // Delivered means the courier collected the cash.
        if ($to === 'delivered' && $order->payment_method === 'cod') {
            $payload['payment_status'] = 'paid';
        }

        $order->update($payload);

        // Stock goes back when an order dies, and comes off again if it is revived.
        if ($wasLive && $nowDead) {
            $this->orders->restock($order->load('items'));
            $message = __('Order cancelled and stock returned.');
        } elseif ($wasDead && $nowLive) {
            $message = __('Order reopened. Check stock — it was returned when the order was cancelled.');
        } else {
            $message = __('Status updated to :status.', ['status' => __(Order::STATUSES[$to])]);
        }

        $this->mailer->statusChanged($order->fresh(), $to);

        return back()->with('status', $message);
    }

    public function updateDelivery(Request $request, Order $order)
    {
        $data = $request->validate([
            'courier'         => ['nullable', 'string', 'max:80'],
            'tracking_number' => ['nullable', 'string', 'max:80'],
            'admin_note'      => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update($data);

        return back()->with('status', __('Order details saved.'));
    }

    public function invoice(Order $order)
    {
        $order->load('items');

        return view('admin.orders.invoice', compact('order'));
    }

    /* ---------- manual orders ---------- */

    public function create()
    {
        return view('admin.orders.create', [
            'zones'    => ShippingZone::active()->orderBy('sort_order')->get(),
            'products' => $this->sellableOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_phone'   => ['required', 'string', 'max:40'],
            'customer_email'   => ['nullable', 'email', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_area'    => ['nullable', 'string', 'max:120'],
            'shipping_zone_id' => ['nullable', 'exists:shipping_zones,id'],
            'delivery_charge'  => ['nullable', 'numeric', 'min:0'],
            'discount'         => ['nullable', 'numeric', 'min:0'],
            'admin_note'       => ['nullable', 'string', 'max:1000'],
            'status'           => ['required', Rule::in(array_keys(Order::STATUSES))],

            'lines'                => ['required', 'array', 'min:1'],
            'lines.*.picker'       => ['nullable', 'string'],
            'lines.*.quantity'     => ['nullable', 'integer', 'min:1', 'max:999'],
            'lines.*.unit_price'   => ['nullable', 'numeric', 'min:0'],
        ]);

        // The picker sends "productId:variantId" so one select can offer
        // every buyable thing, sizes included.
        $lines = [];

        foreach ($data['lines'] as $line) {
            if (empty($line['picker'])) {
                continue;
            }

            [$productId, $variantId] = array_pad(explode(':', $line['picker']), 2, null);

            $lines[] = [
                'product_id' => $productId,
                'variant_id' => $variantId ?: null,
                'quantity'   => $line['quantity'] ?? 1,
                'unit_price' => $line['unit_price'] ?? null,
            ];
        }

        $phone = preg_replace('/\D/', '', $data['customer_phone']);

        if (str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        try {
            $order = $this->orders->placeManual($lines, [
                'customer_name'    => $data['customer_name'],
                'customer_phone'   => $phone,
                'customer_email'   => $data['customer_email'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'shipping_area'    => $data['shipping_area'] ?? null,
                'shipping_zone_id' => $data['shipping_zone_id'] ?? null,
                'delivery_charge'  => $data['delivery_charge'] ?? null,
                'discount'         => $data['discount'] ?? 0,
                'status'           => $data['status'],
                'admin_note'       => $data['admin_note'] ?? null,
            ]);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('status', __('Order :number created.', ['number' => $order->order_number]));
    }

    /** Every buyable line: simple products, plus each variant separately. */
    private function sellableOptions()
    {
        $options = [];

        $products = Product::with(['variants', 'brand'])
            ->where('is_active', true)->orderBy('name')->get();

        foreach ($products as $product) {
            if ($product->has_variants) {
                foreach ($product->variants as $variant) {
                    $options[] = [
                        'value' => $product->id . ':' . $variant->id,
                        'label' => $product->name . ' — ' . $variant->name,
                        'price' => (float) $variant->price,
                        'stock' => (int) $variant->stock,
                    ];
                }
            } else {
                $options[] = [
                    'value' => $product->id . ':',
                    'label' => $product->name,
                    'price' => (float) $product->price,
                    'stock' => (int) $product->stock,
                ];
            }
        }

        return $options;
    }
}
