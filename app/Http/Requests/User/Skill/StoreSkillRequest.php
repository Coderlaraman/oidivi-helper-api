<?php

namespace App\Http\Requests\User\Skill;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:skills,name',
            'description' => 'required|string|max:1000',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'experience_level' => 'required|integer|between:1,5',
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required|string|max:255',
            'certifications.*.issuer' => 'required|string|max:255',
            'certifications.*.date' => 'required|date',
            'certifications.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la habilidad es requerido.',
            'name.unique' => 'Esta habilidad ya existe.',
            'description.required' => 'La descripción es requerida.',
            'category_ids.required' => 'Debes seleccionar al menos una categoría.',
            'category_ids.*.exists' => 'Una de las categorías seleccionadas no existe.',
            'experience_level.required' => 'El nivel de experiencia es requerido.',
            'experience_level.between' => 'El nivel de experiencia debe estar entre 1 y 5.',
            'certifications.*.name.required' => 'El nombre de la certificación es requerido.',
            'certifications.*.issuer.required' => 'El emisor de la certificación es requerido.',
            'certifications.*.date.required' => 'La fecha de la certificación es requerida.',
            'certifications.*.file.mimes' => 'El archivo de certificación debe ser PDF o imagen.',
            'certifications.*.file.max' => 'El archivo de certificación no puede exceder 2MB.'
        ];
    }
}
