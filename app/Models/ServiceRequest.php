<?php

namespace App\Models;

use App\Events\NewServiceRequestNotification;
use App\Traits\Notifiable;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Constants\NotificationType;
use Illuminate\Support\Facades\Log;

/**
 * Class ServiceRequest
 *
 * Modelo que representa una solicitud de servicio dentro de la plataforma.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $address
 * @property string $zip_code
 * @property float $latitude
 * @property float $longitude
 * @property float|null $distance
 * @property float $budget
 * @property string $visibility
 * @property string $status
 * @property string $payment_method
 * @property string $service_type
 * @property string $priority
 * @property array $metadata
 * @property Carbon $due_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $status_text
 * @property-read string $priority_text
 * @property-read string $visibility_text
 * @property-read string $payment_method_text
 * @property-read string $service_type_text
 * @property bool $initial_payment_confirmed
 */

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
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
        'initial_payment_confirmed',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
        'initial_payment_confirmed' => 'boolean',
    ];

    // --- ESTADOS DE LA SOLICITUD ---

    /** Estado: Publicada */
    public const STATUS_PUBLISHED = 'published';
    /** Estado: En progreso */
    public const STATUS_IN_PROGRESS = 'in_progress';
    /** Estado: Cancelada */
    public const STATUS_CANCELED = 'canceled';
    /** Estado: Completada */
    public const STATUS_COMPLETED = 'completed';

    /**
     * Lista de todos los estados válidos para una solicitud de servicio.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_PUBLISHED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_CANCELED,
        self::STATUS_COMPLETED,
    ];

    /**
     * Valores posibles de prioridad para una solicitud de servicio.
     *
     * @var array<string, string>
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    /**
     * Tipos de visibilidad posibles.
     *
     * @var array<string, string>
     */
    public const VISIBILITY = [
        'public' => 'Public',
        'private' => 'Private',
    ];

    /**
     * Métodos de pago disponibles.
     *
     * @var array<string, string>
     */
    public const PAYMENT_METHODS = [
        'paypal' => 'PayPal',
        'credit_card' => 'Credit Card',
        'bank_transfer' => 'Bank Transfer',
    ];

    /**
     * Tipos de servicio posibles.
     *
     * @var array<string, string>
     */
    public const SERVICE_TYPES = [
        'one_time' => 'One Time',
        'recurring' => 'Recurring',
    ];

    /**
     * Inicializa el modelo y define eventos para la generación de slug.
     *
     * @return void
     */
    protected static function boot(): void
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
     * Genera un slug único basado en el título de la solicitud.
     *
     * @return void
     */
    public function generateUniqueSlug(): void
    {
        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $count = 1;

        while (
            static::where('slug', $slug)
                ->where('id', '!=', $this->id ?? 0)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        $this->slug = $slug;
    }

    /**
     * Devuelve el nombre de la clave de ruta para la vinculación de modelos.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Verifica si el título ya existe para otro usuario.
     *
     * @param string $title
     * @param int|null $excludeId
     * @return bool
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
     * Relación: Usuario que creó la solicitud de servicio.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el texto descriptivo del estado actual.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        $map = [
            self::STATUS_PUBLISHED => __('messages.service_requests.status.published'),
            self::STATUS_IN_PROGRESS => __('messages.service_requests.status.in_progress'),
            self::STATUS_CANCELED => __('messages.service_requests.status.canceled'),
            self::STATUS_COMPLETED => __('messages.service_requests.status.completed'),
        ];
        return $map[$this->status] ?? __('messages.common.unknown');
    }

    /**
     * Obtiene el texto descriptivo de la prioridad.
     *
     * @return string
     */
    public function getPriorityTextAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? __('messages.common.unknown');
    }

    /**
     * Verifica si la solicitud está vencida.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() &&
            !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELED]);
    }

    /**
     * Verifica si la solicitud puede cambiar a un nuevo estado.
     *
     * @param string $newStatus
     * @return bool
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_PUBLISHED => [self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_CANCELED],
            self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_CANCELED],
            self::STATUS_COMPLETED => [],
            self::STATUS_CANCELED => [self::STATUS_PUBLISHED], // Restaurar de cancelado a publicado
        ];

        return in_array($newStatus, $validTransitions[$this->status] ?? []);
    }

    /**
     * Asocia categorías a la solicitud de servicio.
     *
     * @param array $categoryIds
     * @return void
     */
    public function attachCategories(array $categoryIds): void
    {
        $this->categories()->attach($categoryIds);
    }

    /**
     * Desasocia categorías de la solicitud de servicio.
     *
     * @param array $categoryIds
     * @return void
     */
    public function detachCategories(array $categoryIds): void
    {
        $this->categories()->detach($categoryIds);
    }

    /**
     * Sincroniza las categorías de la solicitud de servicio.
     *
     * @param array $categoryIds
     * @return void
     */
    public function syncCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }

    /**
     * Verifica si la solicitud está publicada.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Verifica si la solicitud está en progreso.
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Verifica si la solicitud está completada.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si la solicitud está cancelada.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Verifica si la solicitud es urgente.
     *
     * @return bool
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

        /**
         * Obtiene la cantidad total de solicitudes de servicio publicadas.
         *
         * @return int
         */
        public static function totalPublished(): int
        {
            return static::where('status', self::STATUS_PUBLISHED)->count();
        }

        /**
         * Obtiene la cantidad total de solicitudes atendidas (en progreso o completadas).
         *
         * @return int
         */
        public static function totalAttended(): int
        {
            return static::whereIn('status', [self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED])->count();
        }

        /**
         * Calcula el porcentaje de solicitudes atendidas respecto al total de publicadas.
         *
         * @return float
         */
        public static function attendedPercentage(): float
        {
            $totalPublished = static::totalPublished();
            if ($totalPublished === 0) {
                return 0.0;
            }
            $totalAttended = static::totalAttended();
            return round(($totalAttended / $totalPublished) * 100, 2);
        }

    /**
     * Marca la solicitud como "en progreso".
     *
     * @return void
     */
    public function markInProgress(): void
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->save();
    }

    /**
     * Relación: Categorías asociadas a la solicitud.
     *
     * @return MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')
            ->withTimestamps();
    }

    /**
     * Relación: Ofertas asociadas a la solicitud.
     *
     * @return HasMany
     */
    public function offers(): HasMany
    {
        return $this->hasMany(ServiceOffer::class);
    }

    /**
     * Relación: Contratos asociados a la solicitud.
     *
     * @return HasMany
     */
    public function contract(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Relación: Reseñas asociadas a la solicitud.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relación: Transacciones asociadas a la solicitud.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Obtiene el texto descriptivo de la visibilidad.
     *
     * @return string
     */
    public function getVisibilityTextAttribute(): string
    {
        return self::VISIBILITY[$this->visibility] ?? __('messages.common.unknown');
    }

    /**
     * Obtiene el texto descriptivo del método de pago.
     *
     * @return string
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? __('messages.common.not_specified');
    }

    /**
     * Obtiene el texto descriptivo del tipo de servicio.
     *
     * @return string
     */
    public function getServiceTypeTextAttribute(): string
    {
        return self::SERVICE_TYPES[$this->service_type] ?? __('messages.common.unknown');
    }

    /**
     * Notifica a los usuarios que coinciden con la solicitud de servicio.
     *
     * @return void
     */
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
                        'action_url' => ServiceOffer::getNotificationActionUrl(
                            NotificationType::NEW_SERVICE_REQUEST,
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
                } catch (Exception $e) {
                    Log::error('Error sending notification event', [
                        'error' => $e->getMessage(),
                        'notification_id' => $notification->id,
                        'user_id' => $notification->user_id,
                        'service_request_id' => $this->id
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error notifying matching users', [
                'error' => $e->getMessage(),
                'service_request_id' => $this->id
            ]);
        }
    }

    /**
     * Obtiene los usuarios que coinciden con las categorías de la solicitud.
     *
     * @return Collection
     */
    public function getMatchingUsers(): Collection
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
