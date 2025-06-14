<?php

namespace App\Models;

use App\Events\NewServiceRequestNotification;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Constants\NotificationType;
use Illuminate\Support\Facades\Log;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'address',
        'zip_code',
        'latitude',
        'longitude',
        'budget',
        'visibility',
        'status',
        'payment_method',
        'service_type',
        'priority',
        'due_date',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * The possible status values for a service request.
     *
     * @var array<string>
     */
    public const STATUSES = [
        'published' => 'Published',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'canceled' => 'Canceled',
    ];

    /**
     * The possible priority values for a service request.
     *
     * @var array<string>
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const VISIBILITY = [
        'public' => 'Public',
        'private' => 'Private',
    ];

    public const PAYMENT_METHODS = [
        'paypal' => 'PayPal',
        'credit_card' => 'Credit Card',
        'bank_transfer' => 'Bank Transfer',
    ];

    public const SERVICE_TYPES = [
        'one_time' => 'One Time',
        'recurring' => 'Recurring',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($serviceRequest) {
            if (empty($serviceRequest->slug)) {
                $serviceRequest->generateUniqueSlug();
            }
        });

        static::updating(function ($serviceRequest) {
            if ($serviceRequest->isDirty('title')) {
                $serviceRequest->generateUniqueSlug();
            }
        });
    }

    /**
     * Genera un slug único basado en el título.
     */
    public function generateUniqueSlug(): void
    {
        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $this->slug = $slug;
    }

    /**
     * Obtener la URL amigable de la solicitud de servicio.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Verificar si el título ya existe para otro usuario.
     */
    public static function titleExistsForOtherUser(string $title, ?int $excludeId = null): bool
    {
        $query = static::where('title', $title);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get the user that owns the service request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    /**
     * Get the priority text.
     */
    public function getPriorityTextAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? 'Unknown';
    }

    /**
     * Check if the service request is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && 
               !in_array($this->status, ['completed', 'canceled']);
    }

    /**
     * Check if the service request can transition to a new status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            'published' => ['in_progress', 'completed', 'canceled'],
            'in_progress' => ['completed', 'canceled'],
            'completed' => [],
            'canceled' => [],
        ];

        return in_array($newStatus, $validTransitions[$this->status] ?? []);
    }

    /**
     * Attach categories to the service request.
     */
    public function attachCategories(array $categoryIds): void
    {
        $this->categories()->attach($categoryIds);
    }

    /**
     * Detach categories from the service request.
     */
    public function detachCategories(array $categoryIds): void
    {
        $this->categories()->detach($categoryIds);
    }

    /**
     * Sync categories for the service request.
     */
    public function syncCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }

    /**
     * Check if the service request is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the service request is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the service request is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the service request is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if the service request is urgent.
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    public function markInProgress(): void
    {
        $this->status = self::STATUSES['in_progress'];
        $this->save();
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')
            ->withTimestamps();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(ServiceOffer::class);
    }

    public function contract(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getVisibilityTextAttribute(): string
    {
        return self::VISIBILITY[$this->visibility] ?? 'Unknown';
    }

    public function getPaymentMethodTextAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? 'Not specified';
    }

    public function getServiceTypeTextAttribute(): string
    {
        return self::SERVICE_TYPES[$this->service_type] ?? 'Unknown';
    }

    public function notifyMatchingUsers(): void
    {
        try {
            $matchingUsers = $this->getMatchingUsers();
            
            if ($matchingUsers->isEmpty()) {
                Log::info('No matching users found for service request', [
                    'service_request_id' => $this->id
                ]);
                return;
            }

            Log::info('Found matching users for service request', [
                'service_request_id' => $this->id,
                'user_count' => $matchingUsers->count()
            ]);

            $notifications = $this->createNotification(
                userIds: $matchingUsers->pluck('id')->toArray(),
                type: NotificationType::NEW_SERVICE_REQUEST,
                title: __('notifications.types.new_service_request'),
                message: __('notifications.messages.new_service_request', [
                    'title' => $this->title
                ])
            );

            foreach ($notifications as $notification) {
                $notificationData = [
                    'id' => $notification->id,
                    'type' => NotificationType::NEW_SERVICE_REQUEST,
                    'timestamp' => now()->toIso8601String(),
                    'is_read' => false,
                    'service_request' => [
                        'id' => $this->id,
                        'title' => $this->title,
                        'slug' => $this->slug,
                        'description' => $this->description,
                        'budget' => $this->budget,
                        'priority' => $this->priority,
                        'service_type' => $this->service_type,
                        'created_at' => $this->created_at->toIso8601String()
                    ],
                    'notification' => [
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'action_url' => \App\Models\ServiceOffer::getNotificationActionUrl(
                            \App\Constants\NotificationType::NEW_SERVICE_REQUEST,
                            $this->id
                        )
                    ]
                ];

                try {
                event(new NewServiceRequestNotification(
                    $this, 
                    [$notification->user_id],
                    $notificationData
                ));

                    Log::info('Service request notification sent', [
                        'notification_id' => $notification->id,
                        'user_id' => $notification->user_id,
                        'service_request_id' => $this->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error sending notification event', [
                        'error' => $e->getMessage(),
                        'notification_id' => $notification->id,
                        'user_id' => $notification->user_id,
                        'service_request_id' => $this->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error notifying matching users', [
                'error' => $e->getMessage(),
                'service_request_id' => $this->id
            ]);
        }
    }

    public function getMatchingUsers()
    {
        return User::whereHas('skills.categories', function ($query) {
            $query->whereIn('categories.id', function ($subQuery) {
                $subQuery->select('category_id')
                    ->from('categorizables')
                    ->where('categorizable_type', ServiceRequest::class)
                    ->where('categorizable_id', $this->id);
            });
        })->where('id', '!=', $this->user_id)
          ->get();
    }
}
