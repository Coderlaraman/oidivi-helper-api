<?php

namespace App\Http\Requests\User\ServiceRequest;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

class RequestChangesRequest extends FormRequest
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
            'change_reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'change_reason.required' => 'Please provide a reason for requesting changes.',
        ];
    }
}