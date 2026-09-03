<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartService::class);
    }

    public function boot(): void
    {
        // Store settings are needed by nearly every view, so share them once.
        View::share('settings', Setting::all_cached());

        // Storefront nav and the cart badge.
        View::composer('site.*', function ($view) {
            $categories = [];
            $cartCount  = 0;

            if (Schema::hasTable('categories')) {
                $categories = Category::active()
                    ->roots()
                    ->with(['children' => fn ($q) => $q->where('is_active', true)])
                    ->orderBy('sort_order')
                    ->get();
            }

            if (Schema::hasTable('carts')) {
                $cartCount = app(CartService::class)->count();
            }

            $view->with('navCategories', $categories)
                 ->with('cartCount', $cartCount);
        });

        // A guest who adds to the cart then logs in keeps what they picked.
        Event::listen(Login::class, function (Login $event) {
            if (Schema::hasTable('carts')) {
                app(CartService::class)->mergeGuestCart($event->user->id);
            }
        });
    }
}
