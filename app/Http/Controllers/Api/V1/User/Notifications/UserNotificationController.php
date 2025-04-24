<?php

namespace App\Http\Controllers\Api\V1\User\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\User\UserNotificationResource;

class UserNotificationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $notifications = Notification::where('user_id', auth()->id())
                ->with(['serviceRequest', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $unreadCount = Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count();

            return $this->successResponse(
                data: [
                    'items' => UserNotificationResource::collection($notifications),
                    'meta' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                        'unread_count' => $unreadCount
                    ]
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

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        try {
            if ($notification->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'Unauthorized',
                    statusCode: 403
                );
            }

            $notification->markAsRead();

            return $this->successResponse(
                data: new UserNotificationResource($notification),
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
            Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

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
            $count = Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count();

            return $this->successResponse(
                data: [
                    'meta' => [
                        'unread_count' => $count
                    ]
                ],
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

    /**
     * Elimina una notificación específica.
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function destroy(Notification $notification): JsonResponse
    {
        try {
            // Verificar que la notificación pertenezca al usuario autenticado
            if ($notification->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'You do not have permission to delete this notification',
                    statusCode: 403
                );
            }

            $notification->delete();

            return $this->successResponse(
                message: __('messages.notifications.deleted')
            );
        } catch (\Exception $e) {
            Log::error('Error deleting notification', [
                'error' => $e->getMessage(),
                'notification_id' => $notification->id
            ]);
            return $this->errorResponse(
                message: 'Error deleting notification',
                statusCode: 500
            );
        }
    }
} 