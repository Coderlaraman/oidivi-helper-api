<?php

namespace App\Http\Resources\User;

use App\Http\Resources\User\UserProfileResource;
use App\Models\ServiceRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin ServiceRequest
 */
class UserServiceRequestResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'distance' => $this->when(isset($this->distance), function () {
                    return round($this->distance, 2);
                }),
            ],
            'budget' => [
                'amount' => $this->budget,
                'formatted' => number_format($this->budget, 2),
            ],
            'visibility' => [
                'code' => $this->visibility,
                'text' => $this->visibility_text,
            ],
            'status' => [
                'code' => $this->status,
                'text' => $this->status_text,
            ],
            'priority' => [
                'code' => $this->priority,
                'text' => $this->priority_text,
            ],
            'payment_method' => [
                'code' => $this->payment_method,
                'text' => $this->payment_method_text,
            ],
            'service_type' => [
                'code' => $this->service_type,
                'text' => $this->service_type_text,
            ],
            'dates' => [
                'due_date' => $this->due_date?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
                'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
                'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
                'client_confirmed_at' => $this->client_confirmed_at?->format('Y-m-d H:i:s'),
                'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            ],
            'flags' => [
                'is_overdue' => $this->isOverdue(),
                'is_published' => $this->isPublished(),
                'is_in_progress' => $this->isInProgress(),
                'is_completed' => $this->isCompleted(),
                'is_canceled' => $this->isCanceled(),
                'is_urgent' => $this->isUrgent(),
                'is_owner' => auth()->id() === $this->user_id,
            ],
            'metadata' => [
                'completion_notes' => $this->metadata['completion_notes'] ?? null,
                'client_completion_notes' => $this->metadata['client_completion_notes'] ?? null,
                'change_reason' => $this->metadata['change_reason'] ?? null,
                'completion_evidence' => collect($this->metadata['completion_evidence'] ?? [])->map(function ($path) {
                    return Storage::url($path);
                })->toArray(),
                'cancellation_reason' => $this->metadata['cancellation_reason'] ?? null,
                'additional_data' => collect($this->metadata)
                    ->except(['completion_notes', 'client_completion_notes', 'change_reason', 'completion_evidence', 'cancellation_reason'])
                    ->toArray(),
            ],
            'relationships' => [
                'categories' => $this->whenLoaded('categories', function () {
                    return $this->categories->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'description' => $category->description,
                            'created_at' => $category->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $category->updated_at->format('Y-m-d H:i:s'),
                        ];
                    });
                }),
                'user' => new UserProfileResource($this->whenLoaded('user')),
                'offers' => $this->whenLoaded('offers'),
                'offers_count' => $this->whenLoaded('offers', function () {
                    return $this->offers->count();
                }),
            ],
            'permissions' => [
                'can_edit' => auth()->id() === $this->user_id &&
                    in_array($this->status, ['published', 'in_progress']),
                'can_delete' => auth()->id() === $this->user_id &&
                    $this->status === 'published' &&
                    ($this->offers_count ?? 0) === 0,
                'can_make_offer' => auth()->id() !== $this->user_id &&
                    $this->status === 'published',
                'can_cancel' => auth()->id() === $this->user_id &&
                    !in_array($this->status, ['completed', 'canceled']),
                // workflow permissions
                'can_submit_completion' => auth()->id() === $this->assigned_helper_id && $this->isInProgress() && !$this->submitted_at,
                'can_upload_deliverable' => auth()->id() === $this->assigned_helper_id && $this->isInProgress(),
                'can_confirm_completion' => auth()->id() === $this->user_id && $this->isInProgress() && (bool) $this->submitted_at,
                'can_request_changes' => auth()->id() === $this->user_id && $this->isInProgress() && (bool) $this->submitted_at,
            ],
        ];
    }
}
