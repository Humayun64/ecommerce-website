<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Payment;
use App\Models\ReturnRequest;
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

        // Menus and the cart badge, on every storefront page.
        View::composer('site.*', function ($view) {
            $header = collect();
            $footer = collect();
            $cartCount = 0;

            if (Schema::hasTable('menu_items')) {
                $menu = MenuItem::active()->orderBy('column')->orderBy('sort_order')->get();
                $header = $menu->where('location', 'header')->values();
                $footer = $menu->where('location', 'footer')->groupBy('column');
            }

            if (Schema::hasTable('carts')) {
                $cartCount = app(CartService::class)->count();
            }

            // Three most recent posts for the strip above the footer.
            $latestPosts = Schema::hasTable('posts')
                ? Post::live()->latest('published_at')->take(3)->get()
                : collect();

            $view->with('headerMenu', $header)
                 ->with('footerMenu', $footer)
                 ->with('latestPosts', $latestPosts)
                 ->with('cartCount', $cartCount);
        });

        // The badge on the admin sidebar: returns still waiting on a decision.
        View::composer('admin.*', function ($view) {
            $view->with('pendingReturns', Schema::hasTable('return_requests')
                ? ReturnRequest::where('status', 'pending')->count()
                : 0);

            $view->with('pendingPayments', Schema::hasTable('payments')
                ? Payment::where('status', 'pending')->count()
                : 0);
        });

        // A guest who adds to the cart then logs in keeps what they picked.
        Event::listen(Login::class, function (Login $event) {
            if (Schema::hasTable('carts')) {
                app(CartService::class)->mergeGuestCart($event->user->id);
            }
        });
    }
}
