<?php

namespace App\Http\Requests\User\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reported_user' => 'nullable|exists:users,id|different:user_id',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'type' => 'required|in:fraud,abuse,payment_issue,other',
            'description' => 'required|string|min:20|max:1000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpeg,png,pdf,mp4|max:10240',
            'urgency_level' => 'nullable|in:low,medium,high'
        ];
    }

    public function messages(): array
    {
        return [
            'reported_user.exists' => 'El usuario reportado no existe.',
            'reported_user.different' => 'No puedes reportarte a ti mismo.',
            'service_request_id.exists' => 'La solicitud de servicio no existe.',
            'transaction_id.exists' => 'La transacción no existe.',
            'type.required' => 'El tipo de reporte es requerido.',
            'type.in' => 'El tipo de reporte no es válido.',
            'description.required' => 'La descripción es requerida.',
            'description.min' => 'La descripción debe tener al menos 20 caracteres.',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres.',
            'evidence.*.file' => 'La evidencia debe ser un archivo válido.',
            'evidence.*.mimes' => 'La evidencia debe ser una imagen, PDF o video.',
            'evidence.*.max' => 'La evidencia no puede exceder 10MB.',
            'urgency_level.in' => 'El nivel de urgencia no es válido.'
        ];
    }
}
