<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Statuses that represent real money, not cancelled or returned. */
    private const LIVE = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    public function index()
    {
        $products = Product::with('variants')->get();
        $costed   = $products->filter(fn ($p) => $p->margin_percent !== null);

        $monthOrders = Order::whereIn('status', self::LIVE)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();

        $revenue = (float) $monthOrders->sum('subtotal');
        $profit  = (float) $monthOrders->filter(fn ($o) => $o->cost_total !== null)
            ->sum(fn ($o) => (float) $o->subtotal - (float) $o->cost_total);

        return view('admin.dashboard', [
            // catalog
            'productCount'  => $products->count(),
            'categoryCount' => Category::count(),
            'brandCount'    => Brand::count(),
            'lowStock'      => $products->filter(fn ($p) => $p->is_low_stock)->take(6),
            'outOfStock'    => $products->filter(fn ($p) => $p->is_out_of_stock)->count(),
            'stockValue'    => $products->sum(fn ($p) => $p->stock_value),
            'missingCost'   => $products->count() - $costed->count(),

            // money
            'revenue'       => $revenue,
            'profit'        => $profit,
            'orderCount'    => $monthOrders->count(),
            'avgOrder'      => $monthOrders->count() ? $revenue / $monthOrders->count() : 0,

            // work waiting
            'pending'       => Order::where('status', 'pending')->count(),
            'toShip'        => Order::whereIn('status', ['confirmed', 'processing'])->count(),
            'stale'         => Order::where('status', 'shipped')
                                    ->where('shipped_at', '<', now()->subDays(3))->count(),

            'recentOrders'  => Order::latest()->take(8)->get(),
            'chart'         => $this->dailySales(),
            'topProducts'   => $this->topProducts(),
        ]);
    }

    /** Last 14 days of revenue, for the bar chart. */
    private function dailySales(): array
    {
        $rows = Order::whereIn('status', self::LIVE)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(subtotal) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key  = $date->format('Y-m-d');

            $days[] = [
                'label' => $date->format('j M'),
                'total' => (float) ($rows[$key] ?? 0),
            ];
        }

        return $days;
    }

    /** Ranked by profit, not units — the list that should drive reordering. */
    private function topProducts()
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', self::LIVE)
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->selectRaw('order_items.name, SUM(order_items.quantity) as units')
            ->selectRaw('SUM(order_items.line_total) as revenue')
            ->selectRaw('SUM((order_items.unit_price - COALESCE(order_items.unit_cost, order_items.unit_price)) * order_items.quantity) as profit')
            ->groupBy('order_items.name')
            ->orderByDesc('profit')
            ->take(6)
            ->get();
    }
}
