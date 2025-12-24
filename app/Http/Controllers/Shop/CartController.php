<?php

namespace App\Http\Controllers\Shop;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\AddToCartRequest;
use App\Http\Requests\Shop\UpdateCartRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = session(config('shop.cart.session_key'), []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::with('category')
                ->withCount('availableItems')
                ->find($productId);

            if ($product && $product->status === ProductStatus::ACTIVE->value) {
                $availableQty = min($quantity, $product->available_items_count);
                if ($availableQty > 0) {
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $availableQty,
                        'subtotal' => $product->price * $availableQty,
                    ];
                    $total += $product->price * $availableQty;
                }
            }
        }

        return Inertia::render('Shop/Cart', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function add(AddToCartRequest $request)
    {
        try {
            $action = new \App\Actions\Shop\AddToCartAction();
            $result = $action->execute($request->validated());

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withErrors(['product' => $e->getMessage()]);
        }
    }

    public function update(UpdateCartRequest $request)
    {
        $validated = $request->validated();
        $sessionKey = config('shop.cart.session_key');

        $cart = session($sessionKey, []);

        if ($validated['quantity'] === 0) {
            unset($cart[$validated['product_id']]);
        } else {
            $product = Product::withCount('availableItems')->find($validated['product_id']);
            if ($product) {
                $cart[$validated['product_id']] = min($validated['quantity'], $product->available_items_count);
            }
        }

        session([$sessionKey => $cart]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $sessionKey = config('shop.cart.session_key');
        $cart = session($sessionKey, []);
        unset($cart[$validated['product_id']]);
        session([$sessionKey => $cart]);

        return back()->with('success', 'Product removed from cart.');
    }

    public function clear()
    {
        session()->forget(config('shop.cart.session_key'));

        return back()->with('success', 'Cart cleared.');
    }

    public function count()
    {
        $cart = session(config('shop.cart.session_key'), []);
        return response()->json(['count' => array_sum($cart)]);
    }
}
