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
            'skill_ids.required' => 'You must provide at least one skill.',
            'skill_ids.array' => 'The skills format is invalid.',
            'skill_ids.min' => 'You must select at least one skill.',
            'skill_ids.*.required' => 'Each skill must be valid.',
            'skill_ids.*.integer' => 'The skill ID must be a number.',
            'skill_ids.*.exists' => 'One or more selected skills do not exist.',
        ];
    }
} 