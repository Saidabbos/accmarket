<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

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

        if (empty($cartItems)) {
            session()->forget('cart');
            return redirect()->route('cart.index')->withErrors(['cart' => 'No available products in cart.']);
        }

        return Inertia::render('Shop/Checkout', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function process(Request $request)
    {
        try {
            $action = new \App\Actions\Shop\ProcessCheckoutAction();
            $order = $action->execute([
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('payment.show', $order)
                ->with('success', 'Order created successfully. Please complete payment.');
        } catch (\Exception $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }
    }
}
