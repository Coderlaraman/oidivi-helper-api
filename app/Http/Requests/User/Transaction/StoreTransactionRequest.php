<?php

namespace App\Http\Requests\User\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'payee_id' => 'required|exists:users,id|different:user_id',
            'service_request_id' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency' => 'required|string|size:3|in:USD,EUR,GBP',
            'description' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
            'scheduled_date' => 'nullable|date|after:today',
            'installments' => 'nullable|integer|min:1|max:12',
            'terms_accepted' => 'required|accepted'
        ];
    }

    public function messages(): array
    {
        return [
            'payee_id.required' => 'El destinatario del pago es requerido.',
            'payee_id.exists' => 'El destinatario seleccionado no existe.',
            'payee_id.different' => 'No puedes realizarte un pago a ti mismo.',
            'service_request_id.required' => 'La solicitud de servicio es requerida.',
            'amount.required' => 'El monto es requerido.',
            'amount.min' => 'El monto debe ser mayor a 0.',
            'payment_method_id.required' => 'El método de pago es requerido.',
            'currency.required' => 'La moneda es requerida.',
            'currency.in' => 'La moneda seleccionada no es válida.',
            'scheduled_date.after' => 'La fecha programada debe ser posterior a hoy.',
            'installments.min' => 'El número de cuotas debe ser al menos 1.',
            'installments.max' => 'El número máximo de cuotas es 12.',
            'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones.'
        ];
    }
}
