<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRate;
use App\Models\DeliveryTier;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Services\DeliveryCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index()
    {
        $tiers = DeliveryTier::with('rates')->orderBy('sort_order')->get();
        $zones = ShippingZone::orderBy('sort_order')->get();

        // [tierId][zoneId] => rate, so the matrix inputs fill themselves in.
        $matrix = [];

        foreach ($tiers as $tier) {
            foreach ($zones as $zone) {
                $matrix[$tier->id][$zone->id] = $tier->rates
                    ->firstWhere('shipping_zone_id', $zone->id)?->rate;
            }
        }

        return view('admin.delivery.index', [
            'tiers'    => $tiers,
            'zones'    => $zones,
            'matrix'   => $matrix,
            'settings' => Setting::all_cached(),
            'rules'    => DeliveryCalculator::RULES,
            'products' => Product::with('category')->orderBy('name')->get(),
            'unassigned' => Product::whereNull('delivery_tier_id')->count(),
        ]);
    }

    /** The matrix, the free-delivery threshold and the multi-item rule. */
    public function updateRates(Request $request)
    {
        $data = $request->validate([
            'rates'                   => ['array'],
            'rates.*.*'               => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'free_delivery_over'      => ['nullable', 'numeric', 'min:0'],
            'delivery_multi_rule'     => ['required', 'in:highest,sum,highest_plus'],
            'delivery_extra_per_item' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['rates'] ?? [] as $tierId => $byZone) {
                foreach ($byZone as $zoneId => $rate) {
                    if ($rate === null || $rate === '') {
                        DeliveryRate::where('delivery_tier_id', $tierId)
                            ->where('shipping_zone_id', $zoneId)->delete();
                        continue;
                    }

                    DeliveryRate::updateOrCreate(
                        ['delivery_tier_id' => $tierId, 'shipping_zone_id' => $zoneId],
                        ['rate' => $rate]
                    );
                }
            }

            Setting::put([
                'free_delivery_over'      => $data['free_delivery_over'] ?? 0,
                'delivery_multi_rule'     => $data['delivery_multi_rule'],
                'delivery_extra_per_item' => $data['delivery_extra_per_item'] ?? 0,
            ]);
        });

        return back()->with('status', __('Delivery charges saved.'));
    }

    /* ---------- size bands ---------- */

    public function storeTier(Request $request)
    {
        $data = $this->tierRules($request);

        if ($request->boolean('is_default')) {
            DeliveryTier::query()->update(['is_default' => false]);
        }

        DeliveryTier::create($data);

        return back()->with('status', __('Size band added.'));
    }

    public function updateTier(Request $request, DeliveryTier $tier)
    {
        $data = $this->tierRules($request);

        if ($request->boolean('is_default')) {
            DeliveryTier::query()->update(['is_default' => false]);
        }

        $tier->update($data);

        return back()->with('status', __('Size band updated.'));
    }

    public function destroyTier(DeliveryTier $tier)
    {
        if ($tier->products()->exists()) {
            return back()->with('error', __('Products are still using this band. Move them to another one first.'));
        }

        if (DeliveryTier::count() < 2) {
            return back()->with('error', __('Keep at least one size band.'));
        }

        $tier->delete();

        return back()->with('status', __('Size band removed.'));
    }

    private function tierRules(Request $request): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:160'],
            'icon'        => ['nullable', 'string', 'max:8'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }

    /* ---------- zones ---------- */

    public function storeZone(Request $request)
    {
        ShippingZone::create($this->zoneRules($request));

        return back()->with('status', __('Delivery area added.'));
    }

    public function updateZone(Request $request, ShippingZone $zone)
    {
        $zone->update($this->zoneRules($request));

        return back()->with('status', __('Delivery area updated.'));
    }

    public function destroyZone(ShippingZone $zone)
    {
        // Orders point at the zone they were placed with; deleting it would
        // strip that history, so switch it off instead.
        if ($zone->id && DB::table('orders')->where('shipping_zone_id', $zone->id)->exists()) {
            return back()->with('error', __('Orders were placed with this area. Switch it off instead of deleting it.'));
        }

        $zone->delete();

        return back()->with('status', __('Delivery area removed.'));
    }

    private function zoneRules(Request $request): array
    {
        if (! $request->filled('sort_order')) {
            $request->merge(['sort_order' => 0]);
        }

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:80'],
            'delivery_time' => ['nullable', 'string', 'max:80'],
            'rate'          => ['required', 'numeric', 'min:0'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /* ---------- product assignment ---------- */

    public function assignProducts(Request $request)
    {
        $data = $request->validate([
            'tier'   => ['array'],
            'tier.*' => ['nullable', 'exists:delivery_tiers,id'],
        ]);

        foreach ($data['tier'] ?? [] as $productId => $tierId) {
            Product::whereKey($productId)->update(['delivery_tier_id' => $tierId ?: null]);
        }

        return back()->with('status', __('Product sizes saved.'));
    }
}
