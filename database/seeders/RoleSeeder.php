<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Constants\Permissions;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir los roles en la plataforma
        $roles = [
            'admin',
            'moderator',
            'support',
            'user',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Permisos Generales (para todos los usuarios)
        $generalPermissions = [
            Permissions::VIEW_SERVICE_REQUESTS,
            Permissions::CREATE_SERVICE_REQUEST,
            Permissions::UPDATE_OWN_PROFILE,
            Permissions::SEND_MESSAGES,
            Permissions::MAKE_PAYMENTS,
            Permissions::RATE_USERS,
            Permissions::VIEW_USER_PROFILES,
        ];

        // Permisos específicos por rol
        $rolePermissions = [
            'admin' => [
                Permissions::MANAGE_USERS,
                Permissions::ASSIGN_ROLES,
                Permissions::VIEW_REPORTS,
                Permissions::DELETE_ANY_SERVICE_REQUEST,
                Permissions::MANAGE_PAYMENTS,
                Permissions::ACCESS_ADMIN_DASHBOARD,
            ],
            'moderator' => [
                Permissions::VIEW_REPORTS,
                Permissions::SUSPEND_USERS,
                Permissions::EDIT_ANY_SERVICE_REQUEST,
                Permissions::RESOLVE_DISPUTES,
            ],
            'support' => [
                Permissions::ASSIST_USERS,
                Permissions::RESOLVE_TICKETS,
                Permissions::MODERATE_CONTENT,
            ],
            'user' => [
                Permissions::ACCEPT_SERVICE_REQUEST,
                Permissions::CANCEL_SERVICE_REQUEST,
                Permissions::UPDATE_OWN_SERVICE_REQUEST,
                Permissions::DELETE_OWN_SERVICE_REQUEST,
                Permissions::TRACK_SERVICE_STATUS,
            ],
        ];

        // Crear los permisos en la base de datos
        $allPermissions = array_merge($generalPermissions, ...array_values($rolePermissions));
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a cada rol (combinando permisos generales + específicos)
        foreach ($rolePermissions as $role => $specificPermissions) {
            $roleModel = Role::where('name', $role)->first();
            if ($roleModel) {
                $permissionsToAssign = array_merge($generalPermissions, $specificPermissions);
                $roleModel->permissions()->syncWithoutDetaching(
                    Permission::whereIn('name', $permissionsToAssign)->pluck('id')
                );
            }
        }
    }
}
