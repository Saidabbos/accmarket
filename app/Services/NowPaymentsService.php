<?php

namespace App\Services;

use App\Mail\OrderCompleted;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NowPaymentsService
{
    private string $apiKey;
    private string $ipnSecret;
    private string $baseUrl;
    private bool $sandbox;

    public function __construct()
    {
        $this->apiKey = config('services.nowpayments.api_key', '');
        $this->ipnSecret = config('services.nowpayments.ipn_secret', '');
        $this->sandbox = config('services.nowpayments.sandbox', true);
        $this->baseUrl = $this->sandbox
            ? 'https://api-sandbox.nowpayments.io/v1'
            : 'https://api.nowpayments.io/v1';
    }

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
            'is_fee_paid_by_user' => true, // Buyer pays the fee
        ]);

        if ($response->failed()) {
            Log::error('NowPayments API error', [
                'order_id' => $order->id,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Payment creation failed.',
            ];
        }

        $data = $response->json();

        $order->update([
            'nowpayments_id' => $data['id'] ?? null,
            'payment_url' => $data['invoice_url'] ?? null,
            'payment_status' => 'processing',
        ]);

        return [
            'success' => true,
            'payment_url' => $data['invoice_url'] ?? null,
            'payment_id' => $data['id'] ?? null,
        ];
    }

    public function getPaymentStatus(string $paymentId): ?array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->get("{$this->baseUrl}/payment/{$paymentId}");

        if ($response->failed()) {
            Log::error('NowPayments status check failed', [
                'payment_id' => $paymentId,
                'response' => $response->json(),
            ]);
            return null;
        }

        return $response->json();
    }

    public function verifyIpnSignature(array $payload, string $signature): bool
    {
        if (empty($this->ipnSecret)) {
            Log::warning('IPN secret not configured');
            return false;
        }

        ksort($payload);
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $expectedSignature = hash_hmac('sha512', $jsonPayload, $this->ipnSecret);

        return hash_equals($expectedSignature, $signature);
    }

    public function handleIpnPayload(array $payload): bool
    {
        $orderNumber = $payload['order_id'] ?? null;
        $paymentStatus = $payload['payment_status'] ?? null;
        $paymentId = $payload['payment_id'] ?? null;
        $payCurrency = $payload['pay_currency'] ?? 'crypto';

        if (!$orderNumber) {
            Log::error('IPN: Missing order_id', $payload);
            return false;
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            Log::error('IPN: Order not found', ['order_number' => $orderNumber]);
            return false;
        }

        Log::info('IPN received', [
            'order_id' => $order->id,
            'payment_status' => $paymentStatus,
            'payment_id' => $paymentId,
        ]);

        // Don't process if payment is already completed (payment_status = 'completed' after markAsPaid)
        if ($order->payment_status === 'completed') {
            Log::info('IPN: Ignoring webhook for already completed payment', [
                'order_id' => $order->id,
                'current_status' => $order->payment_status,
                'webhook_status' => $paymentStatus,
            ]);
            return true;
        }

        switch ($paymentStatus) {
            case 'finished':
            case 'confirmed':
                $this->completePayment($order, $paymentId, $payCurrency);
                break;

            case 'partially_paid':
                // Check if the difference is just the fee (allow up to 2% difference)
                $actuallyPaid = (float) ($payload['actually_paid'] ?? 0);
                $payAmount = (float) ($payload['pay_amount'] ?? 0);

                if ($payAmount > 0 && $actuallyPaid > 0) {
                    $paidPercentage = ($actuallyPaid / $payAmount) * 100;

                    // If paid >= 98% (fee is typically 0.5-1%), consider it as paid
                    if ($paidPercentage >= 98) {
                        Log::info('IPN: Accepting partially_paid as complete (fee difference)', [
                            'order_id' => $order->id,
                            'pay_amount' => $payAmount,
                            'actually_paid' => $actuallyPaid,
                            'paid_percentage' => $paidPercentage,
                        ]);
                        $this->completePayment($order, $paymentId, $payCurrency);
                        break;
                    }
                }

                $order->update(['payment_status' => 'partially_paid']);
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

    private function completePayment(Order $order, ?string $paymentId, string $paymentMethod): void
    {
        $order->markAsPaid($paymentId ?? '', $paymentMethod);

        // Mark all reserved items as sold
        $order->items()->each(function ($item) {
            $item->markAsSold();
        });

        // Auto-complete order for digital goods
        $order->markAsCompleted();

        // Send confirmation email
        $order->load(['items.productItem.product', 'buyer']);
        if ($order->buyer) {
            Mail::to($order->buyer)->send(new OrderConfirmation($order));
            Mail::to($order->buyer)->send(new OrderCompleted($order));
        }

        Log::info('Payment completed', ['order_id' => $order->id]);
    }

    private function failPayment(Order $order, string $status): void
    {
        $order->update([
            'payment_status' => 'failed',
        ]);

        // Release reserved items
        $order->cancel();

        Log::info('Payment failed', ['order_id' => $order->id, 'status' => $status]);
    }

    public function getAvailableCurrencies(): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->get("{$this->baseUrl}/currencies");

        if ($response->failed()) {
            return [];
        }

        return $response->json('currencies') ?? [];
    }

    public function getMinimumPaymentAmount(string $currency = 'btc'): ?float
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->get("{$this->baseUrl}/min-amount", [
            'currency_from' => 'usd',
            'currency_to' => $currency,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('min_amount');
    }
}
