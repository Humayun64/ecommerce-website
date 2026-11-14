<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SalesReport;
use App\Support\SalesMath;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private SalesReport $report)
    {
    }

    public function index(Request $request)
    {
        [$from, $to, $preset] = $this->report->range($request);

        return view('admin.reports.index', [
            'from'    => $from,
            'to'      => $to,
            'preset'  => $preset,
            'presets' => SalesReport::PRESETS,
            'report'  => $this->report->build($from, $to),
            'months'  => $this->report->monthly(),
            'query'   => ['range' => $preset, 'from' => $from->toDateString(), 'to' => $to->toDateString()],
        ]);
    }

    /** A spreadsheet of the orders in the range, or of the twelve months. */
    public function export(Request $request)
    {
        [$from, $to] = $this->report->range($request);

        if ($request->query('type') === 'months') {
            $name = 'monthly-summary-' . now()->format('Y-m-d') . '.csv';
            $head = ['Month', 'Orders', 'Cancelled', 'Returned', 'Sales', 'Discounts', 'Net sales', 'Delivery collected', 'Cost of goods', 'Gross profit', 'Margin %', 'Refunds', 'Expenses', 'Net profit'];
            $rows = array_map(fn ($m) => [
                $m['label'], $m['totals']['orders'], $m['totals']['cancelled'], $m['totals']['returned'],
                $this->n($m['totals']['sales']), $this->n($m['totals']['discounts']), $this->n($m['totals']['net_sales']),
                $this->n($m['totals']['delivery']), $this->n($m['totals']['cost']), $this->n($m['totals']['gross_profit']),
                $m['totals']['margin'] ?? '', $this->n($m['refunds']), $this->n($m['expenses']), $this->n($m['net']),
            ], $this->report->monthly());
        } else {
            $name = 'orders-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.csv';
            $head = ['Order', 'Date', 'Customer', 'Phone', 'Delivery area', 'Status', 'Payment', 'Payment status', 'Subtotal', 'Discount', 'Delivery', 'Total', 'Cost of goods', 'Gross profit'];
            $rows = $this->report->orders($from, $to)->map(fn ($o) => [
                $o->order_number, $o->created_at->format('Y-m-d H:i'), $o->customer_name, $o->customer_phone,
                $o->shipping_zone_name, Order::STATUSES[$o->status] ?? $o->status, $o->payment_method, $o->payment_status,
                $this->n($o->subtotal), $this->n($o->discount), $this->n($o->delivery_charge), $this->n($o->total),
                $o->cost_total === null ? '' : $this->n($o->cost_total),
                $o->cost_total === null || in_array($o->status, SalesMath::DEAD, true) ? '' : $this->n((float) $o->subtotal - (float) $o->discount - (float) $o->cost_total),
            ])->all();
        }

        return response()->streamDownload(function () use ($head, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // so Excel reads Bangla names correctly
            fputcsv($out, $head);

            foreach ($rows as $row) {
                fputcsv($out, array_map([SalesMath::class, 'csvCell'], $row));
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function n($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
