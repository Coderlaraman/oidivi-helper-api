<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class GlobalSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:users,service_requests,service_offers,all',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'max_rating' => 'nullable|numeric|min:0|max:5|gte:min_rating',
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0|gte:min_budget',
            'status' => 'nullable|array',
            'status.*' => 'string|in:published,in_progress,completed,canceled',
            'priority' => 'nullable|array',
            'priority.*' => 'string|in:low,medium,high,urgent',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100',
            'sort_by' => 'nullable|string|in:relevance,date,rating,budget,distance',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'query.max' => 'La consulta de búsqueda no puede exceder 255 caracteres.',
            'type.in' => 'El tipo de búsqueda debe ser: users, service_requests, service_offers o all.',
            'skills.*.exists' => 'Una o más habilidades seleccionadas no existen.',
            'categories.*.exists' => 'Una o más categorías seleccionadas no existen.',
            'min_rating.between' => 'La calificación mínima debe estar entre 0 y 5.',
            'max_rating.between' => 'La calificación máxima debe estar entre 0 y 5.',
            'max_rating.gte' => 'La calificación máxima debe ser mayor o igual a la mínima.',
            'min_budget.min' => 'El presupuesto mínimo debe ser mayor a 0.',
            'max_budget.gte' => 'El presupuesto máximo debe ser mayor o igual al mínimo.',
            'status.*.in' => 'El estado debe ser: published, in_progress, completed o canceled.',
            'priority.*.in' => 'La prioridad debe ser: low, medium, high o urgent.',
            'date_to.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'radius.between' => 'El radio debe estar entre 1 y 100 km.',
            'sort_by.in' => 'El ordenamiento debe ser: relevance, date, rating, budget o distance.',
            'sort_order.in' => 'El orden debe ser: asc o desc.',
            'per_page.between' => 'Los elementos por página deben estar entre 1 y 100.',
            'page.min' => 'La página debe ser mayor a 0.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'query' => 'consulta de búsqueda',
            'type' => 'tipo de búsqueda',
            'skills' => 'habilidades',
            'categories' => 'categorías',
            'min_rating' => 'calificación mínima',
            'max_rating' => 'calificación máxima',
            'min_budget' => 'presupuesto mínimo',
            'max_budget' => 'presupuesto máximo',
            'status' => 'estado',
            'priority' => 'prioridad',
            'date_from' => 'fecha inicial',
            'date_to' => 'fecha final',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'radius' => 'radio',
            'sort_by' => 'ordenar por',
            'sort_order' => 'orden',
            'per_page' => 'elementos por página',
            'page' => 'página'
        ];
    }
}