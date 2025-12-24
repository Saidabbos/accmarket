<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::with('category')
                ->withCount('availableItems')
                ->find($productId);

            if ($product && $product->status === 'active') {
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

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $product = Product::withCount('availableItems')->find($validated['product_id']);

        if (!$product || $product->status !== 'active') {
            return back()->withErrors(['product' => 'Product is not available.']);
        }

        if ($product->available_items_count < 1) {
            return back()->withErrors(['product' => 'Product is out of stock.']);
        }

        $cart = session('cart', []);
        $currentQty = $cart[$product->id] ?? 0;
        $newQty = min($currentQty + $validated['quantity'], $product->available_items_count);

        $cart[$product->id] = $newQty;
        session(['cart' => $cart]);

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $cart = session('cart', []);

        if ($validated['quantity'] === 0) {
            unset($cart[$validated['product_id']]);
        } else {
            $product = Product::withCount('availableItems')->find($validated['product_id']);
            if ($product) {
                $cart[$validated['product_id']] = min($validated['quantity'], $product->available_items_count);
            }
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = session('cart', []);
        unset($cart[$validated['product_id']]);
        session(['cart' => $cart]);

        return back()->with('success', 'Product removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    public function count()
    {
        $cart = session('cart', []);
        return response()->json(['count' => array_sum($cart)]);
    }
}
