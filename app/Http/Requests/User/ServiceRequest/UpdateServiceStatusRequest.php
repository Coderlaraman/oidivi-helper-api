<?php

namespace App\Http\Requests\User\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() &&
               $this->service_request->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:published,in_progress,completed,canceled',
            'cancellation_reason' => 'required_if:status,canceled|nullable|string|max:500',
            'completion_notes' => 'required_if:status,completed|nullable|string|max:1000',
            'completion_evidence' => 'nullable|array',
            'completion_evidence.*' => 'file|mimes:jpeg,png,pdf|max:5120'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estado es requerido.',
            'status.in' => 'El estado seleccionado no es válido.',
            'cancellation_reason.required_if' => 'La razón de cancelación es requerida cuando se cancela un servicio.',
            'completion_notes.required_if' => 'Las notas de finalización son requeridas cuando se completa un servicio.',
            'completion_evidence.*.file' => 'La evidencia debe ser un archivo válido.',
            'completion_evidence.*.mimes' => 'La evidencia debe ser una imagen o PDF.',
            'completion_evidence.*.max' => 'La evidencia no puede exceder 5MB.'
        ];
    }
}
