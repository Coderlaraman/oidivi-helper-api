<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($this->route('user')),
            ],
            'password' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name',
            'is_active' => 'sometimes|boolean',
            'preferred_language' => 'sometimes|string|size:2',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'zip_code' => 'sometimes|string|max:20',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'biography' => 'sometimes|string',
            'profile_photo_url' => 'sometimes|url',
            'profile_video_url' => 'sometimes|url',
            'verification_status' => 'sometimes|string|in:pending,verified,rejected',
            'verification_notes' => 'sometimes|string',
        ];
    }
}