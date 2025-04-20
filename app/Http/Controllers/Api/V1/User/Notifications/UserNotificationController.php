<?php

namespace App\Http\Controllers\Api\V1\User\Notifications;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserNotificationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $notifications = PushNotification::where('user_id', auth()->id())
                ->with(['serviceRequest'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $unreadCount = PushNotification::where('user_id', auth()->id())
                ->unread()
                ->count();

            return $this->successResponse(
                data: [
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount
                ],
                message: 'Notifications retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return $this->errorResponse(
                message: 'Error retrieving notifications',
                statusCode: 500
            );
        }
    }

    public function markAsRead(Request $request, PushNotification $notification): JsonResponse
    {
        try {
            if ($notification->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'Unauthorized',
                    statusCode: 403
                );
            }

            $notification->update(['read_at' => now()]);

            return $this->successResponse(
                data: $notification,
                message: 'Notification marked as read'
            );
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'error' => $e->getMessage(),
                'notification_id' => $notification->id
            ]);
            return $this->errorResponse(
                message: 'Error marking notification as read',
                statusCode: 500
            );
        }
    }

    public function markAllAsRead(): JsonResponse
    {
        try {
            PushNotification::where('user_id', auth()->id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return $this->successResponse(
                message: 'All notifications marked as read'
            );
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return $this->errorResponse(
                message: 'Error marking all notifications as read',
                statusCode: 500
            );
        }
    }

    public function getUnreadCount(): JsonResponse
    {
        try {
            $count = PushNotification::where('user_id', auth()->id())
                ->unread()
                ->count();

            return $this->successResponse(
                data: ['unread_count' => $count],
                message: 'Unread count retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error('Error getting unread count', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return $this->errorResponse(
                message: 'Error getting unread count',
                statusCode: 500
            );
        }
    }
} 