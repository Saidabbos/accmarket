<?php

namespace App\Actions\Review;

use App\Actions\BaseAction;
use App\Models\Review;
use Exception;

class CreateReviewAction extends BaseAction
{
    protected function handle(array $data)
    {
        $order = $data['order'];
        $product = $data['product'];
        $user = $data['user'];

        // Validate order belongs to user
        if ($order->user_id !== $user->id) {
            throw new Exception('Unauthorized to review this order');
        }

        // Validate order is completed
        if ($order->status !== 'completed') {
            throw new Exception('Can only review completed orders');
        }

        // Check if product is in the order
        $orderItem = $order->items()->where('product_id', $product->id)->first();
        if (!$orderItem) {
            throw new Exception('Product not found in this order');
        }

        // Check if review already exists
        $existingReview = Review::where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            throw new Exception('You have already reviewed this product for this order');
        }

        // Create review
        return Review::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_verified_purchase' => true,
        ]);
    }
}
