<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        private NowPaymentsService $nowPaymentsService
    ) {}

    public function show(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Load order with items
        $order->load(['items.productItem.product', 'user']);

        return Inertia::render('Shop/Payment', [
            'order' => $order,
        ]);
    }

    public function initiate(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Check if order is pending payment
        if ($order->payment_status !== 'pending') {
            return back()->with('error', 'This order cannot be paid.');
        }

        // Create payment with NowPayments
        $result = $this->nowPaymentsService->createPayment($order);

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Payment creation failed.');
        }

        // Redirect to NowPayments payment page
        return Inertia::location($result['payment_url']);
    }

    public function success(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.productItem.product']);

        return Inertia::render('Shop/PaymentSuccess', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Shop/PaymentCancel', [
            'order' => $order,
        ]);
    }

    public function ipn(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('x-nowpayments-sig', '');

        Log::info('IPN webhook received', [
            'payload' => $payload,
            'signature' => $signature,
        ]);

        // Verify signature
        if (!$this->nowPaymentsService->verifyIpnSignature($payload, $signature)) {
            Log::warning('IPN signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the IPN payload
        $handled = $this->nowPaymentsService->handleIpnPayload($payload);

        if (!$handled) {
            return response()->json(['error' => 'Failed to process payment'], 400);
        }

        return response()->json(['success' => true]);
    }

    public function orders()
    {
        $orders = Order::where('buyer_id', auth()->id())
            ->with(['items.productItem.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Shop/Orders', [
            'orders' => $orders,
        ]);
    }

    public function orderShow(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.productItem.product']);

        return Inertia::render('Shop/OrderShow', [
            'order' => $order,
        ]);
    }

    /**
     * Guest payment methods - authenticated via token
     */
    protected function validateGuestToken(Order $order, Request $request): bool
    {
        // Token can come from query string (GET) or request body (POST)
        $token = $request->input('token') ?? $request->query('token');
        return $order->isGuestOrder() && $order->guest_token === $token;
    }

    public function guestShow(Order $order, Request $request)
    {
        if (!$this->validateGuestToken($order, $request)) {
            abort(403, 'Invalid or expired token.');
        }

        $order->load(['items.productItem.product']);

        return Inertia::render('Shop/Payment', [
            'order' => $order,
            'isGuest' => true,
            'guestToken' => $order->guest_token,
        ]);
    }

    public function guestInitiate(Order $order, Request $request)
    {
        if (!$this->validateGuestToken($order, $request)) {
            abort(403, 'Invalid or expired token.');
        }

        if ($order->payment_status !== 'pending') {
            return back()->with('error', 'This order cannot be paid.');
        }

        $result = $this->nowPaymentsService->createPayment($order);

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Payment creation failed.');
        }

        return Inertia::location($result['payment_url']);
    }

    public function guestSuccess(Order $order, Request $request)
    {
        if (!$this->validateGuestToken($order, $request)) {
            abort(403, 'Invalid or expired token.');
        }

        $order->load(['items.productItem.product']);

        return Inertia::render('Shop/PaymentSuccess', [
            'order' => $order,
            'isGuest' => true,
            'guestToken' => $order->guest_token,
        ]);
    }

    public function guestCancel(Order $order, Request $request)
    {
        if (!$this->validateGuestToken($order, $request)) {
            abort(403, 'Invalid or expired token.');
        }

        return Inertia::render('Shop/PaymentCancel', [
            'order' => $order,
            'isGuest' => true,
        ]);
    }
}
