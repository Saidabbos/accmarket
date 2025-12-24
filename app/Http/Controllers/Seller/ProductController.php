<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreProductRequest;
use App\Http\Requests\Seller\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function index(Request $request)
    {
        $products = Product::forSeller(Auth::id())
            ->with(['category', 'items'])
            ->withCount(['items', 'availableItems'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(config('shop.pagination.seller_products_per_page'))
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get(['id', 'name', 'parent_id']);

        return Inertia::render('Seller/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category_id']),
        ]);
    }

    public function create()
    {
        $categories = Category::active()
            ->with('children:id,name,parent_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'parent_id']);

        return Inertia::render('Seller/Products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $validated = $request->validated();

            $action = new \App\Actions\Seller\CreateProductAction();
            $product = $action->execute([
                'seller_id' => Auth::id(),
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'price' => $validated['price'],
                'status' => \App\Enums\ProductStatus::DRAFT->value,
            ]);

            $result = $this->fileUploadService->processFile(
                $request->file('items_file'),
                $product
            );

            if (!$result['success']) {
                $product->delete();
                return back()->withErrors(['items_file' => $result['message']]);
            }

            $product->updateStockCount();

            return redirect()
                ->route('seller.products.index')
                ->with('success', "Product created with {$result['count']} items.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category', 'items' => function ($query) {
            $query->latest()->limit(100);
        }]);
        $product->loadCount(['items', 'availableItems']);

        return Inertia::render('Seller/Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::active()
            ->with('children:id,name,parent_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'parent_id']);

        $product->loadCount(['items', 'availableItems']);

        return Inertia::render('Seller/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $validated = $request->validated();

            $action = new \App\Actions\Seller\UpdateProductAction();
            $action->execute([
                'product_id' => $product->id,
                'seller_id' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'price' => $validated['price'],
                'category_id' => $validated['category_id'],
            ]);

            if ($request->hasFile('items_file')) {
                $result = $this->fileUploadService->processFile(
                    $request->file('items_file'),
                    $product
                );

                if (!$result['success']) {
                    return back()->withErrors(['items_file' => $result['message']]);
                }

                $product->updateStockCount();

                return redirect()
                    ->route('seller.products.index')
                    ->with('success', "Product updated. Added {$result['count']} new items.");
            }

            return redirect()
                ->route('seller.products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->items()->where('status', 'sold')->exists()) {
            return back()->withErrors(['product' => 'Cannot delete product with sold items.']);
        }

        $product->items()->delete();
        $product->delete();

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'status' => 'required|in:draft,active,inactive',
        ]);

        $product->update(['status' => $validated['status']]);

        return back()->with('success', 'Product status updated.');
    }

    public function addItems(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'items_file' => 'required|file|mimes:csv,txt,json,xlsx,xls|max:10240',
        ]);

        $result = $this->fileUploadService->processFile(
            $request->file('items_file'),
            $product
        );

        if (!$result['success']) {
            return back()->withErrors(['items_file' => $result['message']]);
        }

        $product->updateStockCount();

        return back()->with('success', "Added {$result['count']} new items.");
    }
}
