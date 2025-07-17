<?php

namespace App\Http\Requests\Admin\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class AdminListServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed,canceled'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'show_deleted' => ['nullable', 'boolean'],
            'with_category' => ['nullable', 'boolean'],
            'with_user' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', 'in:title,created_at,updated_at,status,priority'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
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
            'search.max' => 'The search cannot exceed 100 characters.',
            'status.in' => 'The selected status is invalid.',
            'priority.in' => 'The selected priority is invalid.',
            'category_id.exists' => 'The selected category does not exist.',
            'user_id.exists' => 'The selected user does not exist.',
            'show_deleted.boolean' => 'The show deleted field must be true or false.',
            'with_category.boolean' => 'The include category field must be true or false.',
            'with_user.boolean' => 'The include user field must be true or false.',
            'sort_by.in' => 'The sorting field is invalid.',
            'sort_direction.in' => 'The sorting direction must be ascending or descending.',
            'per_page.min' => 'The number of elements per page must be at least 1.',
            'per_page.max' => 'The number of elements per page cannot exceed 100.',
            'page.min' => 'The page number must be at least 1.',
        ];
    }
}
