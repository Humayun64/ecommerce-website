<?php

namespace App\Http\Controllers;

/**
 * Where a person goes straight after logging in.
 *
 * Breeze always sends people to /dashboard, and this decides what that
 * means for them: the panel for an admin, their orders for a customer.
 *
 * It is a controller rather than a closure on purpose — `php artisan
 * route:cache`, which runs on the live server, refuses to cache a
 * route that holds a closure and the whole command fails.
 */
class DashboardRedirectController extends Controller
{
    public function __invoke()
    {
        return auth()->user()?->is_admin
            ? redirect()->route('admin.dashboard')
            : redirect()->route('account.dashboard');
    }
}
