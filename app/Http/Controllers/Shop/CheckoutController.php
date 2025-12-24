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
            return redirect()->route('shop.cart')->withErrors(['cart' => 'Your cart is empty.']);
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
            return redirect()->route('shop.cart')->withErrors(['cart' => 'No available products in cart.']);
        }

        return Inertia::render('Shop/Checkout', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function process(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop.cart')->withErrors(['cart' => 'Your cart is empty.']);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($cart as $productId => $quantity) {
                $product = Product::withCount('availableItems')
                    ->lockForUpdate()
                    ->find($productId);

                if (!$product || $product->status !== 'active') {
                    throw new \Exception("Product {$productId} is no longer available.");
                }

                $availableQty = min($quantity, $product->available_items_count);
                if ($availableQty < 1) {
                    throw new \Exception("Product '{$product->name}' is out of stock.");
                }

                $items = ProductItem::where('product_id', $product->id)
                    ->where('status', 'available')
                    ->limit($availableQty)
                    ->lockForUpdate()
                    ->get();

                if ($items->count() < $availableQty) {
                    throw new \Exception("Not enough stock for '{$product->name}'.");
                }

                $orderItems[] = [
                    'product' => $product,
                    'items' => $items,
                    'quantity' => $availableQty,
                    'price' => $product->price,
                ];

                $totalAmount += $product->price * $availableQty;
            }

            // Create order
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            // Reserve items and create order items
            foreach ($orderItems as $orderItemData) {
                foreach ($orderItemData['items'] as $productItem) {
                    // Reserve the product item
                    $productItem->reserve($order);

                    // Create order item for each product item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_item_id' => $productItem->id,
                        'product_id' => $orderItemData['product']->id,
                        'quantity' => 1,
                        'price' => $orderItemData['price'],
                    ]);
                }

                // Update product stock count
                $orderItemData['product']->updateStockCount();
            }

            DB::commit();

            // Clear cart
            session()->forget('cart');

            // Redirect to payment page
            return redirect()->route('payment.show', $order)->with('success', 'Order created successfully. Please complete payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }
    }
}
