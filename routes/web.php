<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/* ---------- storefront ---------- */

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [CatalogController::class, 'index'])->name('shop');
Route::get('/category/{category:slug}', [CatalogController::class, 'category'])->name('shop.category');
Route::get('/brand/{brand:slug}', [CatalogController::class, 'brand'])->name('shop.brand');
Route::get('/product/{product:slug}', [CatalogController::class, 'show'])->name('shop.product');
Route::post('/product/{product:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

/* ---------- blog ---------- */

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

/* ---------- cart ---------- */

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::post('/coupon', [CouponController::class, 'store'])->name('coupon.store');
Route::delete('/coupon', [CouponController::class, 'destroy'])->name('coupon.destroy');

/* ---------- checkout ---------- */

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/{number}/confirmed', [CheckoutController::class, 'done'])->name('checkout.done');

Route::get('/track', [CheckoutController::class, 'trackForm'])->name('orders.track');
Route::post('/track', [CheckoutController::class, 'track'])->name('orders.track.submit');

/* ---------- contact ---------- */

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

/* ---------- what search engines read ---------- */

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/* ---------- product feeds for Facebook and Google ---------- */

Route::get('/feed/facebook.xml', [FeedController::class, 'facebook'])->name('feeds.facebook');
Route::get('/feed/google.xml', [FeedController::class, 'google'])->name('feeds.google');

/* ---------- returns (works for guests too, once they have tracked the order) ---------- */

Route::get('/returns/{order}/new', [ReturnController::class, 'create'])->name('returns.create');
Route::post('/returns/{order}', [ReturnController::class, 'store'])->name('returns.store');
Route::get('/returns/{return}/sent', [ReturnController::class, 'done'])->name('returns.done');

/* ---------- customer account ---------- */

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('orders/{order}', [AccountController::class, 'order'])->name('order');
    Route::post('claim', [AccountController::class, 'claim'])->name('claim');
    Route::get('returns', [ReturnController::class, 'mine'])->name('returns');

    Route::get('addresses', [AccountController::class, 'addresses'])->name('addresses');
    Route::post('addresses', [AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::patch('addresses/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
    Route::delete('addresses/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
});

Route::get('/dashboard', fn () => redirect()->route('account.dashboard'))
    ->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ---------- admin ---------- */

/*
 | The panel's address comes from ADMIN_PATH in .env. Every link is built
 | from a route name, so changing it moves the whole panel at once and the
 | old address simply stops existing -- it is not redirected, because a
 | redirect would hand the new address straight to the bot that asked.
 */
Route::middleware(['auth', 'admin'])
    ->prefix(config('admin.path'))
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('orders/{order}/delivery', [OrderController::class, 'updateDelivery'])->name('orders.delivery');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{phone}', [CustomerController::class, 'show'])->name('customers.show');
        Route::patch('customers/{phone}', [CustomerController::class, 'update'])->name('customers.update');

        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::resource('coupons', AdminCouponController::class)->except('show');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('brands', BrandController::class)->except('show');
        Route::resource('attributes', AttributeController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');

        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])
            ->name('products.images.destroy');
        Route::patch('products/{product}/images/{image}/primary', [ProductController::class, 'makePrimaryImage'])
            ->name('products.images.primary');

        Route::prefix('delivery')->name('delivery.')->group(function () {
            Route::get('/', [DeliveryController::class, 'index'])->name('index');
            Route::post('rates', [DeliveryController::class, 'updateRates'])->name('rates');
            Route::post('products', [DeliveryController::class, 'assignProducts'])->name('products');

            Route::post('tiers', [DeliveryController::class, 'storeTier'])->name('tiers.store');
            Route::patch('tiers/{tier}', [DeliveryController::class, 'updateTier'])->name('tiers.update');
            Route::delete('tiers/{tier}', [DeliveryController::class, 'destroyTier'])->name('tiers.destroy');

            Route::post('zones', [DeliveryController::class, 'storeZone'])->name('zones.store');
            Route::patch('zones/{zone}', [DeliveryController::class, 'updateZone'])->name('zones.update');
            Route::delete('zones/{zone}', [DeliveryController::class, 'destroyZone'])->name('zones.destroy');
        });

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}', [MessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('contact-page', [AdminContactController::class, 'edit'])->name('contact.edit');
        Route::patch('contact-page', [AdminContactController::class, 'update'])->name('contact.update');

        Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
        Route::patch('sms/gateway', [SmsController::class, 'updateGateway'])->name('sms.gateway');
        Route::patch('sms/templates', [SmsController::class, 'updateTemplates'])->name('sms.templates');
        Route::post('sms/test', [SmsController::class, 'test'])->name('sms.test');

        Route::get('marketing', [MarketingController::class, 'index'])->name('marketing.index');
        Route::patch('marketing/codes', [MarketingController::class, 'updateCodes'])->name('marketing.codes');
        Route::patch('marketing/feeds', [MarketingController::class, 'updateFeeds'])->name('marketing.feeds');
        Route::post('marketing/refresh', [MarketingController::class, 'refreshFeeds'])->name('marketing.refresh');

        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::patch('payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');

        Route::resource('payment-methods', PaymentMethodController::class)->except('show');

        Route::get('returns', [AdminReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/{return}', [AdminReturnController::class, 'show'])->name('returns.show');
        Route::patch('returns/{return}', [AdminReturnController::class, 'update'])->name('returns.update');

        Route::resource('posts', AdminPostController::class)->except('show');

        Route::get('blog-categories', [BlogCategoryController::class, 'index'])->name('blog-categories.index');
        Route::post('blog-categories', [BlogCategoryController::class, 'store'])->name('blog-categories.store');
        Route::patch('blog-categories/{category}', [BlogCategoryController::class, 'update'])->name('blog-categories.update');
        Route::delete('blog-categories/{category}', [BlogCategoryController::class, 'destroy'])->name('blog-categories.destroy');

        Route::resource('pages', AdminPageController::class)->except('show');

        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        Route::patch('menus/{item}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{item}', [MenuController::class, 'destroy'])->name('menus.destroy');
        Route::post('menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
        Route::post('menus/columns', [MenuController::class, 'columns'])->name('menus.columns');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';

/*
 | A login page at the panel's own address, so your bookmark is one link.
 | It is the same form customers use -- a shop cannot hide /login, because
 | customers sign in there too. What this hides is the panel.
 */
if (class_exists(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)) {
    Route::get(config('admin.path') . '/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->middleware('guest')
        ->name('admin.login');
}

/* ---------- CMS pages ----------
 | Registered last on purpose: this matches any single-segment path that
 | nothing above claimed, so /about-us works while /login still reaches auth.
 */
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('page.show');
