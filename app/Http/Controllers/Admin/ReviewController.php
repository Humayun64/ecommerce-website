<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with('product')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $term) => $q->where(fn ($sub) => $sub
                ->where('reviewer_name', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Review::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.reviews.index', compact('reviews', 'counts'));
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'status'      => ['required', Rule::in(array_keys(Review::STATUSES))],
            'admin_reply' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->update($data);

        return back()->with('status', __('Review updated.'));
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('status', __('Review deleted.'));
    }
}
