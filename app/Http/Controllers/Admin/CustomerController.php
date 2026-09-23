<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Services\CustomerDirectory;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private CustomerDirectory $directory)
    {
    }

    public function index(Request $request)
    {
        return view('admin.customers.index', [
            'customers' => $this->directory->list($request),
            'accounts'  => $this->directory->accountsWithoutOrders($request),
            'blocked'   => CustomerProfile::where('is_blocked', true)->count(),
        ]);
    }

    public function show(string $phone)
    {
        $data = $this->directory->show($phone);

        abort_if($data['orders']->isEmpty() && ! $data['user'], 404);

        return view('admin.customers.show', $data);
    }

    public function update(Request $request, string $phone)
    {
        $phone = CustomerProfile::normalise($phone);

        abort_unless($phone, 404);

        $data = $request->validate([
            'note'         => ['nullable', 'string', 'max:2000'],
            'block_reason' => ['nullable', 'string', 'max:200'],
            'action'       => ['required', 'in:save,block,unblock'],
        ]);

        $profile = CustomerProfile::firstOrNew(['phone' => $phone]);
        $profile->note = $data['note'] ?? null;

        if ($data['action'] === 'block') {
            $profile->is_blocked   = true;
            $profile->block_reason = $data['block_reason'] ?? null;
            $profile->blocked_at   = now();
            $message = __('Customer blocked. They cannot place new orders.');
        } elseif ($data['action'] === 'unblock') {
            $profile->is_blocked   = false;
            $profile->block_reason = null;
            $profile->blocked_at   = null;
            $message = __('Customer unblocked.');
        } else {
            $message = __('Note saved.');
        }

        $profile->save();

        return back()->with('status', $message);
    }
}
