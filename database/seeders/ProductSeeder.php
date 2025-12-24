<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seller = User::where('email', 'seller@example.com')->first();

        if (!$seller) {
            return;
        }

        $categories = Category::whereNotNull('parent_id')->get();

        foreach ($categories as $category) {
            $productCount = rand(2, 5);

            for ($i = 0; $i < $productCount; $i++) {
                $name = $category->name . ' #' . ($i + 1);
                $price = fake()->randomFloat(2, 5, 50);

                $product = Product::create([
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . uniqid(),
                    'description' => fake()->paragraph(3),
                    'price' => $price,
                    'status' => 'active',
                    'stock_count' => 0,
                ]);

                $itemCount = rand(5, 20);
                for ($j = 0; $j < $itemCount; $j++) {
                    ProductItem::create([
                        'product_id' => $product->id,
                        'content' => $this->generateContent($category->name),
                        'status' => 'available',
                    ]);
                }

                $product->updateStockCount();
            }
        }
    }

    private function generateContent(string $categoryName): string
    {
        $categoryLower = strtolower($categoryName);

        if (str_contains($categoryLower, 'account')) {
            return fake()->email() . ':' . fake()->password(10, 16);
        }

        if (str_contains($categoryLower, 'key') || str_contains($categoryLower, 'license')) {
            return strtoupper(Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5));
        }

        if (str_contains($categoryLower, 'gift card')) {
            return 'CODE: ' . strtoupper(Str::random(16)) . ' | PIN: ' . rand(1000, 9999);
        }

        if (str_contains($categoryLower, 'vpn') || str_contains($categoryLower, 'subscription')) {
            return fake()->email() . ':' . fake()->password(8, 12) . ' | Expires: ' . now()->addYear()->format('Y-m-d');
        }

        return fake()->userName() . ':' . fake()->password(8, 16);
    }
}
