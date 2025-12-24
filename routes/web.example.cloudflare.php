<?php

/**
 * Example Route Configuration with Cloudflare Middleware
 *
 * This file demonstrates how to apply Cloudflare protection middleware
 * to different routes based on security requirements.
 *
 * Copy these patterns to your routes/web.php as needed.
 */

use Illuminate\Support\Facades\Route;

// ============================================
// EXAMPLE 1: Public Routes with Rate Limiting
// ============================================

// Apply moderate rate limiting to public product browsing
Route::middleware(['cloudflare.throttle:120:1'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
});

// ============================================
// EXAMPLE 2: Authentication Routes with Strict Rate Limiting
// ============================================

// Strict rate limiting for login/register to prevent brute force
Route::middleware(['cloudflare.throttle:10:1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
});

// ============================================
// EXAMPLE 3: Checkout and Payment Routes
// ============================================

// Protected checkout process with moderate rate limiting
Route::middleware(['auth', 'cloudflare.throttle:30:1'])->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'process']);
    Route::post('/payment', [PaymentController::class, 'store']);
    Route::post('/cart/add', [CartController::class, 'add']);
});

// ============================================
// EXAMPLE 4: Admin Routes with Cloudflare Verification
// ============================================

// Admin area: Verify Cloudflare + IP whitelist (configure in CF dashboard)
Route::middleware(['auth', 'role:admin', 'cloudflare.verify', 'cloudflare.throttle:60:1'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::resource('users', AdminUserController::class);
        Route::resource('products', AdminProductController::class);
        Route::resource('orders', AdminOrderController::class);
    });

// ============================================
// EXAMPLE 5: API Routes with Custom Rate Limits
// ============================================

// Read-heavy endpoints: Higher rate limits
Route::middleware(['cloudflare.throttle:200:1'])->group(function () {
    Route::get('/api/products', [ApiProductController::class, 'index']);
    Route::get('/api/categories', [ApiCategoryController::class, 'index']);
});

// Write endpoints: Lower rate limits
Route::middleware(['auth', 'cloudflare.throttle:20:1'])->group(function () {
    Route::post('/api/reviews', [ApiReviewController::class, 'store']);
    Route::post('/api/disputes', [ApiDisputeController::class, 'store']);
});

// Critical endpoints: Very strict limits
Route::middleware(['auth', 'cloudflare.throttle:5:1'])->group(function () {
    Route::post('/api/withdraw', [WithdrawController::class, 'process']);
    Route::post('/api/refund', [RefundController::class, 'process']);
});

// ============================================
// EXAMPLE 6: Download Routes with Rate Limiting
// ============================================

// Prevent download abuse
Route::middleware(['auth', 'cloudflare.throttle:50:1'])->group(function () {
    Route::post('/download/link/{order}/{orderItem}', [DownloadController::class, 'generateDownloadLink']);
    Route::post('/download/bulk/{order}', [DownloadController::class, 'generateBulkDownloadLink']);
});

// Signed download URLs (already have expiration, add rate limiting as backup)
Route::middleware(['cloudflare.throttle:100:5'])->group(function () {
    Route::get('/download/{order}/{orderItem}', [DownloadController::class, 'downloadFile'])->name('download.file');
    Route::get('/download/all/{order}', [DownloadController::class, 'downloadAll'])->name('download.all');
});

// ============================================
// EXAMPLE 7: Search and Filter Routes
// ============================================

// Prevent search abuse and scraping
Route::middleware(['cloudflare.throttle:60:1'])->group(function () {
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/products/filter', [ProductController::class, 'filter']);
});

// ============================================
// EXAMPLE 8: Seller Dashboard Routes
// ============================================

// Seller area with moderate protection
Route::middleware(['auth', 'role:seller', 'cloudflare.throttle:100:1'])
    ->prefix('seller')
    ->group(function () {
        Route::get('/dashboard', [SellerDashboardController::class, 'index']);
        Route::resource('products', SellerProductController::class);
        Route::get('/orders', [SellerOrderController::class, 'index']);
        Route::get('/analytics', [SellerAnalyticsController::class, 'index']);
    });

// ============================================
// EXAMPLE 9: Public Pages (No Rate Limiting)
// ============================================

// Static pages - no rate limiting needed (Cloudflare caches these)
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [PageController::class, 'about']);
Route::get('/terms', [PageController::class, 'terms']);
Route::get('/privacy', [PageController::class, 'privacy']);
Route::get('/faq', [PageController::class, 'faq']);

// ============================================
// EXAMPLE 10: Production-Only Cloudflare Verification
// ============================================

// Only verify Cloudflare in production (blocks direct access)
if (app()->environment('production')) {
    // Apply to all routes
    Route::middleware(['cloudflare.verify'])->group(function () {
        // All your routes here
    });
}

// Or selectively:
Route::middleware(['cloudflare.verify'])->group(function () {
    // Only these critical routes require Cloudflare in production
    Route::prefix('admin')->group(function () {
        // Admin routes
    });
});

/**
 * RATE LIMIT SYNTAX:
 *
 * Format: cloudflare.throttle:{requests}:{minutes}
 *
 * Examples:
 * - 'cloudflare.throttle:60:1'   = 60 requests per 1 minute
 * - 'cloudflare.throttle:100:5'  = 100 requests per 5 minutes
 * - 'cloudflare.throttle:1000:60' = 1000 requests per 1 hour
 *
 * Recommendations by Route Type:
 * - Public browsing: 120:1 (120 per minute)
 * - Login/Register: 10:1 (10 per minute)
 * - Authenticated browsing: 100:1 (100 per minute)
 * - Form submissions: 20:1 (20 per minute)
 * - Critical actions: 5:1 (5 per minute)
 * - Downloads: 50:1 (50 per minute)
 * - Search/Filter: 60:1 (60 per minute)
 * - Admin actions: 60:1 (60 per minute)
 */
