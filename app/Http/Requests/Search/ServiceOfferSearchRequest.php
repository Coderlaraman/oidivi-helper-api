<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class ServiceOfferSearchRequest extends FormRequest
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
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'status' => 'nullable|array',
            'status.*' => 'string|in:pending,accepted,rejected,withdrawn',
            'min_estimated_time' => 'nullable|integer|min:1',
            'max_estimated_time' => 'nullable|integer|min:1|gte:min_estimated_time',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'provider_rating_min' => 'nullable|numeric|min:0|max:5',
            'provider_rating_max' => 'nullable|numeric|min:0|max:5|gte:provider_rating_min',
            'has_message' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100',
            'sort_by' => 'nullable|string|in:relevance,created_at,price,estimated_time,provider_rating,distance',
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
            'min_price.min' => 'El precio mínimo debe ser mayor a 0.',
            'max_price.gte' => 'El precio máximo debe ser mayor o igual al mínimo.',
            'status.*.in' => 'El estado debe ser: pending, accepted, rejected o withdrawn.',
            'min_estimated_time.min' => 'El tiempo estimado mínimo debe ser mayor a 0.',
            'max_estimated_time.gte' => 'El tiempo estimado máximo debe ser mayor o igual al mínimo.',
            'date_to.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
            'provider_rating_min.between' => 'La calificación mínima del proveedor debe estar entre 0 y 5.',
            'provider_rating_max.between' => 'La calificación máxima del proveedor debe estar entre 0 y 5.',
            'provider_rating_max.gte' => 'La calificación máxima debe ser mayor o igual a la mínima.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'radius.between' => 'El radio debe estar entre 1 y 100 km.',
            'sort_by.in' => 'El ordenamiento debe ser: relevance, created_at, price, estimated_time, provider_rating o distance.',
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
            'min_price' => 'precio mínimo',
            'max_price' => 'precio máximo',
            'status' => 'estado',
            'min_estimated_time' => 'tiempo estimado mínimo',
            'max_estimated_time' => 'tiempo estimado máximo',
            'date_from' => 'fecha inicial',
            'date_to' => 'fecha final',
            'provider_rating_min' => 'calificación mínima del proveedor',
            'provider_rating_max' => 'calificación máxima del proveedor',
            'has_message' => 'tiene mensaje',
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