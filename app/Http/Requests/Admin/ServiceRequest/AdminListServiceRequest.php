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
            'search.max' => 'La búsqueda no puede exceder los 100 caracteres.',
            'status.in' => 'El estado seleccionado no es válido.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'show_deleted.boolean' => 'El campo mostrar eliminados debe ser verdadero o falso.',
            'with_category.boolean' => 'El campo incluir categoría debe ser verdadero o falso.',
            'with_user.boolean' => 'El campo incluir usuario debe ser verdadero o falso.',
            'sort_by.in' => 'El campo de ordenamiento no es válido.',
            'sort_direction.in' => 'La dirección de ordenamiento debe ser ascendente o descendente.',
            'per_page.min' => 'El número de elementos por página debe ser al menos 1.',
            'per_page.max' => 'El número de elementos por página no puede exceder 100.',
            'page.min' => 'El número de página debe ser al menos 1.',
        ];
    }
}
