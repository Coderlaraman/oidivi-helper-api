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
            'plan_name.required' => 'The plan name is required.',
            'plan_name.in' => 'The selected plan is invalid.',
            'payment_method_id.required' => 'The payment method is required.',
            'payment_method_id.exists' => 'The selected payment method is invalid.',
            'auto_renew.required' => 'You must specify if you want automatic renewal.',
            'billing_cycle.required' => 'The billing cycle is required.',
            'billing_cycle.in' => 'The billing cycle is invalid.',
            'coupon_code.exists' => 'The coupon code is invalid.',
            'start_date.after_or_equal' => 'The start date must be today or later.',
            'billing_details.required' => 'The billing details are required.',
            'billing_details.name.required' => 'The billing name is required.',
            'billing_details.address.required' => 'The billing address is required.',
            'billing_details.zip_code.required' => 'The zip code is required.'
        ];
    }
}
