<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function generateDownloadLink(Order $order, OrderItem $orderItem)
    {
        // Verify ownership
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Verify order is completed
        if ($order->status !== 'completed') {
            return back()->with('error', 'Order is not completed yet.');
        }

        // Verify order item belongs to this order
        if ($orderItem->order_id !== $order->id) {
            abort(404);
        }

        // Generate signed URL valid for 1 hour
        $signedUrl = URL::temporarySignedRoute(
            'download.file',
            now()->addHour(),
            ['order' => $order->id, 'orderItem' => $orderItem->id]
        );

        return response()->json([
            'download_url' => $signedUrl,
            'expires_at' => now()->addHour()->toISOString(),
        ]);
    }

    public function downloadFile(Request $request, Order $order, OrderItem $orderItem)
    {
        // Verify signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Download link has expired or is invalid.');
        }

        // Verify order item belongs to this order
        if ($orderItem->order_id !== $order->id) {
            abort(404);
        }

        // Verify order is completed
        if ($order->status !== 'completed') {
            abort(403, 'Order is not completed.');
        }

        // Load the product item with product info
        $orderItem->load('productItem.product');
        $productItem = $orderItem->productItem;

        if (!$productItem || !$productItem->content) {
            abort(404, 'Item content not found.');
        }

        // Track download
        $this->trackDownload($order, $orderItem);

        // Generate filename
        $productName = $productItem->product?->name ?? 'item';
        $filename = sanitize_filename($productName . '_' . $orderItem->id . '.txt');

        // Return content as downloadable file
        return new StreamedResponse(function () use ($productItem) {
            echo $productItem->content;
        }, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadAll(Request $request, Order $order)
    {
        // Verify signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Download link has expired or is invalid.');
        }

        // Verify order is completed
        if ($order->status !== 'completed') {
            abort(403, 'Order is not completed.');
        }

        // Load all order items with content
        $order->load('items.productItem.product');

        // Track download
        $this->trackBulkDownload($order);

        // Build content for all items
        $content = $this->buildBulkContent($order);

        // Generate filename
        $filename = 'order_' . $order->order_number . '_items.txt';

        return new StreamedResponse(function () use ($content) {
            echo $content;
        }, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function generateBulkDownloadLink(Order $order)
    {
        // Verify ownership
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Verify order is completed
        if ($order->status !== 'completed') {
            return back()->with('error', 'Order is not completed yet.');
        }

        // Generate signed URL valid for 1 hour
        $signedUrl = URL::temporarySignedRoute(
            'download.all',
            now()->addHour(),
            ['order' => $order->id]
        );

        return response()->json([
            'download_url' => $signedUrl,
            'expires_at' => now()->addHour()->toISOString(),
        ]);
    }

    private function buildBulkContent(Order $order): string
    {
        $lines = [];
        $lines[] = "Order: #{$order->order_number}";
        $lines[] = "Date: " . $order->created_at->format('Y-m-d H:i:s');
        $lines[] = str_repeat('=', 50);
        $lines[] = '';

        foreach ($order->items as $index => $item) {
            $productItem = $item->productItem;
            $product = $productItem?->product;

            $lines[] = "Item #" . ($index + 1);
            $lines[] = "Product: " . ($product?->name ?? 'Unknown');
            $lines[] = str_repeat('-', 30);
            $lines[] = $productItem?->content ?? 'Content not available';
            $lines[] = '';
            $lines[] = str_repeat('=', 50);
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function trackDownload(Order $order, OrderItem $orderItem): void
    {
        // Could be expanded to track downloads in database
        // For now, just log
        logger()->info('Download tracked', [
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $order->buyer_id,
        ]);
    }

    private function trackBulkDownload(Order $order): void
    {
        logger()->info('Bulk download tracked', [
            'order_id' => $order->id,
            'user_id' => $order->buyer_id,
            'items_count' => $order->items->count(),
        ]);
    }
}

/**
 * Sanitize filename for download
 */
function sanitize_filename(string $filename): string
{
    // Remove any characters that aren't alphanumeric, dash, underscore, or dot
    $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
    // Remove multiple consecutive underscores
    $filename = preg_replace('/_+/', '_', $filename);
    return $filename;
}
