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

    /**
     * Direct checkout - buy a product without adding to cart
     */
    public function directCheckout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('category')
            ->withCount('availableItems')
            ->findOrFail($request->product_id);

        // Check if product is active and has enough stock
        if ($product->status !== 'active') {
            return back()->withErrors(['product' => 'This product is not available.']);
        }

        if ($product->available_items_count < $request->quantity) {
            return back()->withErrors(['quantity' => 'Not enough items in stock.']);
        }

        // Store in session for direct checkout
        session(['direct_checkout' => [
            'product_id' => $product->id,
            'quantity' => $request->quantity,
        ]]);

        return redirect()->route('checkout.direct.show');
    }

    /**
     * Show direct checkout page
     */
    public function showDirectCheckout()
    {
        $directCheckout = session('direct_checkout');

        if (empty($directCheckout)) {
            return redirect()->route('shop.index');
        }

        $product = Product::with('category')
            ->withCount('availableItems')
            ->find($directCheckout['product_id']);

        if (!$product || $product->status !== 'active') {
            session()->forget('direct_checkout');
            return redirect()->route('shop.index')->withErrors(['product' => 'Product is not available.']);
        }

        $quantity = min($directCheckout['quantity'], $product->available_items_count);
        $total = $product->price * $quantity;

        return Inertia::render('Shop/DirectCheckout', [
            'product' => $product,
            'quantity' => $quantity,
            'total' => $total,
            'isGuest' => !Auth::check(),
        ]);
    }

    /**
     * Process direct checkout
     */
    public function processDirectCheckout(Request $request)
    {
        $directCheckout = session('direct_checkout');

        if (empty($directCheckout)) {
            return redirect()->route('shop.index');
        }

        // Validate guest email if not authenticated
        if (!Auth::check()) {
            $request->validate([
                'guest_email' => 'required|email|max:255',
            ]);
        }

        try {
            $product = Product::findOrFail($directCheckout['product_id']);
            $quantity = $directCheckout['quantity'];

            // Temporarily add to cart for processing
            session(['cart' => [
                $product->id => ['quantity' => $quantity],
            ]]);

            $action = new \App\Actions\Shop\ProcessCheckoutAction();
            $checkoutData = [
                'user_id' => Auth::id(),
            ];

            // Add guest email if not authenticated
            if (!Auth::check()) {
                $checkoutData['guest_email'] = $request->guest_email;
            }

            $order = $action->execute($checkoutData);

            // Clear direct checkout session
            session()->forget('direct_checkout');

            // For guest orders, redirect with token
            if ($order->isGuestOrder()) {
                return redirect()->route('payment.guest.show', [
                    'order' => $order->id,
                    'token' => $order->guest_token,
                ])->with('success', 'Order created successfully. Please complete payment.');
            }

            return redirect()->route('payment.show', $order)
                ->with('success', 'Order created successfully. Please complete payment.');
        } catch (\Exception $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }
    }
}
