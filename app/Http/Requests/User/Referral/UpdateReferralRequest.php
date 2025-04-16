<?php

namespace App\Http\Requests\User\Referral;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el referido puede actualizar el estado de la referencia
        return auth()->id() === $this->referral->referred_id;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:accepted,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be accepted or rejected.',
            'rejection_reason.required_if' => 'The rejection reason is required when rejecting a referral.',
            'rejection_reason.max' => 'The rejection reason cannot exceed 500 characters.'
        ];
    }
}
