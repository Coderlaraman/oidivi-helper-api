<?php

namespace App\Http\Requests\Admin\Category;

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
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($categoryId)
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->whereNull('deleted_at')
                    ->ignore($categoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value !== null) {
                        // Verificar que no se asigne a sí mismo como padre
                        if ($value == $categoryId) {
                            $fail('Una categoría no puede ser su propio padre.');
                            return;
                        }

                        // Verificar que no se asigne un hijo como padre
                        $category = \App\Models\Category::find($categoryId);
                        if ($category && $category->isParentOf($value)) {
                            $fail('No se puede asignar un hijo como padre (crearía un ciclo).');
                            return;
                        }

                        // Verificar que el padre esté activo
                        $parent = \App\Models\Category::find($value);
                        if ($parent && !$parent->is_active) {
                            $fail('No se puede asignar una categoría inactiva como padre.');
                        }
                    }
                }
            ],
            'is_active' => [
                'sometimes',
                'boolean',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value === false) {
                        $category = \App\Models\Category::find($categoryId);
                        if ($category && $category->hasActiveChildren()) {
                            $fail('No se puede desactivar una categoría con hijos activos.');
                        }
                    }
                }
            ],
            'sort_order' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
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
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.unique' => 'Ya existe una categoría con este nombre.',
            'slug.required' => 'El slug de la categoría es obligatorio.',
            'slug.unique' => 'Ya existe una categoría con este slug.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
        ];
    }
}
