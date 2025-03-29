<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de admin si no existe
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crear usuario admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => true,
            'verification_status' => 'verified'
        ]);

        // Asignar rol de admin
        $admin->roles()->sync([$adminRole->id]);

        // Verificar la asignación
        \Log::info('Admin user roles: ', $admin->roles->pluck('name')->toArray());

        // Obtener roles
        $clientRole = Role::where('name', 'client')->firstOrFail();
        $helperRole = Role::where('name', 'helper')->firstOrFail();
        $moderatorRole = Role::where('name', 'moderator')->firstOrFail();
        $supportRole = Role::where('name', 'support')->firstOrFail();

        // Crear usuarios con roles client y helper
        User::factory(10)->create()->each(function ($user) use ($clientRole, $helperRole) {
            $user->roles()->attach([$clientRole->id, $helperRole->id]);
            
            // Asignar habilidades aleatorias
            $skillCount = rand(1, 3);
            $skills = Skill::inRandomOrder()->limit($skillCount)->get();
            $user->skills()->attach($skills);
        });

        // Crear usuarios moderadores
        User::factory(2)->create()->each(function ($user) use ($moderatorRole) {
            $user->roles()->attach($moderatorRole);
            
            $skillCount = rand(1, 3);
            $skills = Skill::inRandomOrder()->limit($skillCount)->get();
            $user->skills()->attach($skills);
        });

        // Crear usuarios de soporte
        User::factory(2)->create()->each(function ($user) use ($supportRole) {
            $user->roles()->attach($supportRole);
            
            $skillCount = rand(1, 3);
            $skills = Skill::inRandomOrder()->limit($skillCount)->get();
            $user->skills()->attach($skills);
        });
    }
}
