<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
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
            ->paginate(15)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0.01|max:99999.99',
            'category_id' => 'required|exists:categories,id',
            'items_file' => 'required|file|mimes:csv,txt,json,xlsx,xls|max:10240',
        ]);

        $product = Product::create([
            'seller_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
            'status' => 'draft',
            'stock_count' => 0,
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

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0.01|max:99999.99',
            'category_id' => 'required|exists:categories,id',
            'items_file' => 'nullable|file|mimes:csv,txt,json,xlsx,xls|max:10240',
        ]);

        $product->update([
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
