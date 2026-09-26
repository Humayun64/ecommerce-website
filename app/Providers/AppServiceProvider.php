<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\Post;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\SmsNotifier;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
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

            $view->with('unreadMessages', Schema::hasTable('contact_messages')
                ? ContactMessage::where('status', 'new')->count()
                : 0);
        });

        // The tracking pixels are put into the page by middleware rather than
        // the layout, so they reach every storefront page including ones
        // added later, and removing an ID in admin really removes the script.
        $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\InjectTracking::class);

        $this->announceOrders();

        // A guest who adds to the cart then logs in keeps what they picked.
        Event::listen(Login::class, function (Login $event) {
            if (Schema::hasTable('carts')) {
                app(CartService::class)->mergeGuestCart($event->user->id);
            }
        });
    }

    /**
     * Text the customer when their order moves.
     *
     * Hooked to the model rather than to a controller so every route is
     * covered at once -- the storefront checkout, an order typed in by hand,
     * and a status changed from the admin list all pass through here.
     */
    private function announceOrders(): void
    {
        Order::created(function (Order $order) {
            $this->afterCommit(fn () => app(SmsNotifier::class)->notify($order, 'placed'));
        });

        Order::updated(function (Order $order) {
            if (! $order->wasChanged('status')) {
                return;
            }

            $notifier = app(SmsNotifier::class);
            $event    = $notifier->eventForStatus((string) $order->status);

            if ($event) {
                $this->afterCommit(fn () => $notifier->notify($order, $event));
            }
        });
    }

    /**
     * Run once the surrounding transaction has really committed.
     *
     * An order is created inside a transaction that can still roll back. A
     * text message cannot be rolled back, so sending one from inside that
     * transaction risks telling a customer about an order that never existed.
     */
    private function afterCommit(callable $job): void
    {
        try {
            $connection = DB::connection();

            if ($connection->transactionLevel() > 0 && method_exists($connection, 'afterCommit')) {
                $connection->afterCommit($job);

                return;
            }
        } catch (\Throwable $e) {
            // Fall through and run it now.
        }

        try {
            $job();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
