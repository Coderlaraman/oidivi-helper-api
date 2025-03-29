<?php

namespace App\Http\Requests\User\Location;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'accuracy' => 'nullable|numeric|min:0',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360'
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Latitude is required.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.required' => 'Longitude is required.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'service_request_id.exists' => 'Invalid service request.',
            'accuracy.min' => 'Accuracy cannot be negative.',
            'speed.min' => 'Speed cannot be negative.',
            'heading.between' => 'Heading must be between 0 and 360 degrees.'
        ];
    }
}
