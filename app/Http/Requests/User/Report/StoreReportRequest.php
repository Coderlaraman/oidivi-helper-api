<?php

namespace App\Http\Requests\User\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reported_user' => 'nullable|exists:users,id|different:user_id',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'type' => 'required|in:fraud,abuse,payment_issue,other',
            'description' => 'required|string|min:20|max:1000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpeg,png,pdf,mp4|max:10240',
            'urgency_level' => 'nullable|in:low,medium,high'
        ];
    }

    public function messages(): array
    {
        return [
            'reported_user.exists' => 'The reported user does not exist.',
            'reported_user.different' => 'You cannot report yourself.',
            'service_request_id.exists' => 'The service request does not exist.',
            'transaction_id.exists' => 'The transaction does not exist.',
            'type.required' => 'The report type is required.',
            'type.in' => 'The report type is invalid.',
            'description.required' => 'The description is required.',
            'description.min' => 'The description must be at least 20 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'evidence.*.file' => 'The evidence must be a valid file.',
            'evidence.*.mimes' => 'The evidence must be an image, PDF or video.',
            'evidence.*.max' => 'The evidence cannot exceed 10MB.',
            'urgency_level.in' => 'The urgency level is invalid.'
        ];
    }
}
