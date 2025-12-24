<?php

namespace App\Actions\Shop;

use App\Actions\BaseAction;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Exception;

class AddToCartAction extends BaseAction
{
    protected function handle(array $data)
    {
        $product = Product::findOrFail($data['product_id']);

        $this->validateProductAvailability($product);
        $this->validateStockAvailability($product, $data['quantity']);

        $cart = $this->getCart();
        $cart = $this->addProductToCart($cart, $product, $data['quantity']);

        Session::put(config('shop.cart.session_key'), $cart);

        return [
            'success' => true,
            'cart_count' => count($cart),
            'message' => 'Product added to cart successfully',
        ];
    }

    private function validateProductAvailability(Product $product): void
    {
        if ($product->status !== ProductStatus::ACTIVE->value) {
            throw new Exception('Product is not available for purchase');
        }
    }

    private function validateStockAvailability(Product $product, int $quantity): void
    {
        if ($product->stock_count < $quantity) {
            throw new Exception('Insufficient stock available');
        }
    }

    private function getCart(): array
    {
        return Session::get(config('shop.cart.session_key'), []);
    }

    private function addProductToCart(array $cart, Product $product, int $quantity): array
    {
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'seller_id' => $product->seller_id,
            ];
        }

        return $cart;
    }
}
