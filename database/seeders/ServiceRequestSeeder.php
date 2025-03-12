<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Aseguramos que exista al menos un usuario (puedes ajustar según tu lógica)
        $user = User::first() ?? User::factory()->create();

        // Recupera todas las categorías creadas
        $categories = Category::all();

        // Crea 20 solicitudes de servicio usando la factory
        ServiceRequest::factory()->count(20)->create([
            'user_id' => $user->id,
        ])->each(function ($serviceRequest) use ($categories) {
            // Asigna de forma aleatoria entre 1 y 3 categorías a cada solicitud
            $randomCategories = $categories->random(rand(1, 3))->pluck('id')->toArray();
            $serviceRequest->categories()->sync($randomCategories);
        });
    }
}
