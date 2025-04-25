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

    protected function createNotification(array $userIds, string $type, string $title, string $message): array
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
            ]);

            $this->notifications()->attach($notification->id);
            $notifications[] = $notification;
        }

        return $notifications;
    }
}