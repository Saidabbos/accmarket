<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(15)->withQueryString();

        // Transform users to include role names
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status ?? 'active',
                'roles' => $user->roles->pluck('name')->toArray(),
                'orders_count' => $user->orders()->count(),
                'products_count' => $user->products()->count(),
                'created_at' => $user->created_at->format('M d, Y'),
                'banned_at' => $user->banned_at?->format('M d, Y H:i'),
                'ban_reason' => $user->ban_reason,
            ];
        });

        $roles = Role::pluck('name')->toArray();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'status', 'sort', 'direction']),
        ]);
    }

    public function show(User $user)
    {
        $user->load('roles');

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status ?? 'active',
                'roles' => $user->roles->pluck('name')->toArray(),
                'orders_count' => $user->orders()->count(),
                'products_count' => $user->products()->count(),
                'created_at' => $user->created_at->format('M d, Y H:i'),
                'email_verified_at' => $user->email_verified_at?->format('M d, Y H:i'),
                'banned_at' => $user->banned_at?->format('M d, Y H:i'),
                'ban_reason' => $user->ban_reason,
            ],
            'roles' => Role::pluck('name')->toArray(),
            'recentOrders' => $user->orders()
                ->with('items')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->format('M d, Y'),
                ]),
            'recentProducts' => $user->products()
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'status' => $product->status,
                    'created_at' => $product->created_at->format('M d, Y'),
                ]),
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        // Prevent removing admin role from yourself
        if ($user->id === auth()->id() && !in_array('admin', $request->roles)) {
            return back()->withErrors(['roles' => 'You cannot remove your own admin role.']);
        }

        $user->syncRoles($request->roles);

        return back()->with('success', 'User roles updated successfully.');
    }

    public function ban(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // Prevent banning yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot ban yourself.']);
        }

        // Prevent banning other admins
        if ($user->hasRole('admin')) {
            return back()->withErrors(['user' => 'You cannot ban an admin user.']);
        }

        $user->update([
            'status' => 'banned',
            'ban_reason' => $request->reason,
            'banned_at' => now(),
        ]);

        return back()->with('success', 'User has been banned.');
    }

    public function unban(User $user)
    {
        $user->update([
            'status' => 'active',
            'ban_reason' => null,
            'banned_at' => null,
        ]);

        return back()->with('success', 'User has been unbanned.');
    }
}
