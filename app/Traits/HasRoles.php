<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $role): bool
    {
        \Log::info('Checking role: ' . $role);
        \Log::info('User roles: ', $this->roles->pluck('name')->toArray());
        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    public function hasAllRoles(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->count() === count($roles);
    }
} 