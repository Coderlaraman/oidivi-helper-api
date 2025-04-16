<?php

namespace App\Http\Requests\User\Skill;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('skills')->ignore($this->skill)
            ],
            'description' => 'sometimes|required|string|max:1000',
            'category_ids' => 'sometimes|required|array',
            'category_ids.*' => 'exists:categories,id',
            'experience_level' => 'sometimes|required|integer|between:1,5',
            'is_active' => 'sometimes|required|boolean',
            'certifications' => 'nullable|array',
            'certifications.*.id' => 'sometimes|exists:skill_certifications,id',
            'certifications.*.name' => 'required|string|max:255',
            'certifications.*.issuer' => 'required|string|max:255',
            'certifications.*.date' => 'required|date',
            'certifications.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The skill name is required.',
            'name.unique' => 'This skill already exists.',
            'description.required' => 'The description is required.',
            'category_ids.required' => 'You must select at least one category.',
            'category_ids.*.exists' => 'One of the selected categories does not exist.',
            'experience_level.required' => 'The experience level is required.',
            'experience_level.between' => 'The experience level must be between 1 and 5.',
            'certifications.*.name.required' => 'The certification name is required.',
            'certifications.*.issuer.required' => 'The certification issuer is required.',
            'certifications.*.date.required' => 'The certification date is required.',
            'certifications.*.file.mimes' => 'The certification file must be a PDF or image.',
            'certifications.*.file.max' => 'The certification file cannot exceed 2MB.'
        ];
    }
}
