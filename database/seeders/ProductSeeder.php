<?php

namespace Database\Seeders;

use App\Enums\ProductItemStatus;
use App\Enums\ProductStatus;
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
        $sellers = User::role('seller')->get();

        if ($sellers->isEmpty()) {
            echo "  ⚠ No sellers found. Skipping product seeding.\n";
            return;
        }

        $categories = Category::whereNotNull('parent_id')->get();
        $productCount = 0;
        $itemCount = 0;

        foreach ($categories as $category) {
            $productsPerCategory = rand(3, 6);

            for ($i = 0; $i < $productsPerCategory; $i++) {
                // Randomly select a seller
                $seller = $sellers->random();

                $name = $category->name . ' #' . ($i + 1);
                $price = fake()->randomFloat(2, 5, 99);

                $product = Product::create([
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . uniqid(),
                    'description' => $this->generateDescription($category->name),
                    'price' => $price,
                    'status' => ProductStatus::ACTIVE->value,
                    'stock_count' => 0,
                ]);

                $productCount++;

                $itemsPerProduct = rand(10, 30);
                for ($j = 0; $j < $itemsPerProduct; $j++) {
                    ProductItem::create([
                        'product_id' => $product->id,
                        'content' => $this->generateContent($category->name),
                        'status' => ProductItemStatus::AVAILABLE->value,
                    ]);
                    $itemCount++;
                }

                $product->updateStockCount();
            }
        }

        echo "  ✓ Created {$productCount} products with {$itemCount} items\n";
    }

    private function generateDescription(string $categoryName): string
    {
        $descriptions = [
            'Premium quality accounts with full access and warranty.',
            'Instant delivery! Get your account details immediately after purchase.',
            'Verified and tested accounts. 100% working guaranteed.',
            'Lifetime warranty included. Replace if any issues occur.',
            'Fresh accounts with no previous usage history.',
            'High-quality accounts from trusted sources.',
            'Fast delivery with 24/7 customer support available.',
            'Secure accounts with password change capability.',
        ];

        return fake()->randomElement($descriptions) . ' ' . fake()->sentence(rand(10, 15));
    }

    private function generateContent(string $categoryName): string
    {
        $categoryLower = strtolower($categoryName);

        // Steam/PlayStation/Xbox/Nintendo Accounts
        if (str_contains($categoryLower, 'steam') ||
            str_contains($categoryLower, 'playstation') ||
            str_contains($categoryLower, 'xbox') ||
            str_contains($categoryLower, 'nintendo')) {
            return fake()->email() . ':' . fake()->password(10, 16) . ' | Region: ' . fake()->randomElement(['US', 'EU', 'UK', 'Global']);
        }

        // Streaming Services (Netflix, Disney+, Spotify, YouTube Premium)
        if (str_contains($categoryLower, 'netflix') ||
            str_contains($categoryLower, 'disney') ||
            str_contains($categoryLower, 'spotify') ||
            str_contains($categoryLower, 'youtube')) {
            return fake()->email() . ':' . fake()->password(8, 12) . ' | Plan: Premium | Expires: ' . now()->addMonths(rand(1, 12))->format('Y-m-d');
        }

        // Social Media (Instagram, Facebook, Twitter, TikTok)
        if (str_contains($categoryLower, 'instagram') ||
            str_contains($categoryLower, 'facebook') ||
            str_contains($categoryLower, 'twitter') ||
            str_contains($categoryLower, 'tiktok')) {
            return fake()->userName() . ':' . fake()->password(8, 16) . ' | Followers: ' . rand(1000, 50000);
        }

        // Software Licenses (Windows, Office, Adobe, Antivirus)
        if (str_contains($categoryLower, 'windows') ||
            str_contains($categoryLower, 'office') ||
            str_contains($categoryLower, 'adobe') ||
            str_contains($categoryLower, 'antivirus')) {
            return 'LICENSE: ' . strtoupper(Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5));
        }

        // Gift Cards
        if (str_contains($categoryLower, 'gift card')) {
            $code = strtoupper(Str::random(16));
            $pin = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            return "CODE: {$code} | PIN: {$pin} | Value: $" . fake()->randomElement([10, 25, 50, 100]);
        }

        // Default: Email:Password format
        return fake()->email() . ':' . fake()->password(8, 16);
    }
}
