<?php

namespace App\Http\Requests\User\Referral;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'referred_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([auth()->id()]), // No puede referirse a sí mismo
                Rule::unique('referrals', 'referred_id')
                    ->where('referrer_id', auth()->id()) // No puede referir al mismo usuario dos veces
            ],
            'message' => 'nullable|string|max:500',
            'referral_code' => 'nullable|string|max:20'
        ];
    }

    public function messages(): array
    {
        return [
            'referred_id.required' => 'The referred user is required.',
            'referred_id.exists' => 'The referred user does not exist.',
            'referred_id.not_in' => 'You cannot refer to yourself.',
            'referred_id.unique' => 'You have already referred this user.',
            'message.max' => 'The message cannot exceed 500 characters.',
            'referral_code.max' => 'The referral code cannot exceed 20 characters.'
        ];
    }
}
