<?php

namespace App\Http\Requests\Client\Referral;

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
            'status.required' => 'El estado es requerido.',
            'status.in' => 'El estado debe ser aceptado o rechazado.',
            'rejection_reason.required_if' => 'La razón de rechazo es requerida cuando se rechaza una referencia.',
            'rejection_reason.max' => 'La razón de rechazo no puede exceder los 500 caracteres.'
        ];
    }
} 