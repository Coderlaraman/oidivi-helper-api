<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
            'parent' => $this->when($this->relationLoaded('parent'), 
                fn() => new ClientCategoryResource($this->parent)),
            'children' => $this->when($this->relationLoaded('children'), 
                fn() => ClientCategoryResource::collection($this->children)),
            'children_count' => $this->when($this->relationLoaded('children'), 
                fn() => $this->children->count()),
            'skills' => $this->when($this->relationLoaded('skills'), 
                fn() => ClientSkillResource::collection($this->skills)),
            'skills_count' => $this->when($this->relationLoaded('skills'), 
                fn() => $this->skills->count()),
            'service_requests' => $this->when($this->relationLoaded('serviceRequests'), 
                fn() => ClientServiceRequestResource::collection($this->serviceRequests)),
            'service_requests_count' => $this->when($this->relationLoaded('serviceRequests'), 
                fn() => $this->serviceRequests->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
