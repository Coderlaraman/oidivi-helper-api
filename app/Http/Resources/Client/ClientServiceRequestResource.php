<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientServiceRequestResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'budget' => $this->budget,
            'visibility' => $this->visibility,
            'status' => $this->status,
            'user' => new ClientUserProfileResource($this->whenLoaded('user')),  // Se asume la existencia de UserResource
            'categories' => ClientCategoryResource::collection($this->whenLoaded('categories')),  // Se asume CategoryResource
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
