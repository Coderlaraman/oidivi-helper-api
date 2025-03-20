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


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
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
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'documents_verified_at' => 'datetime',
            'verification_documents' => 'array',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'accepted_terms' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    // Constantes para estados de verificación
    const VERIFICATION_PENDING = 'pending';
    const VERIFICATION_VERIFIED = 'verified';
    const VERIFICATION_REJECTED = 'rejected';

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
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
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
}
