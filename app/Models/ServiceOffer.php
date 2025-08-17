<?php

namespace App\Models;

use App\Constants\NotificationType;
use App\Events\NewServiceOfferNotification;
use App\Events\ServiceOfferStatusUpdatedNotification;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class ServiceOffer
 *
 * Modelo que representa una oferta realizada sobre una solicitud de servicio.
 *
 * @property int $id
 * @property int $service_request_id
 * @property int $user_id
 * @property float $price_proposed
 * @property string $estimated_time
 * @property string $message
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ServiceRequest|null $serviceRequest
 * @property-read User|null $user
 * @property-read Payment|null $payment
 * @property-read \Illuminate\Database\Eloquent\Collection|Chat[] $chats
 * @method \Illuminate\Database\Eloquent\Relations\HasOne payment()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo serviceRequest()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo user()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany chats()
 */
class ServiceOffer extends Model
{
    use HasFactory, Notifiable;

    /** Estado: Pendiente */
    public const STATUS_PENDING = 'pending';
    /** Estado: Aceptada */
    public const STATUS_ACCEPTED = 'accepted';
    /** Estado: Rechazada */
    public const STATUS_REJECTED = 'rejected';

    /**
     * Lista de todos los estados válidos para una oferta.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'service_request_id',
        'user_id',
        'price_proposed',
        'estimated_time',
        'message',
        'status'
    ];

    /**
     * Relación: Solicitud de servicio asociada a la oferta.
     *
     * @return BelongsTo
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relación: Usuario que realizó la oferta.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Relación: Chats asociados a la oferta.
     *
     * @return HasMany
     */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Verifica si un usuario dado es participante del chat asociado a esta oferta.
     * Un participante es el creador de la solicitud o el ofertante.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function isParticipant(User $user): bool
    {
        $this->loadMissing('serviceRequest');
        if (!$this->serviceRequest) {
            return false;
        }
        return (int) $user->id === (int) $this->serviceRequest->user_id || (int) $user->id === (int) $this->user_id;
    }

    /**
     * Notifica al usuario ofertante sobre un cambio de estado en la oferta.
     *
     * @return void
     */
    public function notifyStatusUpdate(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            $status = $this->status ?? '';
            $this->createNotification(
                userIds: [$this->user_id],
                type: NotificationType::OFFER_STATUS_UPDATED,
                title: __('notifications.types.offer_status_updated'),
                message: __('messages.service_offers.notifications.status_update_message', [
                    'title' => $title,
                    'status' => $status
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
     * Notifica al ofertante que su oferta fue aceptada.
     *
     * @return void
     */
    public function notifyOfferAccepted(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            $this->createNotification(
                userIds: [$this->user_id],
                type: NotificationType::OFFER_ACCEPTED,
                title: __('notifications.types.offer_accepted'),
                message: __('messages.offer_accepted_message', [
                    'title' => $title
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
     *
     * @return void
     */
    public function notifyRequestOwner(): void
    {
        try {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications */
            $notifications = $this->createNotification(
                userIds: [$this->serviceRequest?->user_id],
                type: NotificationType::NEW_OFFER,
                title: __('notifications.types.new_offer'),
                message: __('messages.service_offers.notifications.new_offer_message', [
                    'title' => $this->serviceRequest?->title ?? ''
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
                        'created_at' => $this->created_at?->toIso8601String()
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
                        $notification->user_id
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
     * Relación: Pago asociado a esta oferta.
     *
     * @return HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Genera la URL de acción para notificaciones según el tipo.
     *
     * @param string $type Tipo de notificación.
     * @param int $serviceRequestId ID de la solicitud de servicio.
     * @param int|null $offerId ID de la oferta (opcional).
     * @return string
     */
    public static function getNotificationActionUrl(string $type, int $serviceRequestId, ?int $offerId = null): string
    {
        if ($type === NotificationType::NEW_OFFER || $type === NotificationType::OFFER_STATUS_UPDATED || $type === NotificationType::OFFER_ACCEPTED) {
            return "/my-service-requests/{$serviceRequestId}/offers/{$offerId}";
        }
        return "/service-requests/{$serviceRequestId}";
    }
}
