<?php

namespace App\Http\Requests\User\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() &&
               $this->transaction->payer_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|required|in:pending,completed,failed,refunded',
            'refund_reason' => 'required_if:status,refunded|nullable|string|max:500',
            'dispute_reason' => 'nullable|string|max:500',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120'
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'El estado seleccionado no es válido.',
            'refund_reason.required_if' => 'La razón del reembolso es requerida.',
            'dispute_reason.max' => 'La razón de la disputa no puede exceder 500 caracteres.',
            'evidence.*.file' => 'La evidencia debe ser un archivo válido.',
            'evidence.*.mimes' => 'La evidencia debe ser PDF o imagen.',
            'evidence.*.max' => 'La evidencia no puede exceder 5MB.'
        ];
    }
}
