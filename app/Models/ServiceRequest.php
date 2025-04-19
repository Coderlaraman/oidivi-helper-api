<?php

namespace App\Models;

use App\Events\NewServiceRequestNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

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

        static::created(function ($serviceRequest) {
            // Obtener usuarios con habilidades en las mismas categorías
            $categoryIds = $serviceRequest->categories()->pluck('categories.id');
            
            $users = User::whereHas('skills.categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })->get();

            if ($users->isNotEmpty()) {
                // Crear notificaciones en la base de datos
                foreach ($users as $user) {
                    PushNotification::create([
                        'user_id' => $user->id,
                        'service_request_id' => $serviceRequest->id,
                        'title' => 'New Service Request',
                        'message' => "A new service request has been published that matches your skills: {$serviceRequest->title}"
                    ]);
                }

                // Disparar evento de notificación en tiempo real
                event(new NewServiceRequestNotification($serviceRequest, $users->pluck('id')->toArray()));
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
}
