<?php

namespace App\Http\Requests\Client\Category;

use Illuminate\Foundation\Http\FormRequest;

class ListCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'parent_only' => 'nullable|boolean',
            'active_only' => 'nullable|boolean',
            'with_children' => 'nullable|boolean',
            'with_skills' => 'nullable|boolean',
            'with_service_requests' => 'nullable|boolean',
        ];
    }
} 