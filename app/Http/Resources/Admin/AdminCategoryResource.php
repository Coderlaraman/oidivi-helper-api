<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            
            // Relaciones administrativas
            'parent' => $this->when($this->relationLoaded('parent'), fn() => new AdminCategoryResource($this->parent)),
            'children' => $this->when($this->relationLoaded('children'), fn() => AdminCategoryResource::collection($this->children)),
            'skills_count' => $this->when($this->skills_count !== null, $this->skills_count),
            'service_requests_count' => $this->when($this->service_requests_count !== null, $this->service_requests_count),
            
            // Datos administrativos adicionales
            'has_related_entities' => $this->when(method_exists($this, 'hasRelatedEntities'), fn() => $this->hasRelatedEntities()),
            'admin_metadata' => [
                'can_be_deleted' => !$this->hasRelatedEntities(),
                'can_be_deactivated' => !$this->hasActiveChildren(),
                'has_active_children' => $this->hasActiveChildren(),
                'has_inactive_children' => $this->hasInactiveChildren(),
            ],
        ];
    }
} 