<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Bangladeshi numbers get typed every which way. Strip everything
        // that is not a digit so "+880 1347-419040" and "01347419040" match.
        if ($this->customer_phone) {
            $digits = preg_replace('/\D/', '', $this->customer_phone);

            if (str_starts_with($digits, '880')) {
                $digits = '0' . substr($digits, 3);
            }

            $this->merge(['customer_phone' => $digits]);
        }
    }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_phone'   => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
            'customer_email'   => ['nullable', 'email', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_area'    => ['nullable', 'string', 'max:120'],
            'shipping_zone_id' => ['required', 'exists:shipping_zones,id'],
            'customer_note'    => ['nullable', 'string', 'max:500'],
            'save_address'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => __('Enter a valid Bangladeshi mobile number, like 01347419040.'),
            'shipping_zone_id.required' => __('Choose a delivery area so we can work out the charge.'),
        ];
    }
}
