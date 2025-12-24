<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['seller', 'category'])
            ->withCount(['items', 'availableItems'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhereHas('seller', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($request->seller, function ($query, $seller) {
                $query->where('seller_id', $seller);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $sellers = User::role('seller')->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'sellers' => $sellers,
            'filters' => $request->only(['search', 'status', 'category', 'seller']),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['seller', 'category', 'items' => function ($query) {
            $query->latest()->limit(100);
        }]);

        $product->loadCount(['items', 'availableItems']);

        // Get order statistics
        $orderStats = [
            'total_orders' => $product->orders()->count(),
            'total_revenue' => $product->orders()->sum('total_amount'),
        ];

        return Inertia::render('Admin/Products/Show', [
            'product' => $product,
            'orderStats' => $orderStats,
        ]);
    }

    public function edit(Product $product)
    {
        $product->load(['seller', 'category']);

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,active,inactive',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Check if product has sold items
        $soldItemsCount = $product->items()->where('status', 'sold')->count();

        if ($soldItemsCount > 0) {
            return back()->with('error', 'Cannot delete product with sold items. Consider deactivating it instead.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:draft,active,inactive',
        ]);

        $product->update(['status' => $request->status]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);

        return back()->with('success', 'Product featured status updated.');
    }
}
