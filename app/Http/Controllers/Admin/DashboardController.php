<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->getStats(),
            'revenueChart' => $this->getRevenueChart(),
            'topProducts' => $this->getTopProducts(),
            'recentOrders' => $this->getRecentOrders(),
            'userStats' => $this->getUserStats(),
        ]);
    }

    private function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Total revenue
        $totalRevenue = Order::where('payment_status', 'paid')
            ->orWhere('status', 'completed')
            ->sum('total_amount');

        // This month revenue
        $thisMonthRevenue = Order::where(function ($q) {
                $q->where('payment_status', 'paid')->orWhere('status', 'completed');
            })
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        // Last month revenue
        $lastMonthRevenue = Order::where(function ($q) {
                $q->where('payment_status', 'paid')->orWhere('status', 'completed');
            })
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total_amount');

        // Revenue growth percentage
        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100 : 0);

        // Total orders
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();

        // Today's orders
        $todayOrders = Order::whereDate('created_at', $today)->count();

        // Total users
        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        // Total products
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();

        return [
            'totalRevenue' => $totalRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'revenueGrowth' => $revenueGrowth,
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
            'pendingOrders' => $pendingOrders,
            'todayOrders' => $todayOrders,
            'totalUsers' => $totalUsers,
            'newUsersThisMonth' => $newUsersThisMonth,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
        ];
    }

    private function getRevenueChart(): array
    {
        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $days->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $revenue = Order::where(function ($q) {
                $q->where('payment_status', 'paid')->orWhere('status', 'completed');
            })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $orders = Order::where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('M d'))->toArray(),
            'revenue' => $days->map(fn ($d) => (float) ($revenue[$d] ?? 0))->toArray(),
            'orders' => $days->map(fn ($d) => (int) ($orders[$d] ?? 0))->toArray(),
        ];
    }

    private function getTopProducts(): array
    {
        return Product::select('products.*')
            ->selectRaw('COUNT(order_items.id) as sales_count')
            ->selectRaw('SUM(order_items.price) as total_revenue')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where(function ($q) {
                        $q->where('orders.payment_status', 'paid')
                          ->orWhere('orders.status', 'completed');
                    });
            })
            ->groupBy('products.id')
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'sales_count' => (int) $p->sales_count,
                'total_revenue' => (float) ($p->total_revenue ?? 0),
            ])
            ->toArray();
    }

    private function getRecentOrders(): array
    {
        return Order::with(['buyer:id,name,email', 'items'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_name' => $order->buyer?->name ?? 'Guest',
                'buyer_email' => $order->buyer?->email ?? '-',
                'total_amount' => $order->total_amount,
                'items_count' => $order->items->count(),
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('M d, Y H:i'),
            ])
            ->toArray();
    }

    private function getUserStats(): array
    {
        $roleStats = User::select('roles.name as role', DB::raw('COUNT(users.id) as count'))
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->pluck('count', 'role')
            ->toArray();

        // Get registrations over last 7 days
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $days->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $registrations = User::where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'byRole' => [
                'admin' => $roleStats['admin'] ?? 0,
                'seller' => $roleStats['seller'] ?? 0,
                'buyer' => $roleStats['buyer'] ?? 0,
                'unassigned' => $roleStats[''] ?? $roleStats[null] ?? 0,
            ],
            'registrationChart' => [
                'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('D'))->toArray(),
                'data' => $days->map(fn ($d) => (int) ($registrations[$d] ?? 0))->toArray(),
            ],
        ];
    }
}
