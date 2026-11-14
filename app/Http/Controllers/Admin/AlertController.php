<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAlerts;

class AlertController extends Controller
{
    /** Asked for every few seconds by the admin panel, so it stays small. */
    public function index(AdminAlerts $alerts)
    {
        return response()->json([
            'counts' => $alerts->counts(),
            'latest' => $alerts->latest(),
            'items'  => $alerts->items(),
        ])->header('Cache-Control', 'no-store');
    }
}
