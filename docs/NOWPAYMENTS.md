# NowPayments Integration Documentation

## Overview

This digital marketplace uses [NowPayments](https://nowpayments.io/) for cryptocurrency payment processing. NowPayments allows customers to pay with various cryptocurrencies including Bitcoin, Ethereum, Litecoin, and many others.

## Table of Contents

1. [Configuration](#configuration)
2. [Architecture](#architecture)
3. [Payment Flow](#payment-flow)
4. [API Integration](#api-integration)
5. [IPN Webhooks](#ipn-webhooks)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)

---

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# NowPayments Configuration
NOWPAYMENTS_API_KEY=your_api_key_here
NOWPAYMENTS_IPN_SECRET=your_ipn_secret_here
NOWPAYMENTS_SANDBOX=true
```

| Variable | Description |
|----------|-------------|
| `NOWPAYMENTS_API_KEY` | Your NowPayments API key from the dashboard |
| `NOWPAYMENTS_IPN_SECRET` | Secret key for verifying IPN webhook signatures |
| `NOWPAYMENTS_SANDBOX` | Set to `true` for testing, `false` for production |

### Config File

The configuration is loaded in `config/services.php`:

```php
'nowpayments' => [
    'api_key' => env('NOWPAYMENTS_API_KEY'),
    'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),
    'sandbox' => env('NOWPAYMENTS_SANDBOX', true),
],
```

### Getting API Keys

1. Create an account at [NowPayments](https://nowpayments.io/)
2. Navigate to **Store Settings** → **API Keys**
3. Generate a new API key
4. For IPN Secret: Go to **Store Settings** → **IPN** → Generate IPN Secret
5. For sandbox testing: Use [NowPayments Sandbox](https://sandbox.nowpayments.io/)

---

## Architecture

### Files Structure

```
app/
├── Services/
│   └── NowPaymentsService.php      # Main service class for API interactions
├── Http/Controllers/Shop/
│   └── PaymentController.php        # Handles payment routes and callbacks
├── Mail/
│   ├── OrderConfirmation.php        # Email sent on order creation
│   └── OrderCompleted.php           # Email sent on payment success
└── Models/
    └── Order.php                    # Order model with payment fields

routes/
└── web.php                          # Payment routes including IPN webhook
```

### Database Schema

The `orders` table includes these payment-related fields:

```php
Schema::table('orders', function (Blueprint $table) {
    $table->string('payment_status')->default('pending');
    $table->string('payment_method')->nullable();
    $table->string('payment_id')->nullable();
    $table->string('nowpayments_id')->nullable();
    $table->string('payment_url')->nullable();
    $table->timestamp('paid_at')->nullable();
});
```

---

## Payment Flow

### 1. Checkout Process

```
User Cart → Checkout → Order Created (status: pending) → Payment Page
```

When a user proceeds to checkout:
1. Cart items are validated for availability
2. Product items are reserved (status: `reserved`)
3. Order is created with `payment_status: pending`
4. User is redirected to the payment page

### 2. Payment Initiation

```
Payment Page → NowPayments Invoice → Crypto Payment Page
```

When user clicks "Pay with Crypto":
1. `PaymentController@initiate` is called
2. `NowPaymentsService@createPayment` creates an invoice via API
3. Order is updated with `nowpayments_id` and `payment_url`
4. User is redirected to NowPayments hosted payment page

### 3. Payment Completion

```
NowPayments → IPN Webhook → Order Completed → Email Sent
```

After payment:
1. NowPayments sends IPN webhook to `/payment/ipn`
2. Signature is verified using HMAC-SHA512
3. Order status is updated based on payment status
4. Product items are marked as `sold`
5. Confirmation emails are sent to the buyer

### Flow Diagram

```
┌─────────┐    ┌─────────┐    ┌─────────────┐    ┌─────────────┐
│  Cart   │───▶│Checkout │───▶│ Payment Page│───▶│ NowPayments │
└─────────┘    └─────────┘    └─────────────┘    └─────────────┘
                    │                                    │
                    ▼                                    │
              ┌─────────┐                               │
              │  Order  │◀──────────────────────────────┘
              │ Created │         IPN Webhook
              └─────────┘
                    │
                    ▼
              ┌─────────┐
              │  Email  │
              │  Sent   │
              └─────────┘
```

---

## API Integration

### NowPaymentsService Class

Located at `app/Services/NowPaymentsService.php`:

#### Creating a Payment

```php
public function createPayment(Order $order): array
{
    $response = Http::withHeaders([
        'x-api-key' => $this->apiKey,
        'Content-Type' => 'application/json',
    ])->post("{$this->baseUrl}/invoice", [
        'price_amount' => (float) $order->total_amount,
        'price_currency' => 'usd',
        'order_id' => $order->order_number,
        'order_description' => "Order #{$order->order_number}",
        'ipn_callback_url' => route('payment.ipn'),
        'success_url' => route('payment.success', $order),
        'cancel_url' => route('payment.cancel', $order),
    ]);

    // Returns: ['success' => bool, 'payment_url' => string, 'payment_id' => string]
}
```

#### Checking Payment Status

```php
public function getPaymentStatus(string $paymentId): ?array
{
    $response = Http::withHeaders([
        'x-api-key' => $this->apiKey,
    ])->get("{$this->baseUrl}/payment/{$paymentId}");

    return $response->json();
}
```

#### Getting Available Currencies

```php
public function getAvailableCurrencies(): array
{
    $response = Http::withHeaders([
        'x-api-key' => $this->apiKey,
    ])->get("{$this->baseUrl}/currencies");

    return $response->json('currencies') ?? [];
}
```

### API Endpoints Used

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/v1/invoice` | POST | Create a new payment invoice |
| `/v1/payment/{id}` | GET | Get payment status |
| `/v1/currencies` | GET | List available cryptocurrencies |
| `/v1/min-amount` | GET | Get minimum payment amount |

### API Base URLs

- **Sandbox**: `https://api-sandbox.nowpayments.io/v1`
- **Production**: `https://api.nowpayments.io/v1`

---

## IPN Webhooks

### Webhook Endpoint

The IPN (Instant Payment Notification) webhook is registered at:

```
POST /payment/ipn
```

This route is excluded from CSRF protection in `bootstrap/app.php`.

### Signature Verification

NowPayments signs each webhook with HMAC-SHA512:

```php
public function verifyIpnSignature(array $payload, string $signature): bool
{
    ksort($payload);  // Sort payload alphabetically
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $expectedSignature = hash_hmac('sha512', $jsonPayload, $this->ipnSecret);

    return hash_equals($expectedSignature, $signature);
}
```

The signature is sent in the `x-nowpayments-sig` header.

### Payment Status Values

| Status | Description | Action |
|--------|-------------|--------|
| `waiting` | Waiting for payment | No action |
| `confirming` | Payment detected, confirming | No action |
| `confirmed` | Payment confirmed | Complete order |
| `sending` | Sending to merchant | No action |
| `partially_paid` | Partial payment received | Update status |
| `finished` | Payment complete | Complete order |
| `failed` | Payment failed | Cancel order |
| `refunded` | Payment refunded | Cancel order |
| `expired` | Invoice expired | Cancel order |

### Webhook Handler

```php
public function handleIpnPayload(array $payload): bool
{
    $orderNumber = $payload['order_id'] ?? null;
    $paymentStatus = $payload['payment_status'] ?? null;

    $order = Order::where('order_number', $orderNumber)->first();

    switch ($paymentStatus) {
        case 'finished':
        case 'confirmed':
            $this->completePayment($order, $paymentId, $payCurrency);
            break;
        case 'failed':
        case 'expired':
        case 'refunded':
            $this->failPayment($order, $paymentStatus);
            break;
        default:
            $order->update(['payment_status' => 'processing']);
    }

    return true;
}
```

---

## Testing

### Sandbox Environment

1. Set `NOWPAYMENTS_SANDBOX=true` in `.env`
2. Use sandbox API keys from [sandbox.nowpayments.io](https://sandbox.nowpayments.io/)
3. Sandbox payments don't require real cryptocurrency

### Testing IPN Webhooks Locally

Use ngrok or similar to expose your local server:

```bash
# Install ngrok
brew install ngrok

# Expose local server
ngrok http 8088

# Update your NowPayments IPN URL to the ngrok URL
# Example: https://abc123.ngrok.io/payment/ipn
```

### Manual IPN Testing

You can simulate an IPN webhook using curl:

```bash
curl -X POST http://localhost:8088/payment/ipn \
  -H "Content-Type: application/json" \
  -H "x-nowpayments-sig: YOUR_SIGNATURE" \
  -d '{
    "payment_id": 123456,
    "payment_status": "finished",
    "order_id": "ORD-ABC123",
    "pay_currency": "btc",
    "price_amount": 100.00,
    "price_currency": "usd"
  }'
```

### Testing Checklist

- [ ] Payment page loads correctly
- [ ] "Pay with Crypto" redirects to NowPayments
- [ ] Cancel URL works correctly
- [ ] Success URL works correctly
- [ ] IPN webhook receives notifications
- [ ] Order status updates on payment success
- [ ] Order status updates on payment failure
- [ ] Emails are sent on successful payment
- [ ] Product items are marked as sold

---

## Troubleshooting

### Common Issues

#### 1. "Payment creation failed" Error

**Cause**: Invalid API key or network issue

**Solution**:
- Verify `NOWPAYMENTS_API_KEY` is correct
- Check if using sandbox key with sandbox URL
- Check NowPayments API status

```php
// Debug in NowPaymentsService
Log::error('NowPayments API error', [
    'response' => $response->json(),
    'status' => $response->status(),
]);
```

#### 2. IPN Webhook Not Received

**Cause**: URL not accessible or CSRF blocking

**Solution**:
- Ensure `/payment/ipn` is excluded from CSRF protection
- Verify IPN URL is publicly accessible
- Check NowPayments dashboard for webhook logs

#### 3. Signature Verification Failed

**Cause**: IPN secret mismatch or payload modification

**Solution**:
- Verify `NOWPAYMENTS_IPN_SECRET` matches dashboard
- Ensure payload is not modified before verification
- Check JSON encoding matches (no escaped slashes)

```php
// Debug signature
Log::info('IPN Debug', [
    'received_sig' => $signature,
    'expected_sig' => $expectedSignature,
    'payload' => $jsonPayload,
]);
```

#### 4. Order Not Found in IPN

**Cause**: Order number mismatch

**Solution**:
- Verify `order_id` in IPN matches `order_number` in database
- Check for encoding issues in order number

### Logging

All payment operations are logged. Check logs at:

```bash
# View Laravel logs
docker compose exec php tail -f storage/logs/laravel.log

# Filter for payment logs
docker compose exec php grep -i "payment\|nowpayments\|ipn" storage/logs/laravel.log
```

### Support

- NowPayments Documentation: https://documenter.getpostman.com/view/7907941/S1a32n38
- NowPayments Support: support@nowpayments.io
- Sandbox Dashboard: https://sandbox.nowpayments.io/

---

## Security Considerations

1. **API Key Protection**: Never expose API keys in frontend code
2. **IPN Verification**: Always verify webhook signatures
3. **HTTPS**: Use HTTPS in production for all payment URLs
4. **Rate Limiting**: Implement rate limiting on IPN endpoint
5. **Logging**: Log all payment operations for audit trail
6. **Amount Verification**: Verify payment amount matches order total

---

## Code Examples

### Initiating a Payment

```php
// In PaymentController
public function initiate(Order $order)
{
    // Verify ownership
    if ($order->buyer_id !== auth()->id()) {
        abort(403);
    }

    // Check order status
    if ($order->payment_status !== 'pending') {
        return back()->with('error', 'This order cannot be paid.');
    }

    // Create payment
    $result = $this->nowPaymentsService->createPayment($order);

    if (!$result['success']) {
        return back()->with('error', $result['message']);
    }

    // Redirect to payment page
    return Inertia::location($result['payment_url']);
}
```

### Handling Payment Success

```php
private function completePayment(Order $order, ?string $paymentId, string $paymentMethod): void
{
    // Mark order as paid
    $order->markAsPaid($paymentId ?? '', $paymentMethod);

    // Mark items as sold
    $order->items()->each(function ($item) {
        $item->markAsSold();
    });

    // Complete order (for digital goods)
    $order->markAsCompleted();

    // Send emails
    $order->load(['items.productItem.product', 'buyer']);
    if ($order->buyer) {
        Mail::to($order->buyer)->send(new OrderConfirmation($order));
        Mail::to($order->buyer)->send(new OrderCompleted($order));
    }
}
```

---

## Changelog

| Date | Version | Changes |
|------|---------|---------|
| Dec 24, 2025 | 1.0.0 | Initial implementation |

---

**Last Updated**: December 24, 2025
