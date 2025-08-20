<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class UserSearchRequest extends FormRequest
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
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'max_rating' => 'nullable|numeric|min:0|max:5|gte:min_rating',
            'verification_status' => 'nullable|string|in:verified,unverified,all',
            'role' => 'nullable|string|in:client,provider,all',
            'is_active' => 'nullable|boolean',
            'has_profile_photo' => 'nullable|boolean',
            'has_profile_video' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100',
            'sort_by' => 'nullable|string|in:relevance,rating,name,created_at,last_active',
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
            'skills.*.exists' => 'Una o más habilidades seleccionadas no existen.',
            'categories.*.exists' => 'Una o más categorías seleccionadas no existen.',
            'min_rating.between' => 'La calificación mínima debe estar entre 0 y 5.',
            'max_rating.between' => 'La calificación máxima debe estar entre 0 y 5.',
            'max_rating.gte' => 'La calificación máxima debe ser mayor o igual a la mínima.',
            'verification_status.in' => 'El estado de verificación debe ser: verified, unverified o all.',
            'role.in' => 'El rol debe ser: client, provider o all.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'radius.between' => 'El radio debe estar entre 1 y 100 km.',
            'sort_by.in' => 'El ordenamiento debe ser: relevance, rating, name, created_at o last_active.',
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
            'skills' => 'habilidades',
            'categories' => 'categorías',
            'min_rating' => 'calificación mínima',
            'max_rating' => 'calificación máxima',
            'verification_status' => 'estado de verificación',
            'role' => 'rol',
            'is_active' => 'usuario activo',
            'has_profile_photo' => 'tiene foto de perfil',
            'has_profile_video' => 'tiene video de perfil',
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