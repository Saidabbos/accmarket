<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Generate a signed download link for a single order item.
     */
    public function generateDownloadLink(Request $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        // Check if user owns the order
        if ($order->buyer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            return response()->json(['error' => 'Order is not completed'], 400);
        }

        // Check if order item belongs to order
        if ($orderItem->order_id !== $order->id) {
            return response()->json(['error' => 'Invalid order item'], 400);
        }

        // Generate signed URL (valid for 5 minutes)
        $downloadUrl = URL::temporarySignedRoute(
            'download.file',
            now()->addMinutes(5),
            ['order' => $order->id, 'orderItem' => $orderItem->id]
        );

        return response()->json(['download_url' => $downloadUrl]);
    }

    /**
     * Generate a signed download link for all order items.
     */
    public function generateBulkDownloadLink(Request $request, Order $order): JsonResponse
    {
        // Check if user owns the order
        if ($order->buyer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            return response()->json(['error' => 'Order is not completed'], 400);
        }

        // Generate signed URL (valid for 5 minutes)
        $downloadUrl = URL::temporarySignedRoute(
            'download.all',
            now()->addMinutes(5),
            ['order' => $order->id]
        );

        return response()->json(['download_url' => $downloadUrl]);
    }

    /**
     * Download a single order item content as a text file.
     */
    public function downloadFile(Request $request, Order $order, OrderItem $orderItem): Response|StreamedResponse
    {
        // Verify signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired download link');
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            abort(400, 'Order is not completed');
        }

        // Check if order item belongs to order
        if ($orderItem->order_id !== $order->id) {
            abort(400, 'Invalid order item');
        }

        // Get the content
        $content = $orderItem->productItem?->content ?? '';
        $productName = $orderItem->productItem?->product?->name ?? 'item';
        $filename = str_replace(' ', '_', $productName) . '_' . $orderItem->id . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download all order items as a single text file.
     */
    public function downloadAll(Request $request, Order $order): Response|StreamedResponse
    {
        // Verify signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired download link');
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            abort(400, 'Order is not completed');
        }

        // Load order items with product info
        $order->load('items.productItem.product');

        // Build content
        $content = "Order: #{$order->order_number}\n";
        $content .= "Date: " . $order->created_at->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('=', 50) . "\n\n";

        foreach ($order->items as $index => $item) {
            $productName = $item->productItem?->product?->name ?? 'Digital Item';
            $itemContent = $item->productItem?->content ?? 'No content';

            $content .= "Item " . ($index + 1) . ": {$productName}\n";
            $content .= str_repeat('-', 30) . "\n";
            $content .= $itemContent . "\n\n";
        }

        $filename = 'order_' . $order->order_number . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
