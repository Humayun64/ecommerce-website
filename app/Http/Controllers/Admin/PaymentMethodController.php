<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function __construct(private ImageService $images)
    {
    }

    public function index()
    {
        return view('admin.payment-methods.index', [
            'methods'  => PaymentMethod::withCount('payments')->orderBy('sort_order')->orderBy('id')->get(),
            'drivers'  => PaymentMethod::DRIVERS,
            'gateways' => PaymentMethod::GATEWAYS,
        ]);
    }

    public function create()
    {
        return view('admin.payment-methods.create', [
            'method'   => new PaymentMethod(['needs_sender' => true, 'needs_txn' => true, 'is_active' => true]),
            'drivers'  => PaymentMethod::DRIVERS,
            'gateways' => PaymentMethod::GATEWAYS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->images->store($request->file('logo'), 'payments');
        }

        $method = PaymentMethod::create($data);
        $this->keepOneDefault($method);

        return redirect()->route('admin.payment-methods.index')
            ->with('status', __(':name added.', ['name' => $method->name]));
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.edit', [
            'method'   => $paymentMethod,
            'drivers'  => PaymentMethod::DRIVERS,
            'gateways' => PaymentMethod::GATEWAYS,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validated($request, $paymentMethod->id);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->images->store($request->file('logo'), 'payments');
        }

        $paymentMethod->update($data);
        $this->keepOneDefault($paymentMethod);

        return redirect()->route('admin.payment-methods.index')
            ->with('status', __(':name saved.', ['name' => $paymentMethod->name]));
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        // Deleting a method that orders point at would leave those receipts
        // unreadable. Switching it off does the same job without the damage.
        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', __('Orders have been paid with :name, so it cannot be deleted. Switch it off instead.', [
                'name' => $paymentMethod->name,
            ]));
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')
            ->with('status', __('Payment method deleted.'));
    }

    private function validated(Request $request, ?int $ignoreId): array
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_-]+$/', Rule::unique('payment_methods', 'code')->ignore($ignoreId)],
            'name'           => ['required', 'string', 'max:60'],
            'tagline'        => ['nullable', 'string', 'max:160'],
            'driver'         => ['required', Rule::in(array_keys(PaymentMethod::DRIVERS))],
            'gateway'        => ['nullable', Rule::in(array_keys(PaymentMethod::GATEWAYS))],
            'account_number' => ['nullable', 'string', 'max:40'],
            'account_type'   => ['nullable', 'string', 'max:40'],
            'instructions'   => ['nullable', 'string', 'max:1200'],
            'accent'         => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'needs_sender'   => ['nullable', 'boolean'],
            'needs_txn'      => ['nullable', 'boolean'],
            'charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_amount'     => ['nullable', 'numeric', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
            'is_default'     => ['nullable', 'boolean'],
            'sort_order'     => ['nullable', 'integer', 'min:0', 'max:999'],
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:1024'],
        ], [
            'code.regex'   => __('Use lowercase letters, numbers, dashes or underscores only.'),
            'accent.regex' => __('The colour must be a hex code like #E2136E.'),
        ]);

        $data['needs_sender']   = $request->boolean('needs_sender');
        $data['needs_txn']      = $request->boolean('needs_txn');
        $data['is_active']      = $request->boolean('is_active');
        $data['is_default']     = $request->boolean('is_default');
        $data['charge_percent'] = $data['charge_percent'] ?? 0;
        $data['sort_order']     = $data['sort_order'] ?? 0;

        // Cash on delivery asks for nothing, whatever the boxes said.
        if ($data['driver'] !== 'manual') {
            $data['needs_sender'] = false;
            $data['needs_txn']    = false;
        }

        if ($data['driver'] !== 'gateway') {
            $data['gateway'] = null;
        }

        unset($data['logo']);

        return $data;
    }

    /** Exactly one method can be the one pre-selected at checkout. */
    private function keepOneDefault(PaymentMethod $method): void
    {
        if ($method->is_default) {
            PaymentMethod::where('id', '!=', $method->id)->update(['is_default' => false]);
        }
    }
}
