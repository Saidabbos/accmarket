<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Verification ===\n\n";

// Check Users
echo "USERS:\n";
echo str_repeat('-', 50) . "\n";
$users = App\Models\User::with('roles')->get();
foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->implode(', ');
    echo "• {$user->email}\n";
    echo "  Name: {$user->name}\n";
    echo "  Roles: {$roles}\n";
    echo "  Status: {$user->status}\n\n";
}

// Check Categories
echo "\nCATEGORIES:\n";
echo str_repeat('-', 50) . "\n";
$parentCategories = App\Models\Category::whereNull('parent_id')->with('children')->get();
foreach ($parentCategories as $parent) {
    echo "• {$parent->name} ({$parent->children->count()} subcategories)\n";
}

// Check Products
echo "\n\nPRODUCTS:\n";
echo str_repeat('-', 50) . "\n";
$products = App\Models\Product::with('category', 'seller')->withCount('items')->take(5)->get();
foreach ($products as $product) {
    echo "• {$product->name}\n";
    echo "  Category: {$product->category->name}\n";
    echo "  Seller: {$product->seller->name}\n";
    echo "  Price: \${$product->price}\n";
    echo "  Items: {$product->items_count}\n";
    echo "  Status: {$product->status}\n\n";
}

// Summary
echo "\nSUMMARY:\n";
echo str_repeat('=', 50) . "\n";
echo "Total Users: " . App\Models\User::count() . "\n";
echo "Total Categories: " . App\Models\Category::count() . "\n";
echo "Total Products: " . App\Models\Product::count() . "\n";
echo "Total Product Items: " . App\Models\ProductItem::count() . "\n";
echo "Total Orders: " . App\Models\Order::count() . "\n";

echo "\nDatabase is ready! ✓\n";
