<?php

namespace App\Actions\Seller;

use App\Actions\BaseAction;
use App\Models\Product;
use Exception;

class UpdateProductAction extends BaseAction
{
    protected function handle(array $data)
    {
        $product = Product::findOrFail($data['product_id']);

        // Verify product belongs to seller
        if ($product->seller_id !== $data['seller_id']) {
            throw new Exception('Unauthorized to update this product');
        }

        $product->update([
            'category_id' => $data['category_id'] ?? $product->category_id,
            'name' => $data['name'] ?? $product->name,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'status' => $data['status'] ?? $product->status,
        ]);

        return $product->fresh();
    }
}
