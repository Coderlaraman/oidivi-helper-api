<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    /**
     * Define la relación de muchos a muchos entre usuarios y roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Verifica si el usuario tiene al menos uno de los roles proporcionados.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Verifica si el usuario tiene todos los roles proporcionados.
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    /**
     * Obtiene todos los permisos del usuario basados en sus roles.
     */
    public function permissions()
    {
        return $this->roles()
                    ->with('permissions')
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('name')
                    ->unique();
    }

    /**
     * Verifica si el usuario tiene un permiso específico.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->contains($permission);
    }

    /**
     * Verifica si el usuario tiene al menos uno de los permisos proporcionados.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->intersect($permissions)->isNotEmpty();
    }

    /**
     * Verifica si el usuario tiene todos los permisos proporcionados.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return collect($permissions)->diff($this->permissions())->isEmpty();
    }

    /**
     * Asigna un rol al usuario sin eliminar otros roles existentes.
     */
    public function assignRole(string $role): void
    {
        if ($roleModel = Role::where('name', $role)->first()) {
            $this->roles()->syncWithoutDetaching([$roleModel->id]);
        }
    }

    /**
     * Elimina un rol del usuario.
     */
    public function removeRole(string $role): void
    {
        if ($roleModel = Role::where('name', $role)->first()) {
            $this->roles()->detach($roleModel->id);
        }
    }

    /**
     * Sincroniza roles basados en un array de nombres.
     * Centraliza la lógica de asignación de roles para evitar duplicación en el controlador.
     */
    public function syncRolesByName(array $roleNames): void
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);
    }

    /**
     * Reemplaza el rol principal del usuario.
     * Asegura que solo un administrador pueda modificar roles.
     */
    public function setMainRoleAttribute(string $roleName): void
    {
        if (!auth()->user()->hasRole('admin')) {
            throw new \Exception('No tienes permiso para modificar roles.');
        }
        
        if ($role = Role::where('name', $roleName)->first()) {
            $this->roles()->sync([$role->id]);
        }
    }
}
