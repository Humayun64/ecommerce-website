<?php

namespace App\Support;

/**
 * The arithmetic behind the sales report, kept free of the database so the
 * same sums are used for a date range, a single month and a CSV — and so
 * they can be checked on their own.
 *
 * The rules, in one place:
 *  - A cancelled or returned order earns nothing. It is counted, not summed.
 *  - Sales is what the products sold for. Delivery charges are shown
 *    separately because that money goes on to the courier.
 *  - Gross profit = sales − discounts − cost of the goods. An order whose
 *    products have no cost price saved cannot have a profit worked out, so
 *    it is left out of the profit figure and counted as "uncosted".
 */
class SalesMath
{
    public const DEAD = ['cancelled', 'returned'];

    /**
     * @param  iterable<array{status:string, subtotal:mixed, discount:mixed, delivery_charge:mixed, cost_total:mixed}>  $orders
     */
    public static function totals(iterable $orders): array
    {
        $t = [
            'placed' => 0, 'orders' => 0, 'cancelled' => 0, 'returned' => 0,
            'sales' => 0.0, 'discounts' => 0.0, 'delivery' => 0.0,
            'cost' => 0.0, 'costed_net' => 0.0, 'uncosted' => 0,
        ];

        foreach ($orders as $o) {
            $t['placed']++;

            if ($o['status'] === 'cancelled') { $t['cancelled']++; continue; }
            if ($o['status'] === 'returned')  { $t['returned']++;  continue; }

            $sub  = (float) $o['subtotal'];
            $disc = (float) $o['discount'];

            $t['orders']++;
            $t['sales']     += $sub;
            $t['discounts'] += $disc;
            $t['delivery']  += (float) $o['delivery_charge'];

            if ($o['cost_total'] === null) {
                $t['uncosted']++;
            } else {
                $t['cost']       += (float) $o['cost_total'];
                $t['costed_net'] += $sub - $disc;
            }
        }

        $t['net_sales']    = $t['sales'] - $t['discounts'];
        $t['gross_profit'] = $t['costed_net'] - $t['cost'];
        $t['margin']       = $t['costed_net'] > 0 ? round($t['gross_profit'] / $t['costed_net'] * 100, 1) : null;
        $t['average']      = $t['orders'] > 0 ? $t['net_sales'] / $t['orders'] : 0.0;
        $t['cancel_rate']  = $t['placed'] > 0 ? round($t['cancelled'] / $t['placed'] * 100, 1) : 0.0;

        return $t;
    }

    /** What is left after refunds and running costs. */
    public static function net(array $totals, float $refunds, float $expenses): float
    {
        return $totals['gross_profit'] - $refunds - $expenses;
    }

    /** Percentage change from the previous period, or null when there is nothing to compare with. */
    public static function change(float $now, float $before): ?float
    {
        return abs($before) < 0.005 ? null : round(($now - $before) / abs($before) * 100, 1);
    }

    /**
     * A cell that starts with = + - or @ is run as a formula by Excel.
     * A leading apostrophe makes it plain text.
     */
    public static function csvCell($value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($value)
            ? "'" . $value
            : $value;
    }
}
