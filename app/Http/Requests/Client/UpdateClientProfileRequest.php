<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateClientProfileRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Se validará en el controlador que el usuario sea el propietario y esté autenticado.
        return Auth::check();
    }

    /**
     * Reglas de validación para actualizar el perfil del usuario.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|required|string|min:7|max:15',
            'address' => 'sometimes|required|string|max:255',
            'zip_code' => 'sometimes|required|string|max:10',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'biography' => 'sometimes|nullable|string|max:1000',
            'verification_documents.*' => 'sometimes|required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio cuando se actualiza el perfil.',
            'email.required' => 'El correo electrónico es obligatorio cuando se actualiza el perfil.',
            'phone.required' => 'El número de teléfono es obligatorio cuando se actualiza el perfil.',
            'address.required' => 'La dirección es obligatoria cuando se actualiza el perfil.',
            'zip_code.required' => 'El código postal es obligatorio cuando se actualiza el perfil.',
            'latitude.required' => 'La latitud es obligatoria cuando se actualiza el perfil.',
            'longitude.required' => 'La longitud es obligatoria cuando se actualiza el perfil.',
            'biography.max' => 'La biografía no puede exceder los 1000 caracteres.',
            'verification_documents.*.file' => 'El documento debe ser un archivo válido.',
            'verification_documents.*.mimes' => 'El documento debe ser un archivo PDF, DOC, DOCX, JPG, JPEG o PNG.',
            'verification_documents.*.max' => 'El documento no puede ser mayor a 5MB.',
        ];
    }
}
