<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminUserSeederProduction extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar roles base
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'moderator']);
        Role::firstOrCreate(['name' => 'support']);
        Role::firstOrCreate(['name' => 'user']);

        // Crear usuario admin determinista SIN Faker
        $admin = User::firstOrCreate(
            ['email' => 'admin@oidivi-helper.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt(env('ADMIN_INITIAL_PASSWORD', 'ChangeMe123!')),
                'email_verified_at' => now(),
                'is_active' => true,
                'verification_status' => 'verified',
            ]
        );

        // Asignar rol de admin
        $admin->roles()->sync([$adminRole->id]);
    }
}