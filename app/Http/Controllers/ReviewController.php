<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
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
            ->paginate(config('shop.pagination.reviews_per_page'));

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
        if ($order->status !== \App\Enums\OrderStatus::COMPLETED->value) {
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

    public function store(StoreReviewRequest $request, Order $order, Product $product)
    {
        try {
            $action = new \App\Actions\Review\CreateReviewAction();
            $action->execute([
                'order' => $order,
                'product' => $product,
                'user' => auth()->user(),
                ...$request->validated(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Review submitted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
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

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $review->update($request->validated());

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
