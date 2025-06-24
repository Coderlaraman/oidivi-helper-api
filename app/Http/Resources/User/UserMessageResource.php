<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // El objeto $this->resource es una instancia del modelo App\Models\Message
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,

            // Carga condicional de la relación para obtener el nombre del remitente
            'sender_name' => $this->whenLoaded('sender', function () {
                return $this->sender->name;
            }),

            'message' => $this->message,
            'type' => $this->type,

            // Asegura que la URL de los medios sea siempre la correcta
            'media_url' => $this->media_url, // Asumiendo que ya guardas la URL completa
            'media_type' => $this->media_type,
            'media_name' => $this->media_name,

            'metadata' => $this->metadata,
            'seen_at' => $this->seen_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
