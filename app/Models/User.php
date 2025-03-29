<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'metadata',
        'accepted_terms',
        'profile_photo_url',
        'profile_video_url',
        'biography',
        'verification_documents',
        'verification_status',
        'verification_notes',
        'phone',
        'address',
        'zip_code',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'documents_verified_at' => 'datetime',
        'verification_documents' => 'array',
        'accepted_terms' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Constantes para estados de verificación
    const VERIFICATION_PENDING = 'pending';
    const VERIFICATION_VERIFIED = 'verified';
    const VERIFICATION_REJECTED = 'rejected';

    /**
     * The possible role values for a user.
     *
     * @var array<string>
     */
    public const ROLES = [
        'user' => 'Usuario',
        'admin' => 'Administrador',
    ];

    // Método helper para verificar si el usuario está verificado
    public function isVerified(): bool
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED;
    }

    // Método para agregar un documento verificable
    public function addVerificationDocument(string $documentUrl, string $documentType): void
    {
        $documents = $this->verification_documents ?? [];
        $documents[] = [
            'url' => $documentUrl,
            'type' => $documentType,
            'uploaded_at' => now()->toDateTimeString(),
        ];
        $this->verification_documents = $documents;
        $this->save();
    }

    /**
     * Override 'sendVerificationNotification()' method to use the new notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * The skills that belong to the user.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)->withTimestamps();
    }

    /**
     * The statistics that belong to the user.
     */
    public function stats(): HasOne
    {
        return $this->hasOne(UserStat::class);
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    public function reportsMade()
    {
        return $this->hasMany(Report::class, 'reported_by');
    }

    public function reportsReceived()
    {
        return $this->hasMany(Report::class, 'reported_user');
    }

    /**
     * Get the service requests for the user.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Get the role text.
     */
    public function getRoleTextAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the number of service requests for the user.
     */
    public function getServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->count();
    }

    /**
     * Get the number of pending service requests for the user.
     */
    public function getPendingServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->where('status', 'pending')->count();
    }

    /**
     * Get the number of in progress service requests for the user.
     */
    public function getInProgressServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->where('status', 'in_progress')->count();
    }

    /**
     * Get the number of completed service requests for the user.
     */
    public function getCompletedServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->where('status', 'completed')->count();
    }

    /**
     * Get the number of cancelled service requests for the user.
     */
    public function getCancelledServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->where('status', 'cancelled')->count();
    }
}
