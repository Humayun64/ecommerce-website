<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Normalise the phone the same way checkout does, so guest orders
        // placed with "+880 1347-419040" still match this account.
        if ($request->phone) {
            $digits = preg_replace('/\D/', '', $request->phone);

            if (str_starts_with($digits, '880')) {
                $digits = '0' . substr($digits, 3);
            }

            $request->merge(['phone' => $digits]);
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, like 01347419040.'),
        ]);

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Anything they ordered as a guest on this number becomes theirs.
        $claimed = Order::whereNull('user_id')
            ->where('customer_phone', $user->phone)
            ->update(['user_id' => $user->id]);

        return redirect(route('account.dashboard', absolute: false))
            ->with('status', $claimed
                ? __('Welcome. We found :n past order(s) on your number and added them.', ['n' => $claimed])
                : __('Welcome to :store.', ['store' => config('app.name')]));
    }
}
