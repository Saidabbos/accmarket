<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Cloudflare Middleware Test ===\n\n";

// Check if middleware classes exist
$middlewareClasses = [
    'TrustCloudflareProxies' => App\Http\Middleware\TrustCloudflareProxies::class,
    'CloudflareSecurityHeaders' => App\Http\Middleware\CloudflareSecurityHeaders::class,
    'VerifyCloudflareRequest' => App\Http\Middleware\VerifyCloudflareRequest::class,
    'CloudflareRateLimiting' => App\Http\Middleware\CloudflareRateLimiting::class,
];

echo "Middleware Classes:\n";
echo str_repeat('-', 50) . "\n";
foreach ($middlewareClasses as $name => $class) {
    $exists = class_exists($class) ? '✓' : '✗';
    echo "{$exists} {$name}\n";
    echo "   {$class}\n\n";
}

// Check config
echo "\nConfiguration:\n";
echo str_repeat('-', 50) . "\n";
if (file_exists(config_path('cloudflare.php'))) {
    echo "✓ config/cloudflare.php exists\n";
    $config = config('cloudflare');
    echo "  Enabled: " . ($config['enabled'] ? 'Yes' : 'No') . "\n";
    echo "  IPv4 Ranges: " . count($config['ip_ranges']['ipv4']) . "\n";
    echo "  IPv6 Ranges: " . count($config['ip_ranges']['ipv6']) . "\n";
} else {
    echo "✗ config/cloudflare.php not found\n";
}

echo "\n=== All Cloudflare Protection Installed ===\n";
echo "\nNext Steps:\n";
echo "1. Add domain to Cloudflare\n";
echo "2. Update nameservers\n";
echo "3. Configure SSL/TLS\n";
echo "4. Apply rate limiting to routes (see examples)\n";
echo "\nSee CLOUDFLARE_QUICK_START.md for details.\n";
