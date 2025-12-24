<?php

namespace App\Actions\Shop;

use App\Actions\BaseAction;
use App\Models\Product;

class GetProductsAction extends BaseAction
{
    protected function handle(array $data)
    {
        $query = $this->buildBaseQuery();

        $this->applySearchFilter($query, $data);
        $this->applyCategoryFilter($query, $data);
        $this->applyPriceFilters($query, $data);
        $this->applySorting($query, $data);

        $perPage = $data['per_page'] ?? config('shop.pagination.products_per_page');

        return $query->having('available_items_count', '>', 0)
            ->paginate($perPage)
            ->withQueryString();
    }

    private function buildBaseQuery()
    {
        return Product::active()
            ->with(['category', 'seller:id,name'])
            ->withCount('availableItems')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');
    }

    private function applySearchFilter($query, array $data): void
    {
        if (!empty($data['search'])) {
            $query->where(function ($q) use ($data) {
                $q->where('name', 'like', "%{$data['search']}%")
                  ->orWhere('description', 'like', "%{$data['search']}%");
            });
        }
    }

    private function applyCategoryFilter($query, array $data): void
    {
        if (!empty($data['category'])) {
            $query->whereHas('category', function ($q) use ($data) {
                $q->where('slug', $data['category']);
            });
        }
    }

    private function applyPriceFilters($query, array $data): void
    {
        if (!empty($data['min_price'])) {
            $query->where('price', '>=', $data['min_price']);
        }

        if (!empty($data['max_price'])) {
            $query->where('price', '<=', $data['max_price']);
        }
    }

    private function applySorting($query, array $data): void
    {
        $sort = $data['sort'] ?? config('shop.sorting.default');
        $sortOptions = config('shop.sorting.options');

        if (isset($sortOptions[$sort])) {
            $sortConfig = $sortOptions[$sort];
            $query->orderBy($sortConfig['column'], $sortConfig['direction']);
        } else {
            $defaultSort = $sortOptions[config('shop.sorting.default')];
            $query->orderBy($defaultSort['column'], $defaultSort['direction']);
        }
    }
}
