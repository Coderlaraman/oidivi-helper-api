<?php

namespace App\Http\Requests\User\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() &&
               $this->subscription->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'plan_name' => 'sometimes|required|string|in:basic,premium,professional',
            'payment_method_id' => 'sometimes|required|exists:payment_methods,id',
            'auto_renew' => 'sometimes|required|boolean',
            'billing_cycle' => 'sometimes|required|in:monthly,quarterly,yearly',
            'cancel_at_period_end' => 'sometimes|required|boolean',
            'cancellation_reason' => 'required_if:cancel_at_period_end,true|nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'plan_name.in' => 'The selected plan is invalid.',
            'payment_method_id.exists' => 'The selected payment method is invalid.',
            'billing_cycle.in' => 'The billing cycle is invalid.',
            'cancellation_reason.required_if' => 'The cancellation reason is required when canceling the subscription.'
        ];
    }
}
