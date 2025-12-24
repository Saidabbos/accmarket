<?php

namespace App\Actions\Admin;

use App\Actions\BaseAction;
use App\Models\Order;
use Exception;

class UpdateOrderStatusAction extends BaseAction
{
    protected function handle(array $data)
    {
        $order = Order::findOrFail($data['order_id']);

        $validStatuses = ['pending', 'processing', 'completed', 'cancelled', 'refunded'];

        if (!in_array($data['status'], $validStatuses)) {
            throw new Exception('Invalid order status');
        }

        // Prevent changing status if order is already completed or refunded
        if (in_array($order->status, ['completed', 'refunded']) && $order->status !== $data['status']) {
            throw new Exception('Cannot change status of completed or refunded orders');
        }

        $order->update(['status' => $data['status']]);

        return $order->fresh();
    }
}
