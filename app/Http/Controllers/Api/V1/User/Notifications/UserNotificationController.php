<?php

namespace App\Http\Controllers\Api\V1\User\Notifications;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $notifications = PushNotification::where('user_id', auth()->id())
            ->with('serviceRequest')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse($notifications, __('messages.notifications.list_success'));
    }

    public function markAsRead(Request $request, PushNotification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return $this->errorResponse(__('messages.unauthorized'), 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return $this->successResponse($notification, __('messages.notifications.read'));
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        PushNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return $this->successResponse(null, __('messages.notifications.all_read'));
    }
} 