<?php

namespace App\Actions\Seller;

use App\Actions\BaseAction;
use App\Models\Product;
use App\Models\ProductItem;
use Exception;

class AddProductItemsAction extends BaseAction
{
    protected function handle(array $data)
    {
        $product = Product::findOrFail($data['product_id']);

        // Verify product belongs to seller
        if ($product->seller_id !== $data['seller_id']) {
            throw new Exception('Unauthorized to add items to this product');
        }

        $items = $data['items'] ?? [];
        $createdItems = [];

        foreach ($items as $itemContent) {
            $createdItems[] = ProductItem::create([
                'product_id' => $product->id,
                'content' => $itemContent,
                'status' => 'available',
            ]);
        }

        // Update product stock count
        $product->updateStockCount();

        return [
            'product' => $product->fresh(),
            'items_added' => count($createdItems),
            'items' => $createdItems,
        ];
    }
}
