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
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id,deleted_at,NULL'],
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
            'title.required' => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'priority.required' => 'La prioridad es obligatoria.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'due_date.date' => 'La fecha de vencimiento no es válida.',
            'due_date.after' => 'La fecha de vencimiento debe ser posterior a la fecha actual.',
        ];
    }
} 