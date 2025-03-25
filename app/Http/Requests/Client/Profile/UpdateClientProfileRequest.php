<?php

namespace App\Http\Requests\Client\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore(auth()->id())
            ],
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string|max:255',
            'zip_code' => 'sometimes|required|string|max:10',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'biography' => 'nullable|string|max:1000',
            'verification_documents' => 'nullable|array',
            'verification_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'phone.required' => 'El teléfono es requerido.',
            'address.required' => 'La dirección es requerida.',
            'zip_code.required' => 'El código postal es requerido.',
            'latitude.required' => 'La latitud es requerida.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90 grados.',
            'longitude.required' => 'La longitud es requerida.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180 grados.',
            'biography.max' => 'La biografía no puede exceder los 1000 caracteres.',
            'verification_documents.*.file' => 'El documento debe ser un archivo válido.',
            'verification_documents.*.mimes' => 'El documento debe ser un archivo PDF, JPG, JPEG o PNG.',
            'verification_documents.*.max' => 'El documento no puede exceder 2MB.'
        ];
    }
} 