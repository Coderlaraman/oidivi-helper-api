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
            'is_active' => (bool) $this->is_active,
            'parent_id' => $this->parent_id,
            'path' => $this->path,
            'full_path' => $this->full_path,
            'parent' => $this->loadRelation('parent', ClientCategoryResource::class),
            'children' => $this->loadCollection('children', ClientCategoryResource::class),
            'children_count' => $this->countRelation('children'),
            'skills' => $this->loadCollection('skills', ClientSkillResource::class),
            'skills_count' => $this->countRelation('skills'),
            'service_requests' => $this->loadCollection('serviceRequests', ClientServiceRequestResource::class),
            'service_requests_count' => $this->countRelation('serviceRequests'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /**
     * Helper function to load a single relation if available.
     *
     * @param string $relation
     * @param string $resourceClass
     * @return mixed
     */
    private function loadRelation(string $relation, string $resourceClass)
    {
        return $this->whenLoaded($relation, fn() => new $resourceClass($this->$relation));
    }

    /**
     * Helper function to load a collection relation if available.
     *
     * @param string $relation
     * @param string $resourceClass
     * @return mixed
     */
    private function loadCollection(string $relation, string $resourceClass)
    {
        return $this->whenLoaded($relation, fn() => $resourceClass::collection($this->$relation));
    }

    /**
     * Helper function to count related items if available.
     *
     * @param string $relation
     * @return int
     */
    private function countRelation(string $relation): int
    {
        return $this->whenLoaded($relation, fn() => $this->$relation->count(), 0);
    }
}