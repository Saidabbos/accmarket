<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DisputeResolved;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = Dispute::with([
            'order:id,order_number,total_amount,buyer_id',
            'order.buyer:id,name,email',
            'user:id,name,email',
            'resolver:id,name',
        ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhereHas('order', function ($sq) use ($search) {
                          $sq->where('order_number', 'like', "%{$search}%");
                      })
                      ->orWhereHas('user', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Dispute::count(),
            'open' => Dispute::where('status', 'open')->count(),
            'in_progress' => Dispute::where('status', 'in_progress')->count(),
            'resolved' => Dispute::where('status', 'resolved')->count(),
        ];

        return Inertia::render('Admin/Disputes/Index', [
            'disputes' => $disputes,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'order.items.productItem.product:id,name,price',
            'order.buyer:id,name,email',
            'user:id,name,email',
            'resolver:id,name',
        ]);

        return Inertia::render('Admin/Disputes/Show', [
            'dispute' => $dispute,
        ]);
    }

    public function updateStatus(Request $request, Dispute $dispute)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $dispute->update(['status' => $request->status]);

        return back()->with('success', 'Dispute status updated.');
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $request->validate([
            'resolution' => 'required|string|max:5000',
            'action' => 'required|in:no_action,refund,partial_refund',
            'refund_amount' => 'required_if:action,partial_refund|nullable|numeric|min:0',
        ]);

        // Process refund if needed
        if ($request->action === 'refund') {
            $this->processRefund($dispute->order, $dispute->order->total_amount);
        } elseif ($request->action === 'partial_refund' && $request->refund_amount) {
            $this->processRefund($dispute->order, $request->refund_amount);
        }

        $dispute->resolve(auth()->user(), $request->resolution);

        // Send resolution email to user
        $dispute->load(['order', 'user']);
        if ($dispute->user) {
            Mail::to($dispute->user)->send(new DisputeResolved($dispute));
        }

        return redirect()->route('admin.disputes.index')
            ->with('success', 'Dispute resolved successfully.');
    }

    private function processRefund(Order $order, float $amount): void
    {
        // Mark order as refunded
        $order->update([
            'status' => 'refunded',
            'refund_amount' => $amount,
            'refunded_at' => now(),
        ]);

        // Release product items back to available status
        foreach ($order->items as $item) {
            if ($item->productItem) {
                $item->productItem->update(['status' => 'available']);

                // Update product stock count
                if ($item->productItem->product) {
                    $item->productItem->product->updateStockCount();
                }
            }
        }
    }
}
