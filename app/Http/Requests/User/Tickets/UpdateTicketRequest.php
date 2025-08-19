<?php

namespace App\Http\Requests\User\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => 'sometimes|string|in:account,payment,technical,other',
            'message' => 'sometimes|string|min:10|max:2000',
            'status' => 'sometimes|string|in:open,in_progress,closed',
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'La categoría debe ser: account, payment, technical, o other.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'message.max' => 'El mensaje no puede exceder los 2000 caracteres.',
            'status.in' => 'El estado debe ser: open, in_progress o closed.',
        ];
    }
}