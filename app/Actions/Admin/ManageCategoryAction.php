<?php

namespace App\Actions\Admin;

use App\Actions\BaseAction;
use App\Models\Category;
use Illuminate\Support\Str;

class ManageCategoryAction extends BaseAction
{
    protected function handle(array $data)
    {
        $action = $data['action'] ?? 'create';

        return match ($action) {
            'create' => $this->createCategory($data),
            'update' => $this->updateCategory($data),
            'delete' => $this->deleteCategory($data),
            'toggle_status' => $this->toggleStatus($data),
            default => throw new \Exception('Invalid action'),
        };
    }

    private function createCategory(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    private function updateCategory(array $data): Category
    {
        $category = Category::findOrFail($data['category_id']);

        $category->update([
            'name' => $data['name'] ?? $category->name,
            'slug' => $data['slug'] ?? $category->slug,
            'description' => $data['description'] ?? $category->description,
            'parent_id' => $data['parent_id'] ?? $category->parent_id,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return $category->fresh();
    }

    private function deleteCategory(array $data): bool
    {
        $category = Category::findOrFail($data['category_id']);

        // Check if category has products
        if ($category->products()->exists()) {
            throw new \Exception('Cannot delete category with existing products');
        }

        // Check if category has children
        if ($category->children()->exists()) {
            throw new \Exception('Cannot delete category with subcategories');
        }

        return $category->delete();
    }

    private function toggleStatus(array $data): Category
    {
        $category = Category::findOrFail($data['category_id']);

        $category->update(['is_active' => !$category->is_active]);

        return $category->fresh();
    }
}
