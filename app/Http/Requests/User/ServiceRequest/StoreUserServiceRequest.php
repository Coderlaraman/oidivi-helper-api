<?php

namespace App\Http\Requests\User\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ServiceRequest;
use Illuminate\Validation\Rule;

class StoreUserServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Asumimos que el usuario debe estar autenticado para crear una oferta.
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\_\.\,\&]+$/',
                function ($attribute, $value, $fail) {
                    if (ServiceRequest::titleExistsForOtherUser($value)) {
                        $fail('This title is already in use. Please choose a unique title for your service request.');
                    }
                }
            ],
            'description' => 'required|string|min:20',
            'address' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'budget' => 'required|numeric|min:0|max:999999.99',
            'visibility' => ['required', Rule::in(array_keys(ServiceRequest::VISIBILITY))],
            'payment_method' => ['nullable', Rule::in(array_keys(ServiceRequest::PAYMENT_METHODS))],
            'service_type' => ['required', Rule::in(array_keys(ServiceRequest::SERVICE_TYPES))],
            'priority' => ['required', Rule::in(array_keys(ServiceRequest::PRIORITIES))],
            'due_date' => 'nullable|date|after:now',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The service request title is required.',
            'title.regex' => 'The title can only contain letters, numbers, spaces and basic punctuation.',
            'description.required' => 'A detailed description of the service request is required.',
            'description.min' => 'The description must be at least 20 characters long.',
            'address.required' => 'The service location address is required.',
            'zip_code.required' => 'The ZIP code for the service location is required.',
            'latitude.required' => 'The latitude coordinate is required.',
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',
            'longitude.required' => 'The longitude coordinate is required.',
            'longitude.between' => 'The longitude must be between -180 and 180 degrees.',
            'budget.required' => 'Please specify your budget for this service.',
            'budget.min' => 'The budget cannot be negative.',
            'budget.max' => 'The budget cannot exceed 999,999.99.',
            'visibility.required' => 'Please specify the visibility of your service request.',
            'visibility.in' => 'Invalid visibility option selected.',
            'payment_method.in' => 'Invalid payment method selected.',
            'service_type.required' => 'Please specify the type of service (one-time or recurring).',
            'service_type.in' => 'Invalid service type selected.',
            'priority.required' => 'Please specify the priority level for this service.',
            'priority.in' => 'Invalid priority level selected.',
            'due_date.after' => 'The due date must be in the future.',
            'category_ids.required' => 'Please select at least one category for your service request.',
            'category_ids.min' => 'Please select at least one category for your service request.',
            'category_ids.*.exists' => 'One or more selected categories are invalid.',
        ];
    }
}
