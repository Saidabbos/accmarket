<x-mail::message>
# Dispute Resolved

Your dispute for order **#{{ $dispute->order->order_number }}** has been resolved.

## Dispute Details

**Subject:** {{ $dispute->subject }}
**Order Number:** #{{ $dispute->order->order_number }}
**Status:** Resolved

## Resolution

{{ $dispute->resolution }}

@if($dispute->order->refund_amount)
## Refund Information

A refund of **${{ number_format($dispute->order->refund_amount, 2) }}** has been issued to your original payment method.

Please allow 5-10 business days for the refund to appear in your account.
@endif

<x-mail::button :url="$disputeUrl">
View Dispute Details
</x-mail::button>

If you have any questions about this resolution, please contact our support team.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
