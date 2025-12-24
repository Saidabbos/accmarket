<x-mail::message>
# Your Order is Complete!

Great news! Your order **#{{ $order->order_number }}** has been completed and your digital products are now ready for download.

## Order Details

**Order Number:** #{{ $order->order_number }}
**Date:** {{ $order->created_at->format('F j, Y, g:i a') }}
**Total:** ${{ number_format($order->total_amount, 2) }}

## Your Items

<x-mail::table>
| Product | Price |
|:--------|------:|
@foreach($order->items as $item)
| {{ $item->productItem?->product?->name ?? 'Product' }} | ${{ number_format($item->price, 2) }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$orderUrl">
Download Your Items
</x-mail::button>

Your download links are available in your order details page. Links are valid for a limited time.

Thanks for your purchase!<br>
{{ config('app.name') }}
</x-mail::message>
