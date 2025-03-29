<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserVerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:users,id',
            'hash' => 'required|string'
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'User ID is required.',
            'id.exists' => 'Invalid user ID provided.',
            'hash.required' => 'Verification hash is required.',
            'hash.string' => 'Invalid verification hash format.'
        ];
    }
}
