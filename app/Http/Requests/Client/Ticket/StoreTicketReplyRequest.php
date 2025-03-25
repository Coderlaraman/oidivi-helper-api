<?php

namespace App\Http\Requests\Client\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && 
               $this->ticket->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|min:10|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje es requerido.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'message.max' => 'El mensaje no puede exceder los 1000 caracteres.',
            'attachments.*.file' => 'El archivo adjunto debe ser válido.',
            'attachments.*.mimes' => 'El archivo adjunto debe ser PDF o imagen.',
            'attachments.*.max' => 'El archivo adjunto no puede exceder 2MB.'
        ];
    }
} 