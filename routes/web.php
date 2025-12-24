<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\DownloadController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Shop routes (public)
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopProductController::class, 'index'])->name('index');
    Route::get('/product/{product}', [ShopProductController::class, 'show'])->name('product');
});

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

// Checkout routes (authenticated)
Route::middleware(['auth', 'verified'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
});

// Payment routes (authenticated)
Route::middleware(['auth', 'verified'])->prefix('payment')->name('payment.')->group(function () {
    Route::get('/order/{order}', [PaymentController::class, 'show'])->name('show');
    Route::post('/order/{order}/pay', [PaymentController::class, 'initiate'])->name('initiate');
    Route::get('/order/{order}/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/order/{order}/cancel', [PaymentController::class, 'cancel'])->name('cancel');
});

// Order history routes (authenticated)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [PaymentController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [PaymentController::class, 'orderShow'])->name('orders.show');
});

// Download routes (authenticated, signed URLs)
Route::middleware(['auth', 'verified'])->prefix('download')->name('download.')->group(function () {
    Route::get('/order/{order}/item/{orderItem}/link', [DownloadController::class, 'generateDownloadLink'])->name('link');
    Route::get('/order/{order}/all/link', [DownloadController::class, 'generateBulkDownloadLink'])->name('all.link');
});

// Signed download routes (no auth middleware - verified by signature)
Route::get('/download/order/{order}/item/{orderItem}', [DownloadController::class, 'downloadFile'])->name('download.file');
Route::get('/download/order/{order}/all', [DownloadController::class, 'downloadAll'])->name('download.all');

// IPN webhook (no auth - external callback)
Route::post('/payment/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');

// Seller routes
Route::middleware(['auth', 'verified', 'role:seller,admin'])->prefix('seller')->name('seller.')->group(function () {
    Route::resource('products', SellerProductController::class);
    Route::patch('products/{product}/status', [SellerProductController::class, 'toggleStatus'])->name('products.status');
    Route::post('products/{product}/items', [SellerProductController::class, 'addItems'])->name('products.add-items');
});

// Admin routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::patch('categories/{category}/toggle', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle');

    // User management
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('users/{user}/roles', [AdminUserController::class, 'updateRoles'])->name('users.roles');
    Route::post('users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');

    // Product management
    Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [AdminProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('products/{product}/status', [AdminProductController::class, 'toggleStatus'])->name('products.status');
    Route::patch('products/{product}/featured', [AdminProductController::class, 'toggleFeatured'])->name('products.featured');
});

require __DIR__.'/auth.php';
