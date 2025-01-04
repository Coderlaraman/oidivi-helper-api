<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un administrador
        $admin = User::firstOrCreate(
            ['email' => 'coderman1980@gmail.com'],
            [
                'name' => 'Jaime Sierra',
                'password' => Hash::make('coderman'),
                'is_active' => true,
                'accepted_terms' => true,
            ]
        );
        $adminRoles = Role::where('name', 'admin')->pluck('id');
        $admin->roles()->sync($adminRoles);
        $admin->role = Role::where('id', $adminRoles)->pluck('name')->toArray();
        $admin->save();

        // Crear un moderador
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Moderator',
                'password' => Hash::make('password'),
                'is_active' => true,
                'accepted_terms' => true,
            ]
        );
        $moderatorRoles = Role::where('name', 'moderator')->pluck('id');
        $moderator->roles()->sync($moderatorRoles);
        $moderator->role = Role::where('id', $moderatorRoles)->pluck('name')->toArray();
        $moderator->save();

        // Crear un usuario de soporte
        $support = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Support',
                'password' => Hash::make('password'),
                'is_active' => true,
                'accepted_terms' => true,
            ]
        );
        $supportRoles = Role::where('name', 'support')->pluck('id');
        $support->roles()->sync($supportRoles);
        $support->role = Role::where('id', $supportRoles)->pluck('name')->toArray();
        $support->save();

        // Crear usuarios estándar con roles de client y helper
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Standard User',
                'password' => Hash::make('password'),
                'is_active' => true,
                'accepted_terms' => true,
            ]
        );
        $userRoles = Role::whereIn('name', ['client', 'helper'])->pluck('id');
        $user->roles()->sync($userRoles);
        $user->role = Role::whereIn('id', $userRoles)->pluck('name')->toArray();
        $user->save();
    }
}
