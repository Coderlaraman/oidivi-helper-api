<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientTransactionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // La autorización específica se validará en el controlador (por ejemplo, rol helper).
        return auth()->check();
    }

    /**
     * Reglas de validación para registrar una transacción (contratación).
     */
    public function rules(): array
    {
        return [
            'proposed_price' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'proposed_price.required' => 'El precio propuesto es obligatorio.',
            'proposed_price.numeric' => 'El precio propuesto debe ser un valor numérico.',
            'proposed_price.min' => 'El precio propuesto no puede ser negativo.',
            'message.max' => 'El mensaje no debe exceder 1000 caracteres.',
        ];
    }
}
