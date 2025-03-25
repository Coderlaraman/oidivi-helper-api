<?php

namespace App\Http\Requests\Client\Review;

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
            'comment' => 'required|string|min:10|max:500',
            'aspects' => 'nullable|array',
            'aspects.*' => 'required|in:punctuality,professionalism,quality,communication',
            'aspects_ratings' => 'nullable|array',
            'aspects_ratings.*' => 'required|integer|between:1,5',
            'would_recommend' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'reviewed_id.required' => 'El usuario a calificar es requerido.',
            'reviewed_id.exists' => 'El usuario a calificar no existe.',
            'reviewed_id.not_in' => 'No puedes calificarte a ti mismo.',
            'reviewed_id.unique' => 'Ya has calificado a este usuario para este servicio.',
            'service_request_id.required' => 'La solicitud de servicio es requerida.',
            'service_request_id.exists' => 'La solicitud de servicio no existe.',
            'rating.required' => 'La calificación es requerida.',
            'rating.between' => 'La calificación debe estar entre 1 y 5.',
            'comment.required' => 'El comentario es requerido.',
            'comment.min' => 'El comentario debe tener al menos 10 caracteres.',
            'comment.max' => 'El comentario no puede exceder los 500 caracteres.',
            'aspects.*.in' => 'El aspecto seleccionado no es válido.',
            'aspects_ratings.*.between' => 'La calificación del aspecto debe estar entre 1 y 5.',
            'would_recommend.required' => 'Debes indicar si recomendarías al usuario.'
        ];
    }
} 