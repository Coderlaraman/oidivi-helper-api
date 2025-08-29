<?php

namespace App\Http\Requests\User\ServiceRequest;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $serviceRequestId = $this->route('id');
        /** @var ServiceRequest|null $serviceRequest */
        $serviceRequest = ServiceRequest::find($serviceRequestId);
        if (!$serviceRequest) {
            return false;
        }
        return auth()->check() && $serviceRequest->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'completion_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'review' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating cannot exceed 5.',
        ];
    }
}