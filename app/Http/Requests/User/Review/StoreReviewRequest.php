<?php

namespace App\Http\Requests\User\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reviewed_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([auth()->id()]),
                Rule::unique('reviews')->where(function ($query) {
                    return $query->where('reviewer_id', auth()->id())
                                ->where('service_request_id', $this->service_request_id);
                })
            ],
            'service_request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|min:10|max:500',
            'aspects' => 'nullable|array',
            'aspects.*' => 'required|in:punctuality,professionalism,quality,communication',
            'aspects_ratings' => 'nullable|array',
            'aspects_ratings.*' => 'required|integer|between:1,5',
            'would_recommend' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'reviewed_id.required' => 'The user to rate is required.',
            'reviewed_id.exists' => 'The user to rate does not exist.',
            'reviewed_id.not_in' => 'You cannot rate yourself.',
            'reviewed_id.unique' => 'You have already rated this user for this service.',
            'service_request_id.required' => 'The service request is required.',
            'service_request_id.exists' => 'The service request does not exist.',
            'rating.required' => 'The rating is required.',
            'rating.between' => 'The rating must be between 1 and 5.',
            'comment.required' => 'The comment is required.',
            'comment.min' => 'The comment must be at least 10 characters.',
            'comment.max' => 'The comment cannot exceed 500 characters.',
            'aspects.*.in' => 'The selected aspect is invalid.',
            'aspects_ratings.*.between' => 'The aspect rating must be between 1 and 5.',
            'would_recommend.required' => 'You must indicate if you would recommend the user.'
        ];
    }
}
