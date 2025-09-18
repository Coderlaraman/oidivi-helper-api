<?php

namespace App\Traits;

use App\Models\Notification;
use App\Constants\NotificationType;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Notifiable
{
    public function notifications(): MorphToMany
    {
        return $this->morphToMany(Notification::class, 'notifiable');
    }

    public function createNotification(array $userIds, string $type, string $title, string $message, array $data = []): array
    {
        if (!NotificationType::isValid($type)) {
            throw new \InvalidArgumentException("Invalid notification type: {$type}");
        }

        $notifications = [];
        foreach ($userIds as $userId) {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => !empty($data) ? $data : null,
            ]);

            // No need to save to relationship since notification already has user_id
            $notifications[] = $notification;
        }

        return $notifications;
    }
}