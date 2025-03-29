<?php

namespace App\Http\Requests\User\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientServiceRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Se validará en el controlador que el usuario sea el propietario.
        return auth()->check();
    }

    /**
     * Reglas de validación para actualizar una oferta de servicio.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'zip_code' => 'sometimes|required|string|max:10',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'budget' => 'sometimes|required|numeric|min:0',
            'visibility' => 'sometimes|required|in:public,private',
            'category_ids' => 'sometimes|required|array',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio cuando se actualiza la oferta.',
            'description.required' => 'La descripción es obligatoria cuando se actualiza la oferta.',
            'zip_code.required' => 'El código postal es obligatorio cuando se actualiza la oferta.',
            'latitude.required' => 'La latitud es obligatoria cuando se actualiza la oferta.',
            'longitude.required' => 'La longitud es obligatoria cuando se actualiza la oferta.',
            'budget.required' => 'El presupuesto es obligatorio cuando se actualiza la oferta.',
            'visibility.required' => 'La visibilidad es obligatoria cuando se actualiza la oferta.',
            'category_ids.required' => 'Debes seleccionar al menos una categoría cuando se actualiza la oferta.',
            'category_ids.*.exists' => 'La categoría seleccionada no es válida.',
        ];
    }
}
