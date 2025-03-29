<?php

namespace App\Http\Requests\Client\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($this->category)
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn([$this->category->id]) // Evita que una categoría sea su propio padre
            ],
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:99999'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.unique' => 'Este nombre de categoría ya existe.',
            'name.max' => 'El nombre de la categoría no puede exceder los 255 caracteres.',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
            'parent_id.not_in' => 'Una categoría no puede ser su propio padre.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
            'sort_order.min' => 'El orden mínimo es 0.',
            'sort_order.max' => 'El orden máximo es 99999.'
        ];
    }
    
    // Normalizar parámetros
    protected function prepareForValidation()
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->is_active === '1' || $this->is_active === 'true' || $this->is_active === true
            ]);
        }
    }
}