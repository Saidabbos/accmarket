<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\DisputeOpened;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = Dispute::with(['order:id,order_number,total_amount', 'resolver:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Shop/Disputes/Index', [
            'disputes' => $disputes,
        ]);
    }

    public function create(Order $order)
    {
        // Check if user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Check if dispute already exists
        $existingDispute = Dispute::where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['open', 'in_progress'])
            ->first();

        if ($existingDispute) {
            return redirect()->route('disputes.show', $existingDispute)
                ->with('info', 'A dispute for this order is already open.');
        }

        $order->load('items.productItem.product:id,name');

        return Inertia::render('Shop/Disputes/Create', [
            'order' => $order,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        // Check if user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
        ]);

        $dispute = Dispute::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => 'open',
        ]);

        // Send confirmation email
        $dispute->load('order');
        Mail::to(auth()->user())->send(new DisputeOpened($dispute));

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Dispute submitted successfully. We will review it shortly.');
    }

    public function show(Dispute $dispute)
    {
        // Check if user owns this dispute
        if ($dispute->user_id !== auth()->id()) {
            abort(403);
        }

        $dispute->load([
            'order.items.productItem.product:id,name',
            'resolver:id,name',
        ]);

        return Inertia::render('Shop/Disputes/Show', [
            'dispute' => $dispute,
        ]);
    }
}
