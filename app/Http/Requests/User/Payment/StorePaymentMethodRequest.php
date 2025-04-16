<?php

namespace App\Http\Requests\User\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:credit_card,paypal,bank_transfer,crypto',
            'provider' => 'required|string',
            'token' => 'required|string',
            'is_default' => 'boolean',
            'billing_details' => 'required|array',
            'billing_details.name' => 'required|string|max:255',
            'billing_details.address' => 'required|string|max:255',
            'billing_details.zip_code' => 'required|string|max:10'
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The payment method type is required.',
            'type.in' => 'The payment method type is invalid.',
            'provider.required' => 'The provider is required.',
            'token.required' => 'The token is required.',
            'billing_details.required' => 'The billing details are required.',
            'billing_details.name.required' => 'The billing name is required.',
            'billing_details.address.required' => 'The billing address is required.',
            'billing_details.zip_code.required' => 'The billing zip code is required.'
        ];
    }
}
