<?php

namespace App\Http\Requests\Admin\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateServiceRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:1000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['sometimes', 'required', 'string', 'in:pending,in_progress,completed,cancelled'],
            'priority' => ['sometimes', 'required', 'string', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date', 'after:now'],
            'metadata' => ['nullable', 'array'],
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
            'title.required' => 'The title is required.',
            'description.required' => 'The description is required.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'status.required' => 'The status is required.',
            'status.in' => 'The selected status is invalid.',
            'priority.required' => 'The priority is required.',
            'priority.in' => 'The selected priority is invalid.',
            'due_date.date' => 'The due date is invalid.',
            'due_date.after' => 'The due date must be after the current date.',
        ];
    }
} 