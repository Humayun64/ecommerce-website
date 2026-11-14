<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ReturnRequest;
use App\Support\SalesMath;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Gathers the numbers for the sales report. The sums themselves live in
 * App\Support\SalesMath.
 */
class SalesReport
{
    private const COLUMNS = [
        'id', 'order_number', 'customer_name', 'customer_phone', 'shipping_zone_name',
        'subtotal', 'discount', 'delivery_charge', 'total', 'cost_total',
        'payment_method', 'payment_status', 'status', 'created_at',
    ];

    public const PRESETS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        '30_days'    => 'Last 30 days',
        '90_days'    => 'Last 90 days',
        'this_year'  => 'This year',
        'custom'     => 'Custom dates',
    ];

    /** @return array{0:Carbon, 1:Carbon, 2:string} */
    public function range(Request $request): array
    {
        $preset = $request->query('range', 'this_month');
        $preset = isset(self::PRESETS[$preset]) ? $preset : 'this_month';

        if ($preset === 'custom') {
            try {
                $from = Carbon::parse((string) $request->query('from'))->startOfDay();
                $to   = Carbon::parse((string) $request->query('to'))->endOfDay();
            } catch (\Throwable $e) {
                $from = $to = null;
            }

            // Anything unusable falls back to this month rather than erroring.
            if ($from && $to && $from->lte($to) && $from->diffInDays($to) <= 1100) {
                return [$from, $to, 'custom'];
            }

            $preset = 'this_month';
        }

        return match ($preset) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), $preset],
            '30_days'    => [now()->subDays(29)->startOfDay(), now()->endOfDay(), $preset],
            '90_days'    => [now()->subDays(89)->startOfDay(), now()->endOfDay(), $preset],
            'this_year'  => [now()->startOfYear(), now()->endOfDay(), $preset],
            default      => [now()->startOfMonth(), now()->endOfDay(), 'this_month'],
        };
    }

    public function orders(Carbon $from, Carbon $to)
    {
        return Order::whereBetween('created_at', [$from, $to])->orderBy('created_at')->get(self::COLUMNS);
    }

    public function build(Carbon $from, Carbon $to): array
    {
        $orders = $this->orders($from, $to);
        $rows   = $orders->map(fn ($o) => $o->only(['status', 'subtotal', 'discount', 'delivery_charge', 'cost_total']))->all();
        $totals = SalesMath::totals($rows);

        $refunds  = $this->refunds($from, $to);
        $expenses = (float) Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])->sum('amount');

        // The same number of days immediately before, for the "vs before" arrows.
        $days     = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $prevTo   = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        $before   = SalesMath::totals(
            $this->orders($prevFrom, $prevTo)->map(fn ($o) => $o->only(['status', 'subtotal', 'discount', 'delivery_charge', 'cost_total']))->all()
        );

        $live = $orders->reject(fn ($o) => in_array($o->status, SalesMath::DEAD, true));

        return [
            'totals'    => $totals,
            'refunds'   => $refunds,
            'expenses'  => $expenses,
            'net'       => SalesMath::net($totals, $refunds, $expenses),
            'days'      => $days,
            'change'    => [
                'net_sales'    => SalesMath::change($totals['net_sales'], $before['net_sales']),
                'orders'       => SalesMath::change($totals['orders'], $before['orders']),
                'gross_profit' => SalesMath::change($totals['gross_profit'], $before['gross_profit']),
            ],
            'trend'     => $this->trend($live, $from, $to, $days),
            'byPayment' => $this->group($live, fn ($o) => $o->payment_method, $this->paymentNames()),
            'byZone'    => $this->group($live, fn ($o) => $o->shipping_zone_name ?: 'No delivery area'),
            'byStatus'  => $orders->countBy('status')->all(),
            'customers' => $this->customers($live),
            'products'  => $this->products($from, $to),
            'expenseBy' => Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')
                ->pluck('total', 'category')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    /** Twelve calendar months, newest last, whatever range is on screen. */
    public function monthly(): array
    {
        $start  = now()->subMonthsNoOverflow(11)->startOfMonth();
        $orders = $this->orders($start, now()->endOfDay())->groupBy(fn ($o) => $o->created_at->format('Y-m'));

        $expenses = Expense::where('spent_on', '>=', $start->toDateString())->get(['spent_on', 'amount'])
            ->groupBy(fn ($e) => $e->spent_on->format('Y-m'))->map(fn ($g) => (float) $g->sum('amount'));

        $refunds = ReturnRequest::where('status', 'refunded')->where('refunded_at', '>=', $start)
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', SalesMath::DEAD))
            ->get(['refunded_at', 'refund_amount'])
            ->groupBy(fn ($r) => Carbon::parse($r->refunded_at)->format('Y-m'))->map(fn ($g) => (float) $g->sum('refund_amount'));

        $months = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonthsNoOverflow($i);
            $key   = $month->format('Y-m');
            $t     = SalesMath::totals(($orders[$key] ?? collect())->map(fn ($o) => $o->only(['status', 'subtotal', 'discount', 'delivery_charge', 'cost_total']))->all());

            $months[] = [
                'key'      => $key,
                'label'    => $month->format('M Y'),
                'totals'   => $t,
                'refunds'  => $refunds[$key] ?? 0.0,
                'expenses' => $expenses[$key] ?? 0.0,
                'net'      => SalesMath::net($t, $refunds[$key] ?? 0.0, $expenses[$key] ?? 0.0),
            ];
        }

        return $months;
    }

    /** Refunds paid in the range on orders that still count as sales. */
    private function refunds(Carbon $from, Carbon $to): float
    {
        return (float) ReturnRequest::where('status', 'refunded')
            ->whereBetween('refunded_at', [$from, $to])
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', SalesMath::DEAD))
            ->sum('refund_amount');
    }

    /** One bar per day for short ranges, per month for long ones. */
    private function trend($live, Carbon $from, Carbon $to, int $days): array
    {
        $daily  = $days <= 62;
        $format = $daily ? 'Y-m-d' : 'Y-m';
        $sums   = $live->groupBy(fn ($o) => $o->created_at->format($format))
            ->map(fn ($g) => (float) $g->sum(fn ($o) => (float) $o->subtotal - (float) $o->discount));

        $bars   = [];
        $cursor = $daily ? $from->copy()->startOfDay() : $from->copy()->startOfMonth();

        while ($cursor->lte($to) && count($bars) < 400) {
            $bars[] = [
                'label' => $cursor->format($daily ? 'j M' : 'M y'),
                'total' => $sums[$cursor->format($format)] ?? 0.0,
            ];
            $daily ? $cursor->addDay() : $cursor->addMonthNoOverflow();
        }

        return ['daily' => $daily, 'bars' => $bars];
    }

    private function group($live, callable $key, array $names = []): array
    {
        return $live->groupBy($key)->map(fn ($g, $k) => [
            'label'  => $names[$k] ?? ($k === '' || $k === null ? 'Unknown' : (string) $k),
            'orders' => $g->count(),
            'sales'  => (float) $g->sum(fn ($o) => (float) $o->subtotal - (float) $o->discount),
        ])->sortByDesc('sales')->values()->all();
    }

    private function customers($live): array
    {
        return $live->groupBy('customer_phone')->map(fn ($g) => [
            'name'   => $g->last()->customer_name,
            'phone'  => $g->first()->customer_phone,
            'orders' => $g->count(),
            'spent'  => (float) $g->sum(fn ($o) => (float) $o->subtotal - (float) $o->discount),
        ])->sortByDesc('spent')->take(10)->values()->all();
    }

    private function products(Carbon $from, Carbon $to)
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', SalesMath::DEAD)
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('order_items.name, SUM(order_items.quantity) as units')
            ->selectRaw('SUM(order_items.line_total) as revenue')
            ->selectRaw('SUM(CASE WHEN order_items.unit_cost IS NULL THEN 0 ELSE (order_items.unit_price - order_items.unit_cost) * order_items.quantity END) as profit')
            ->selectRaw('SUM(CASE WHEN order_items.unit_cost IS NULL THEN 1 ELSE 0 END) as uncosted')
            ->groupBy('order_items.name')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();
    }

    private function paymentNames(): array
    {
        return PaymentMethod::pluck('name', 'code')->all() + ['cod' => 'Cash on delivery'];
    }
}
