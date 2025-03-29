<?php

namespace App\Http\Requests\User\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'plan_name' => 'required|string|in:basic,premium,professional',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'auto_renew' => 'required|boolean',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'start_date' => 'nullable|date|after_or_equal:today',
            'billing_details' => 'required|array',
            'billing_details.name' => 'required|string|max:255',
            'billing_details.address' => 'required|string|max:255',
            'billing_details.zip_code' => 'required|string|max:10',
            'billing_details.tax_id' => 'nullable|string|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'plan_name.required' => 'El nombre del plan es requerido.',
            'plan_name.in' => 'El plan seleccionado no es válido.',
            'payment_method_id.required' => 'El método de pago es requerido.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'auto_renew.required' => 'Debe especificar si desea renovación automática.',
            'billing_cycle.required' => 'El ciclo de facturación es requerido.',
            'billing_cycle.in' => 'El ciclo de facturación no es válido.',
            'coupon_code.exists' => 'El código de cupón no es válido.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'billing_details.required' => 'Los detalles de facturación son requeridos.',
            'billing_details.name.required' => 'El nombre de facturación es requerido.',
            'billing_details.address.required' => 'La dirección de facturación es requerida.',
            'billing_details.zip_code.required' => 'El código postal es requerido.'
        ];
    }
}
