<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function exportOrders(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,processing,completed,failed,cancelled',
        ]);

        $query = Order::with(['buyer:id,name,email', 'items'])
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc');

        $orders = $query->get();

        $csv = $this->generateCsv($orders->map(fn ($order) => [
            'Order Number' => $order->order_number,
            'Date' => $order->created_at->format('Y-m-d H:i:s'),
            'Customer Name' => $order->buyer?->name ?? 'Guest',
            'Customer Email' => $order->buyer?->email ?? '-',
            'Items Count' => $order->items->count(),
            'Total Amount' => number_format($order->total_amount, 2),
            'Payment Status' => $order->payment_status,
            'Order Status' => $order->status,
            'Payment Method' => $order->payment_method ?? '-',
        ])->toArray());

        $filename = 'orders_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportProducts(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:draft,active,inactive',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $query = Product::with(['seller:id,name,email', 'category:id,name'])
            ->withCount(['items', 'availableItems'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->category_id, fn ($q, $cat) => $q->where('category_id', $cat))
            ->orderBy('created_at', 'desc');

        $products = $query->get();

        $csv = $this->generateCsv($products->map(fn ($product) => [
            'ID' => $product->id,
            'Name' => $product->name,
            'Slug' => $product->slug,
            'Category' => $product->category?->name ?? 'Uncategorized',
            'Seller Name' => $product->seller?->name ?? '-',
            'Seller Email' => $product->seller?->email ?? '-',
            'Price' => number_format($product->price, 2),
            'Total Items' => $product->items_count,
            'Available Items' => $product->available_items_count,
            'Status' => $product->status,
            'Created At' => $product->created_at->format('Y-m-d H:i:s'),
        ])->toArray());

        $filename = 'products_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportUsers(Request $request)
    {
        $request->validate([
            'role' => 'nullable|in:admin,seller,buyer',
        ]);

        $query = User::with('roles')
            ->when($request->role, fn ($q, $role) => $q->role($role))
            ->orderBy('created_at', 'desc');

        $users = $query->get();

        $csv = $this->generateCsv($users->map(fn ($user) => [
            'ID' => $user->id,
            'Name' => $user->name,
            'Email' => $user->email,
            'Roles' => $user->roles->pluck('name')->join(', '),
            'Status' => $user->banned_at ? 'Banned' : 'Active',
            'Registered At' => $user->created_at->format('Y-m-d H:i:s'),
            'Email Verified' => $user->email_verified_at ? 'Yes' : 'No',
        ])->toArray());

        $filename = 'users_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportRevenue(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $orders = Order::where(function ($q) {
                $q->where('payment_status', 'paid')->orWhere('status', 'completed');
            })
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $csv = $this->generateCsv($orders->map(fn ($row) => [
            'Date' => $row->date,
            'Orders' => $row->orders_count,
            'Revenue' => number_format($row->revenue, 2),
        ])->toArray());

        $filename = 'revenue_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function generateCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Write headers
        fputcsv($output, array_keys($data[0]));

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
