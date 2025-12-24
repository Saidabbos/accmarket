<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SellerController extends Controller
{
    public function show(User $seller)
    {
        // Ensure the user is a seller
        if (!$seller->hasRole('seller') && !$seller->hasRole('admin')) {
            abort(404);
        }

        // Get seller statistics
        $stats = [
            'total_products' => Product::where('seller_id', $seller->id)->count(),
            'active_products' => Product::where('seller_id', $seller->id)->where('status', 'active')->count(),
            'total_sales' => Order::whereHas('items.productItem.product', function ($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })->where('status', 'completed')->count(),
            'member_since' => $seller->created_at->format('F Y'),
        ];

        // Get active products
        $products = Product::where('seller_id', $seller->id)
            ->where('status', 'active')
            ->withCount('availableItems')
            ->with('category:id,name')
            ->latest()
            ->paginate(12);

        return Inertia::render('Shop/Seller/Show', [
            'seller' => [
                'id' => $seller->id,
                'name' => $seller->name,
                'created_at' => $seller->created_at,
            ],
            'stats' => $stats,
            'products' => $products,
        ]);
    }

    public function products(Request $request, User $seller)
    {
        // Ensure the user is a seller
        if (!$seller->hasRole('seller') && !$seller->hasRole('admin')) {
            abort(404);
        }

        $products = Product::where('seller_id', $seller->id)
            ->where('status', 'active')
            ->withCount('availableItems')
            ->with('category:id,name')
            ->when($request->category, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->sort, function ($query, $sort) {
                match ($sort) {
                    'price_low' => $query->orderBy('price', 'asc'),
                    'price_high' => $query->orderBy('price', 'desc'),
                    'newest' => $query->orderBy('created_at', 'desc'),
                    default => $query->orderBy('created_at', 'desc'),
                };
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Shop/Seller/Products', [
            'seller' => [
                'id' => $seller->id,
                'name' => $seller->name,
            ],
            'products' => $products,
            'filters' => $request->only(['category', 'sort']),
        ]);
    }
}
