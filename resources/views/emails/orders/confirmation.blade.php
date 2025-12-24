<x-mail::message>
# Order Confirmation

Thank you for your order! Your order **#{{ $order->order_number }}** has been received and is being processed.

## Order Details

**Order Number:** #{{ $order->order_number }}
**Date:** {{ $order->created_at->format('F j, Y, g:i a') }}
**Total:** ${{ number_format($order->total_amount, 2) }}

<x-mail::table>
| Product | Quantity | Price |
|:--------|:--------:|------:|
@foreach($order->items as $item)
| {{ $item->productItem?->product?->name ?? 'Product' }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$orderUrl">
View Order
</x-mail::button>

If you have any questions about your order, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
