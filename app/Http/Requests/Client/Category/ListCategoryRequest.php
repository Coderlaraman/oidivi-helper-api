<?php

namespace App\Http\Requests\Client\Category;

use Illuminate\Foundation\Http\FormRequest;

class ListCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Abierto para listar
    }

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
            '_no_cache' => 'nullable|boolean'
        ];
    }
    
    // Función para normalizar los parámetros
    protected function prepareForValidation()
    {
        $this->merge([
            'parent_only' => $this->toBoolean($this->parent_only),
            'active_only' => $this->toBoolean($this->active_only),
            'with_children' => $this->toBoolean($this->with_children),
            'with_skills' => $this->toBoolean($this->with_skills),
            'with_service_requests' => $this->toBoolean($this->with_service_requests),
            'show_deleted' => $this->toBoolean($this->show_deleted),
            '_no_cache' => $this->toBoolean($this->_no_cache),
        ]);
    }
    
    // Convertir varios formatos a boolean
    protected function toBoolean($value)
    {
        if ($value === null) {
            return null;
        }
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return $value === '1' || strtolower($value) === 'true';
        }
        
        return (bool) $value;
    }
}