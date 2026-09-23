<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::with(['category', 'brand'])
            ->withSum('usages as saved_total', 'discount_amount')
            ->when($request->search, fn ($q, $term) => $q->where('code', 'like', "%{$term}%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create', $this->formData(new Coupon([
            'type'      => 'percent',
            'is_active' => true,
        ])));
    }

    public function store(Request $request)
    {
        $coupon = Coupon::create($this->validated($request));

        return redirect()->route('admin.coupons.index')
            ->with('status', __('Coupon :code created.', ['code' => $coupon->code]));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', $this->formData($coupon));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('admin.coupons.index')
            ->with('status', __('Coupon :code updated.', ['code' => $coupon->code]));
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->used_count > 0) {
            return back()->with('error', __('This coupon has been used on real orders. Switch it off instead of deleting it, so those orders keep their history.'));
        }

        $coupon->delete();

        return back()->with('status', __('Coupon deleted.'));
    }

    /* ---------- helpers ---------- */

    private function formData(Coupon $coupon): array
    {
        return [
            'coupon'     => $coupon,
            'categories' => Category::with('parent')->orderBy('name')->get()
                                ->sortBy(fn ($c) => $c->full_name),
            'brands'     => Brand::orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        // Empty numbers and selects arrive as "" — MySQL rejects that in
        // numeric and foreign-key columns.
        foreach (['max_discount', 'min_spend', 'usage_limit', 'usage_limit_per_customer',
                  'category_id', 'brand_id', 'starts_at', 'expires_at'] as $key) {
            if (! $request->filled($key)) {
                $request->merge([$key => null]);
            }
        }

        $data = $request->validate([
            'code'                     => [
                'required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('coupons')->ignore($coupon),
            ],
            'description'              => ['nullable', 'string', 'max:200'],
            'type'                     => ['required', Rule::in(['percent', 'fixed'])],
            'value'                    => ['required', 'numeric', 'min:0'],
            'max_discount'             => ['nullable', 'numeric', 'min:0'],
            'min_spend'                => ['nullable', 'numeric', 'min:0'],
            'category_id'              => ['nullable', 'exists:categories,id'],
            'brand_id'                 => ['nullable', 'exists:brands,id'],
            'usage_limit'              => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at'                => ['nullable', 'date'],
            'expires_at'               => ['nullable', 'date', 'after:starts_at'],
        ], [
            'code.regex'        => __('Use letters, numbers, dashes and underscores only — no spaces.'),
            'expires_at.after'  => __('The end date has to be after the start date.'),
        ]);

        if ($data['type'] === 'percent' && $data['value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => __('A percentage coupon cannot be more than 100%.'),
            ]);
        }

        $data['free_shipping']    = $request->boolean('free_shipping');
        $data['first_order_only'] = $request->boolean('first_order_only');
        $data['is_active']        = $request->boolean('is_active');

        // A cap only means anything on a percentage coupon.
        if ($data['type'] === 'fixed') {
            $data['max_discount'] = null;
        }

        return $data;
    }
}
