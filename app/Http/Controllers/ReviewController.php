<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = auth()->user()
            ->reviews()
            ->with(['product', 'order'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function create(Order $order, Product $product)
    {
        // Check if order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'You can only review completed orders.');
        }

        // Check if already reviewed
        $existingReview = Review::where([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ])->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this product.');
        }

        return Inertia::render('Reviews/Create', [
            'order' => $order->load('product'),
            'product' => $product,
        ]);
    }

    public function store(Request $request, Order $order, Product $product)
    {
        // Check if order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'You can only review completed orders.');
        }

        // Check if already reviewed
        $existingReview = Review::where([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ])->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this product.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => true,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Review submitted successfully!');
    }

    public function edit(Review $review)
    {
        // Check if review belongs to user
        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('Reviews/Edit', [
            'review' => $review->load(['product', 'order']),
        ]);
    }

    public function update(Request $request, Review $review)
    {
        // Check if review belongs to user
        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('reviews.index')
            ->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        // Check if review belongs to user
        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully!');
    }
}
