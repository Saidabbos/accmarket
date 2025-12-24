<?php

namespace App\Http\Controllers\Shop;

use App\Actions\Shop\GetProductsAction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request, GetProductsAction $getProductsAction)
    {
        $products = $getProductsAction->execute([
            'search' => $request->search,
            'category' => $request->category,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'sort' => $request->sort,
            'per_page' => config('shop.pagination.products_per_page'),
        ]);

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
        if ($product->status !== \App\Enums\ProductStatus::ACTIVE->value) {
            abort(404);
        }

        $product->load([
            'category.parent',
            'seller:id,name',
            'reviews' => function ($query) {
                $query->with('user:id,name')
                    ->latest()
                    ->limit(config('shop.pagination.reviews_per_product'));
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
            ->limit(config('shop.pagination.related_products_limit'))
            ->get();

        return Inertia::render('Shop/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
