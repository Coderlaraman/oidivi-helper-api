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
        // Obtener roles
        $adminRole = Role::where('name', 'admin')->first();
        $clientRole = Role::where('name', 'client')->first();
        $helperRole = Role::where('name', 'helper')->first();
        $moderatorRole = Role::where('name', 'moderator')->first();
        $supportRole = Role::where('name', 'support')->first();

        // Crear un usuario admin
        $admin = User::factory()->create([
            'name' => 'Jaime Sierra',
            'email' => 'coderman1980@gmail.com',
        ]);
        $admin->roles()->attach($adminRole);

        // Crear 10 usuarios con roles client y helper
        $clientHelperUsers = User::factory(10)->create();
        foreach ($clientHelperUsers as $user) {
            $user->roles()->attach([$clientRole->id, $helperRole->id]);

            // Asignar de 1 a 3 habilidades aleatorias
            $skills = Skill::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $user->skills()->attach($skills);
        }

        // Crear 2 usuarios con rol moderator
        $moderatorUsers = User::factory(2)->create();
        foreach ($moderatorUsers as $user) {
            $user->roles()->attach($moderatorRole);

            // Asignar de 1 a 3 habilidades aleatorias
            $skills = Skill::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $user->skills()->attach($skills);
        }

        // Crear 2 usuarios con rol support
        $supportUsers = User::factory(2)->create();
        foreach ($supportUsers as $user) {
            $user->roles()->attach($supportRole);

            // Asignar de 1 a 3 habilidades aleatorias
            $skills = Skill::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $user->skills()->attach($skills);
        }
    }
}
