<?php

namespace App\Services;

use App\Models\DeliveryTier;
use App\Models\Setting;
use App\Models\ShippingZone;

/**
 * Works out the delivery charge for a set of items going to one zone.
 *
 * Every surface asks this one class — the cart estimate, the checkout page,
 * and the order transaction — so a customer can never be quoted one figure
 * and charged another.
 */
class DeliveryCalculator
{
    public const RULES = [
        'highest'      => 'Charge for the largest item only',
        'sum'          => 'Add up every item',
        'highest_plus' => 'Largest item, plus a little for each extra',
    ];

    private ?\Illuminate\Support\Collection $tiers = null;

    /**
     * @param array $lines  [['tier_id' => ?int, 'quantity' => int], …]
     * @return array{amount: float, free: bool, reason: string}
     */
    public function charge(array $lines, ?ShippingZone $zone, float $subtotal): array
    {
        $freeOver = (float) Setting::get('free_delivery_over', 0);

        if ($freeOver > 0 && $subtotal >= $freeOver) {
            return ['amount' => 0.0, 'free' => true, 'reason' => 'threshold'];
        }

        if (! $zone) {
            return ['amount' => 0.0, 'free' => false, 'reason' => 'no_zone'];
        }

        $lines = array_values(array_filter($lines, fn ($l) => (int) ($l['quantity'] ?? 0) > 0));

        if (! $lines) {
            return ['amount' => 0.0, 'free' => false, 'reason' => 'empty'];
        }

        $rule = (string) Setting::get('delivery_multi_rule', 'highest');

        $rates = [];
        $units = 0;

        foreach ($lines as $line) {
            $quantity = (int) $line['quantity'];
            $units   += $quantity;
            $rates[]  = ['rate' => $this->rateFor($line['tier_id'] ?? null, $zone), 'quantity' => $quantity];
        }

        $highest = max(array_column($rates, 'rate'));

        $amount = match ($rule) {
            'sum'          => array_sum(array_map(fn ($r) => $r['rate'] * $r['quantity'], $rates)),
            'highest_plus' => $highest + ((float) Setting::get('delivery_extra_per_item', 0) * max(0, $units - 1)),
            default        => $highest,
        };

        return ['amount' => round($amount, 2), 'free' => false, 'reason' => $rule];
    }

    /** What each zone would cost for this basket, for the checkout radios. */
    public function quoteZones(array $lines, float $subtotal): array
    {
        $quotes = [];

        foreach (ShippingZone::active()->orderBy('sort_order')->get() as $zone) {
            $quotes[$zone->id] = $this->charge($lines, $zone, $subtotal);
        }

        return $quotes;
    }

    /** The cheapest a basket could ship for — used for the cart estimate. */
    public function cheapest(array $lines, float $subtotal): array
    {
        $quotes = $this->quoteZones($lines, $subtotal);

        if (! $quotes) {
            return ['amount' => 0.0, 'free' => false, 'reason' => 'no_zone'];
        }

        usort($quotes, fn ($a, $b) => $a['amount'] <=> $b['amount']);

        return $quotes[0];
    }

    /** Cart items → the shape charge() wants. */
    public function linesFromCart($cart): array
    {
        return $cart->items->map(fn ($item) => [
            'tier_id'  => $item->product?->delivery_tier_id,
            'quantity' => (int) $item->quantity,
        ])->all();
    }

    /**
     * A product with no band set falls back to the default band, and a band
     * with no rate for this zone falls back to the zone's own base rate.
     */
    private function rateFor(?int $tierId, ShippingZone $zone): float
    {
        $tier = $this->tier($tierId) ?? $this->tier(null);

        if ($tier) {
            $rate = $tier->rateFor($zone->id);

            if ($rate !== null) {
                return $rate;
            }
        }

        return (float) $zone->rate;
    }

    private function tier(?int $tierId): ?DeliveryTier
    {
        if ($this->tiers === null) {
            $this->tiers = DeliveryTier::with('rates')->orderBy('sort_order')->get();
        }

        if ($tierId === null) {
            return $this->tiers->firstWhere('is_default', true) ?? $this->tiers->first();
        }

        return $this->tiers->firstWhere('id', $tierId);
    }
}
