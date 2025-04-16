<?php

namespace App\Http\Requests\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
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
            'name.required' => 'The name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.required' => 'The phone is required.',
            'address.required' => 'The address is required.',
            'zip_code.required' => 'The zip code is required.',
            'latitude.required' => 'The latitude is required.',
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',
            'longitude.required' => 'The longitude is required.',
            'longitude.between' => 'The longitude must be between -180 and 180 degrees.',
            'biography.max' => 'The biography cannot exceed 1000 characters.',
            'verification_documents.*.file' => 'The document must be a valid file.',
            'verification_documents.*.mimes' => 'The document must be a PDF, JPG, JPEG or PNG file.',
            'verification_documents.*.max' => 'The document cannot exceed 2MB.'
        ];
    }
}
