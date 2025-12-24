<?php

namespace App\Actions\Seller;

use App\Actions\BaseAction;
use App\Models\Product;
use Illuminate\Support\Str;

class CreateProductAction extends BaseAction
{
    protected function handle(array $data)
    {
        return Product::create([
            'seller_id' => $data['seller_id'],
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . uniqid(),
            'description' => $data['description'],
            'price' => $data['price'],
            'status' => $data['status'] ?? 'active',
            'stock_count' => 0,
        ]);
    }
}
