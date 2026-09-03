<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/* ---------- storefront ---------- */

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [CatalogController::class, 'index'])->name('shop');
Route::get('/category/{category:slug}', [CatalogController::class, 'category'])->name('shop.category');
Route::get('/brand/{brand:slug}', [CatalogController::class, 'brand'])->name('shop.brand');
Route::get('/product/{product:slug}', [CatalogController::class, 'show'])->name('shop.product');

/* ---------- cart ---------- */

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

/* ---------- checkout ---------- */

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/{number}/confirmed', [CheckoutController::class, 'done'])->name('checkout.done');

Route::get('/track', [CheckoutController::class, 'trackForm'])->name('orders.track');
Route::post('/track', [CheckoutController::class, 'track'])->name('orders.track.submit');

/* ---------- customer account ---------- */

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ---------- admin ---------- */

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('brands', BrandController::class)->except('show');
        Route::resource('attributes', AttributeController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');

        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])
            ->name('products.images.destroy');
        Route::patch('products/{product}/images/{image}/primary', [ProductController::class, 'makePrimaryImage'])
            ->name('products.images.primary');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';
