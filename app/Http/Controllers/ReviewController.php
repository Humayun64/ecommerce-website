<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        if ($request->phone) {
            $digits = preg_replace('/\D/', '', $request->phone);

            if (str_starts_with($digits, '880')) {
                $digits = '0' . substr($digits, 3);
            }

            $request->merge(['phone' => $digits]);
        }

        $data = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:80'],
            'phone'         => ['nullable', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
            'rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'title'         => ['nullable', 'string', 'max:120'],
            'body'          => ['required', 'string', 'min:10', 'max:1500'],
        ], [
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, or leave it blank.'),
            'body.min'    => __('Tell us a little more — at least a sentence.'),
        ]);

        // The badge has to be earned: a delivered order, on this phone,
        // that actually contained this product.
        $order = $data['phone'] ? $this->deliveredOrderFor($data['phone'], $product) : null;

        $product->reviews()->create([
            'user_id'        => auth()->id(),
            'order_id'       => $order?->id,
            'reviewer_name'  => $data['reviewer_name'],
            'reviewer_phone' => $data['phone'] ?? null,
            'reviewer_email' => auth()->user()?->email,
            'rating'         => $data['rating'],
            'title'          => $data['title'] ?? null,
            'body'           => $data['body'],
            'is_verified'    => (bool) $order,
            'status'         => 'pending',
        ]);

        return back()->with('status', __('Thank you. Your review will appear once we have read it.'));
    }

    private function deliveredOrderFor(string $phone, Product $product): ?Order
    {
        return Order::where('customer_phone', $phone)
            ->where('status', 'delivered')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->latest()
            ->first();
    }
}
