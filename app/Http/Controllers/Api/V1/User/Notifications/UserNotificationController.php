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

        return $this->successResponse($notifications, 'Notificaciones recuperadas exitosamente');
    }

    public function markAsRead(Request $request, PushNotification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return $this->errorResponse('No autorizado', 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return $this->successResponse($notification, 'Notificación marcada como leída');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        PushNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return $this->successResponse(null, 'Todas las notificaciones marcadas como leídas');
    }
} 