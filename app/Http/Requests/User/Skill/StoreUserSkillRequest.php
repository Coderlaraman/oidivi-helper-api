<?php

namespace App\Http\Requests\User\Skill;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserSkillRequest extends FormRequest
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
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => ['required', 'integer', 'exists:skills,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'skill_ids.required' => 'Debes proporcionar al menos una habilidad.',
            'skill_ids.array' => 'El formato de las habilidades no es válido.',
            'skill_ids.min' => 'Debes seleccionar al menos una habilidad.',
            'skill_ids.*.required' => 'Cada habilidad debe ser válida.',
            'skill_ids.*.integer' => 'El ID de la habilidad debe ser un número.',
            'skill_ids.*.exists' => 'Una o más habilidades seleccionadas no existen.',
        ];
    }
} 