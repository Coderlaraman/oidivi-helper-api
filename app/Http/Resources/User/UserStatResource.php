<?php
// app/Http/Resources/UserStatResource.php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'completed_tasks' => $this->completed_tasks,
            'active_services' => $this->active_services,
            'total_earnings' => $this->total_earnings,
            'rating' => $this->rating,
        ];
    }
}
