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
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed,cancelled'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id,deleted_at,NULL'],
            'user_id' => ['nullable', 'integer', 'exists:users,id,deleted_at,NULL'],
            'show_deleted' => ['nullable', 'boolean'],
            'with_category' => ['nullable', 'boolean'],
            'with_user' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', 'in:title,created_at,updated_at,status,priority'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
