<?php

namespace App\Models;

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

    public function createNotification()
    {
        $notification = new NewServiceOfferNotification($this);
        $this->serviceRequest->user->notify($notification);
        
        event(new ServiceOfferCreated($notification, $this->serviceRequest->user));
        
        return $notification;
    }
}
