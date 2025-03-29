<?php

namespace App\Http\Requests\User\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category' => 'required|in:account,payment,technical,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:20|max:2000',
            'priority' => 'required|in:low,medium,high',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'related_service_id' => 'nullable|exists:service_requests,id',
            'related_transaction_id' => 'nullable|exists:transactions,id'
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'La categoría del ticket es requerida.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'subject.required' => 'El asunto del ticket es requerido.',
            'message.required' => 'El mensaje es requerido.',
            'message.min' => 'El mensaje debe tener al menos 20 caracteres.',
            'priority.required' => 'La prioridad es requerida.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'attachments.*.file' => 'El archivo adjunto debe ser válido.',
            'attachments.*.mimes' => 'El archivo adjunto debe ser PDF, imagen o documento de Word.',
            'attachments.*.max' => 'El archivo adjunto no puede exceder 5MB.',
            'related_service_id.exists' => 'El servicio relacionado no existe.',
            'related_transaction_id.exists' => 'La transacción relacionada no existe.'
        ];
    }
}
