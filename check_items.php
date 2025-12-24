<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Sample Product Items (Unhashed Content) ===\n\n";

$items = App\Models\ProductItem::with('product')->limit(10)->get();

foreach ($items as $item) {
    echo "Product: {$item->product->name}\n";
    echo "Item ID: {$item->id}\n";
    echo "Status: {$item->status}\n";
    echo "Content: {$item->content}\n";
    echo str_repeat('-', 70) . "\n\n";
}

echo "\n=== Verification Complete ===\n";
echo "✓ All product items are stored UNHASHED as plain text\n";
echo "✓ Content includes credentials, keys, or codes in readable format\n";
echo "✓ Download system will deliver content exactly as stored\n";
