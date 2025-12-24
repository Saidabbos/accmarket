<x-mail::message>
# Dispute Received

We have received your dispute for order **#{{ $dispute->order->order_number }}**.

## Dispute Details

**Subject:** {{ $dispute->subject }}

**Order Number:** #{{ $dispute->order->order_number }}
**Order Total:** ${{ number_format($dispute->order->total_amount, 2) }}
**Submitted:** {{ $dispute->created_at->format('F j, Y, g:i a') }}

## Your Message

{{ $dispute->description }}

---

Our team will review your dispute within 24-48 hours. You will receive an email notification once a resolution has been made.

<x-mail::button :url="$disputeUrl">
View Dispute Status
</x-mail::button>

If you need to provide additional information, please reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
