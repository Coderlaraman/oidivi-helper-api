<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class LoginClientAuthRequest extends FormRequest
{
    /**
     * Permite a cualquier usuario realizar esta solicitud de login.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define las reglas de validación para el inicio de sesión.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'The email is required.',
            'email.string' => 'The email must be a string.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
        ];
    }
}
