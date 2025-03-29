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
            'referred_id.required' => 'El usuario referido es requerido.',
            'referred_id.exists' => 'El usuario referido no existe.',
            'referred_id.not_in' => 'No puedes referirte a ti mismo.',
            'referred_id.unique' => 'Ya has referido a este usuario.',
            'message.max' => 'El mensaje no puede exceder los 500 caracteres.',
            'referral_code.max' => 'El código de referido no puede exceder los 20 caracteres.'
        ];
    }
}
