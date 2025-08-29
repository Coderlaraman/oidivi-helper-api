<?php

namespace App\Http\Requests\User\ServiceRequest;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

class SubmitCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $serviceRequestId = $this->route('id');
        /** @var ServiceRequest|null $serviceRequest */
        $serviceRequest = ServiceRequest::find($serviceRequestId);
        if (!$serviceRequest) {
            return false;
        }
        return auth()->check() && $serviceRequest->assigned_helper_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'completion_notes' => ['required', 'string', 'max:2000'],
            'completion_evidence' => ['sometimes', 'array'],
            'completion_evidence.*' => ['file', 'mimes:jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'completion_notes.required' => 'Please provide completion notes describing the deliverables.',
            'completion_evidence.*.mimes' => 'Each evidence file must be a jpeg, png, or pdf.',
            'completion_evidence.*.max' => 'Each evidence file must be at most 5MB.',
        ];
    }
}