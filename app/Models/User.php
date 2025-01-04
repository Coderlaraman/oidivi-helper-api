<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
        'role',
        'is_active',
        'accepted_terms',
        'profile_photo',
        'profile_video',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'accepted_terms' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'role' => 'array', // Conversión automática a arreglo
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Sincroniza el campo 'role' con el primer rol asignado.
     */
    public function syncPrimaryRole(): void
    {
        $this->role = $this->roles->pluck('name')->toArray();
        $this->save();
    }


    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo ? Storage::url($this->profile_photo) : null;
    }

    public function getProfileVideoUrlAttribute(): ?string
    {
        return $this->profile_video ? Storage::url($this->profile_video) : null;
    }
}
