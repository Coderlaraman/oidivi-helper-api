<?php

namespace App\Models;

use App\Constants\NotificationType;
use App\Events\NewServiceOfferNotification;
use App\Events\ServiceOfferStatusUpdatedNotification;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ServiceOffer extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'price_proposed',
        'estimated_time',
        'message',
        'status'
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    /**
     * Get the chats associated with the service offer.
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function notifyStatusUpdate(): void
    {
        try {
            $this->createNotification(
                userIds: [$this->user_id],
                type: NotificationType::OFFER_STATUS_UPDATED,
                title: __('notifications.types.offer_status_updated'),
                message: __('messages.service_offers.notifications.status_update_message', [
                    'title' => $this->serviceRequest->title,
                    'status' => $this->status
                ])
            );

            event(new ServiceOfferStatusUpdatedNotification($this, $this->user_id));
        } catch (\Exception $e) {
            Log::error('Error notifying offer status update', [
                'error' => $e->getMessage(),
                'offer_id' => $this->id
            ]);
        }
    }

    /**
     * Notifica al dueño de la solicitud que se ha recibido una nueva oferta.
     */
    public function notifyOfferAccepted(): void
    {
        try {
            $this->createNotification(
                userIds: [$this->user_id],
                type: NotificationType::OFFER_ACCEPTED,
                title: __('notifications.types.offer_accepted'),
                message: __('messages.service_offers.notifications.offer_accepted_message', [
                    'title' => $this->serviceRequest->title
                ])
            );

            event(new ServiceOfferStatusUpdatedNotification($this, $this->user_id));
        } catch (\Exception $e) {
            Log::error('Error notifying offer accepted', [
                'error' => $e->getMessage(),
                'offer_id' => $this->id
            ]);
        }
    }

    /**
     * Notifica al dueño de la solicitud que se ha recibido una nueva oferta.
     */
    public function notifyRequestOwner(): void
    {
        try {
            $notifications = $this->createNotification(
                userIds: [$this->serviceRequest->user_id],
                type: NotificationType::NEW_OFFER,
                title: __('notifications.types.new_offer'),
                message: __('messages.service_offers.notifications.new_offer_message', [
                    'title' => $this->serviceRequest->title
                ])
            );

            foreach ($notifications as $notification) {
                $notificationData = [
                    'id' => $notification->id,
                    'type' => NotificationType::NEW_OFFER,
                    'timestamp' => now()->toIso8601String(),
                    'is_read' => false,
                    'service_offer' => [
                        'id' => $this->id,
                        'service_request_id' => $this->service_request_id,
                        'price_proposed' => $this->price_proposed,
                        'estimated_time' => $this->estimated_time,
                        'message' => $this->message,
                        'status' => $this->status,
                        'created_at' => $this->created_at->toIso8601String()
                    ],
                    'notification' => [
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'action_url' => self::getNotificationActionUrl(
                            NotificationType::NEW_OFFER,
                            $this->service_request_id,
                            $this->id
                        )
                    ]
                ];

                try {
                    event(new NewServiceOfferNotification(
                        $this,
                        $notification->user_id,
                        $notificationData
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending offer notification event', [
                        'error' => $e->getMessage(),
                        'notification_id' => $notification->id,
                        'user_id' => $notification->user_id,
                        'service_offer_id' => $this->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error notifying request owner about new offer', [
                'error' => $e->getMessage(),
                'service_offer_id' => $this->id
            ]);
        }
    }

    /**
     * Genera la URL de acción para notificaciones según el tipo.
     */
    public static function getNotificationActionUrl($type, $serviceRequestId, $offerId = null)
    {
        if ($type === NotificationType::NEW_OFFER || $type === NotificationType::OFFER_STATUS_UPDATED || $type === NotificationType::OFFER_ACCEPTED) {
            return "/service-requests/my/{$serviceRequestId}/offers/{$offerId}";
        }
        // Para solicitudes de servicio
        return "/service-requests/{$serviceRequestId}";
    }
}
