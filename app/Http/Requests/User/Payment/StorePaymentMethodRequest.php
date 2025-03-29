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
            'type.required' => 'El tipo de método de pago es requerido.',
            'type.in' => 'El tipo de método de pago no es válido.',
            'provider.required' => 'El proveedor es requerido.',
            'token.required' => 'El token es requerido.',
            'billing_details.required' => 'Los detalles de facturación son requeridos.',
            'billing_details.name.required' => 'El nombre de facturación es requerido.',
            'billing_details.address.required' => 'La dirección de facturación es requerida.',
            'billing_details.zip_code.required' => 'El código postal de facturación es requerido.'
        ];
    }
}
