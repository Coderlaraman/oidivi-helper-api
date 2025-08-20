<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequestSearchRequest extends FormRequest
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
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0|gte:min_budget',
            'status' => 'nullable|array',
            'status.*' => 'string|in:published,in_progress,completed,canceled',
            'priority' => 'nullable|array',
            'priority.*' => 'string|in:low,medium,high,urgent',
            'service_type' => 'nullable|string|in:one_time,recurring',
            'visibility' => 'nullable|string|in:public,private',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'due_date_from' => 'nullable|date',
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
            'has_offers' => 'nullable|boolean',
            'min_offers' => 'nullable|integer|min:0',
            'max_offers' => 'nullable|integer|min:0|gte:min_offers',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100',
            'sort_by' => 'nullable|string|in:relevance,created_at,budget,due_date,offers_count,distance',
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
            'categories.*.exists' => 'Una o más categorías seleccionadas no existen.',
            'min_budget.min' => 'El presupuesto mínimo debe ser mayor a 0.',
            'max_budget.gte' => 'El presupuesto máximo debe ser mayor o igual al mínimo.',
            'status.*.in' => 'El estado debe ser: published, in_progress, completed o canceled.',
            'priority.*.in' => 'La prioridad debe ser: low, medium, high o urgent.',
            'service_type.in' => 'El tipo de servicio debe ser: one_time o recurring.',
            'visibility.in' => 'La visibilidad debe ser: public o private.',
            'date_to.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
            'due_date_to.after_or_equal' => 'La fecha límite final debe ser posterior o igual a la inicial.',
            'min_offers.min' => 'El número mínimo de ofertas debe ser mayor a 0.',
            'max_offers.gte' => 'El número máximo de ofertas debe ser mayor o igual al mínimo.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'radius.between' => 'El radio debe estar entre 1 y 100 km.',
            'sort_by.in' => 'El ordenamiento debe ser: relevance, created_at, budget, due_date, offers_count o distance.',
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
            'categories' => 'categorías',
            'min_budget' => 'presupuesto mínimo',
            'max_budget' => 'presupuesto máximo',
            'status' => 'estado',
            'priority' => 'prioridad',
            'service_type' => 'tipo de servicio',
            'visibility' => 'visibilidad',
            'date_from' => 'fecha inicial',
            'date_to' => 'fecha final',
            'due_date_from' => 'fecha límite inicial',
            'due_date_to' => 'fecha límite final',
            'has_offers' => 'tiene ofertas',
            'min_offers' => 'número mínimo de ofertas',
            'max_offers' => 'número máximo de ofertas',
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