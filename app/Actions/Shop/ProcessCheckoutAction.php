<?php

namespace App\Actions\Shop;

use App\Actions\BaseAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductItemStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Exception;

class ProcessCheckoutAction extends BaseAction
{
    protected function handle(array $data)
    {
        $cart = $this->getCart();
        $this->validateCart($cart);

        $orderItems = $this->validateAndPrepareOrderItems($cart);
        $totalAmount = $this->calculateTotalAmount($orderItems);

        $order = $this->createOrder($data['user_id'], $totalAmount);
        $this->createOrderItems($order, $orderItems);
        $this->clearCart();

        return $order->load('items.product');
    }

    private function getCart(): array
    {
        return Session::get(config('shop.cart.session_key'), []);
    }

    private function validateCart(array $cart): void
    {
        if (empty($cart)) {
            throw new Exception('Cart is empty');
        }
    }

    private function validateAndPrepareOrderItems(array $cart): array
    {
        $orderItems = [];

        foreach ($cart as $item) {
            $product = Product::findOrFail($item['product_id']);

            $this->validateProductStatus($product);
            $availableItems = $this->getAvailableProductItems($product, $item['quantity']);
            $this->validateStockAvailability($product, $availableItems, $item['quantity']);

            $orderItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'product_items' => $availableItems,
            ];
        }

        return $orderItems;
    }

    private function validateProductStatus(Product $product): void
    {
        if ($product->status !== ProductStatus::ACTIVE->value) {
            throw new Exception("Product {$product->name} is no longer available");
        }
    }

    private function getAvailableProductItems(Product $product, int $quantity)
    {
        return ProductItem::where('product_id', $product->id)
            ->where('status', ProductItemStatus::AVAILABLE->value)
            ->limit($quantity)
            ->get();
    }

    private function validateStockAvailability(Product $product, $availableItems, int $requestedQuantity): void
    {
        if ($availableItems->count() < $requestedQuantity) {
            throw new Exception("Insufficient stock for {$product->name}");
        }
    }

    private function calculateTotalAmount(array $orderItems): float
    {
        $total = 0;

        foreach ($orderItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    private function createOrder(int $userId, float $totalAmount): Order
    {
        return Order::create([
            'buyer_id' => $userId,
            'order_number' => $this->generateOrderNumber(),
            'total_amount' => $totalAmount,
            'status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);
    }

    private function generateOrderNumber(): string
    {
        $prefix = config('shop.order.number_prefix');
        $randomLength = config('shop.order.number_random_length');

        return $prefix . strtoupper(Str::random($randomLength));
    }

    private function createOrderItems(Order $order, array $orderItems): void
    {
        foreach ($orderItems as $item) {
            // Create one OrderItem per ProductItem
            foreach ($item['product_items'] as $productItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_item_id' => $productItem->id,
                    'product_id' => $item['product']->id,
                    'seller_id' => $item['product']->seller_id,
                    'quantity' => 1,
                    'price' => $item['price'],
                    'total' => $item['price'],
                ]);

                // Mark the product item as sold
                $productItem->update([
                    'status' => ProductItemStatus::SOLD->value,
                ]);
            }

            $item['product']->updateStockCount();
        }
    }

    private function clearCart(): void
    {
        Session::forget(config('shop.cart.session_key'));
    }
}
