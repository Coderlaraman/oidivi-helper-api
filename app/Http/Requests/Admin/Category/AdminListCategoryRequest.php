<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminListCategoryRequest extends FormRequest
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
        return [
            'search' => 'nullable|string|max:100',
            'parent_only' => 'nullable|boolean',
            'active_only' => 'nullable|boolean',
            'with_children' => 'nullable|boolean',
            'with_skills' => 'nullable|boolean',
            'with_service_requests' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:name,created_at,updated_at,sort_order',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'show_deleted' => 'nullable|boolean',
            'show_inactive' => 'nullable|boolean',
            '_no_cache' => 'nullable|boolean',
        ];
    }
}
