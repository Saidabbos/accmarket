<?php

namespace App\Actions\Admin;

use App\Actions\BaseAction;
use App\Models\Product;

class UpdateProductStatusAction extends BaseAction
{
    protected function handle(array $data)
    {
        $product = Product::findOrFail($data['product_id']);

        $newStatus = $product->status === 'active' ? 'inactive' : 'active';

        $product->update(['status' => $newStatus]);

        return $product->fresh();
    }
}
