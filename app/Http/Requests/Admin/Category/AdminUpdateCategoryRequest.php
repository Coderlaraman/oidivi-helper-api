<?php

namespace App\Http\Requests\Admin\Category;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($categoryId)
            ],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->whereNull('deleted_at')
                    ->ignore($categoryId)
            ],
            'description' => 'sometimes|string',
            'icon' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
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
            'name.required' => 'The name is required.',
            'name.unique' => 'A category with this name already exists.',
            'slug.required' => 'The slug is required.',
            'slug.unique' => 'A category with this slug already exists.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ];
    }
}
