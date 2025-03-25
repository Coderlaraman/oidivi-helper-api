<?php

namespace App\Http\Requests\Client\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientServiceRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Asumimos que el usuario debe estar autenticado para crear una oferta.
        return auth()->check();
    }

    /**
     * Obtiene las reglas de validación que se aplican a la petición.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'zip_code' => 'required|string|max:10',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'budget' => 'required|numeric|min:0',
            'visibility' => 'required|in:public,private',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'zip_code.required' => 'El código postal es obligatorio.',
            'latitude.required' => 'La latitud es obligatoria.',
            'longitude.required' => 'La longitud es obligatoria.',
            'budget.required' => 'El presupuesto es obligatorio.',
            'visibility.required' => 'La visibilidad es obligatoria.',
            'category_ids.required' => 'Debes seleccionar al menos una categoría.',
        ];
    }
}
