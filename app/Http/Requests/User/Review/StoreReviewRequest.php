<?php

namespace App\Http\Requests\User\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Campos básicos requeridos
            'reviewed_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([auth()->id()]),
                Rule::unique('reviews')->where(function ($query) {
                    return $query->where('reviewer_id', auth()->id())
                                ->where('service_request_id', $this->service_request_id);
                })
            ],
            'service_request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|min:10|max:1000',
            'would_recommend' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'reviewed_id.required' => 'El usuario a evaluar es requerido.',
            'reviewed_id.exists' => 'El usuario a evaluar no existe.',
            'reviewed_id.not_in' => 'No puedes evaluarte a ti mismo.',
            'reviewed_id.unique' => 'Ya has evaluado a este usuario para este servicio.',
            
            'service_request_id.required' => 'La solicitud de servicio es requerida.',
            'service_request_id.exists' => 'La solicitud de servicio no existe.',
            
            'rating.required' => 'La calificación es requerida.',
            'rating.integer' => 'La calificación debe ser un número entero.',
            'rating.between' => 'La calificación debe estar entre 1 y 5.',
            
            'comment.required' => 'El comentario es requerido.',
            'comment.string' => 'El comentario debe ser texto.',
            'comment.min' => 'El comentario debe tener al menos 10 caracteres.',
            'comment.max' => 'El comentario no puede exceder 1000 caracteres.',
            
            'would_recommend.required' => 'Debes indicar si recomendarías al usuario.',
            'would_recommend.boolean' => 'La recomendación debe ser verdadero o falso.'
        ];
    }

    /**
     * Configurar los datos después de la validación
     */
    public function passedValidation(): void
    {
        // Agregar reviewer_id automáticamente
        $this->merge([
            'reviewer_id' => auth()->id()
        ]);
    }


}
