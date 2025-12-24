<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()
            ->with(['category', 'seller:id,name'])
            ->withCount('availableItems')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($query, $categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice);
            })
            ->when($request->sort, function ($query, $sort) {
                match ($sort) {
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    'newest' => $query->orderBy('created_at', 'desc'),
                    'name' => $query->orderBy('name', 'asc'),
                    default => $query->orderBy('created_at', 'desc'),
                };
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->having('available_items_count', '>', 0)
            ->paginate(12)
            ->withQueryString();

        $categories = Category::active()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'parent_id']);

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'min_price', 'max_price', 'sort']),
        ]);
    }

    public function show(Product $product)
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load([
            'category.parent',
            'seller:id,name',
            'reviews' => function ($query) {
                $query->with('user:id,name')->latest()->limit(10);
            }
        ]);
        $product->loadCount('availableItems');
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withCount('availableItems')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->having('available_items_count', '>', 0)
            ->limit(4)
            ->get();

        return Inertia::render('Shop/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
