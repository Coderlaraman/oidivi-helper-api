<?php

namespace App\Http\Requests\User\ServiceRequest;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserServiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Obtener el ID de la ruta
        $serviceRequestId = $this->route('id');
        
        // Buscar la solicitud de servicio
        $serviceRequest = ServiceRequest::find($serviceRequestId);

        if (!$serviceRequest) {
            return false;
        }

        // Verificar que el usuario autenticado sea el propietario
        return auth()->check() && $serviceRequest->user_id === auth()->id();
    }

    public function rules(): array
    {
        $rules = [
            'status' => 'required|in:published,in_progress,completed,canceled',
            'cancellation_reason' => 'required_if:status,canceled|nullable|string|max:500',
            'completion_notes' => 'required_if:status,completed|nullable|string|max:1000',
        ];

        // Solo agregar reglas de completion_evidence si se están enviando archivos
        if ($this->hasFile('completion_evidence')) {
            $rules['completion_evidence'] = 'array';
            $rules['completion_evidence.*'] = 'file|mimes:jpeg,png,pdf|max:5120';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status is required.',
            'status.in' => 'Selected status is not valid.',
            'cancellation_reason.required_if' => 'The cancellation reason is required when canceling a service.',
            'completion_notes.required_if' => 'The completion notes are required when completing a service.',
            'completion_evidence.array' => 'The completion evidence must be provided as an array of files.',
            'completion_evidence.*.file' => 'Each evidence item must be a valid file.',
            'completion_evidence.*.mimes' => 'Evidence files must be jpeg, png, or pdf.',
            'completion_evidence.*.max' => 'Evidence files cannot exceed 5MB.'
        ];
    }
}


