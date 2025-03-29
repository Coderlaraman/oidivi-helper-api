<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
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
            'view_service_requests',    // Ver solicitudes publicadas
            'create_service_request',  // Publicar una solicitud de servicio
            'update_own_profile',      // Editar su propio perfil
            'send_messages',           // Enviar mensajes en la plataforma
            'make_payments',           // Realizar pagos a través de la plataforma
            'rate_users',              // Calificar a otros usuarios después de un servicio
            'view_user_profiles',      // Ver perfiles de otros usuarios
        ];

        // Permisos específicos por rol
        $rolePermissions = [
            'admin' => [
                'manage_users',               // Gestionar usuarios (banear, eliminar)
                'assign_roles',               // Asignar roles a usuarios
                'view_reports',               // Ver reportes y estadísticas
                'delete_any_service_request', // Eliminar cualquier solicitud de servicio
                'manage_payments',            // Gestionar pagos y facturación
                'access_admin_dashboard',     // Acceder al panel administrativo
            ],
            'moderator' => [
                'view_reports',               // Ver reportes de usuarios o contenido
                'suspend_users',              // Suspender temporalmente usuarios problemáticos
                'edit_any_service_request',   // Editar solicitudes de servicio de cualquier usuario
                'resolve_disputes',           // Resolver disputas entre usuarios
            ],
            'support' => [
                'assist_users',               // Brindar asistencia técnica a usuarios
                'resolve_tickets',            // Resolver tickets de soporte
                'moderate_content',           // Modificar o eliminar contenido ofensivo
            ],
            'user' => [
                'accept_service_request',     // Aceptar solicitudes de servicio como helper
                'cancel_service_request',     // Cancelar una solicitud propia antes de la aceptación
                'update_own_service_request', // Editar su propia solicitud antes de ser aceptada
                'track_service_status',       // Hacer seguimiento de una solicitud en curso
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
