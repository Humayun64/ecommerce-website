<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->phone) {
            $digits = preg_replace('/\D/', '', $this->phone);

            if (str_starts_with($digits, '880')) {
                $digits = '0' . substr($digits, 3);
            }

            $this->merge(['phone' => $digits]);
        }
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, like 01347419040.'),
        ];
    }
}
