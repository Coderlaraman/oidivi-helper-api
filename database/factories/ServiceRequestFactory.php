<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),  // o puedes asignar un usuario ya existente
            'title' => $this->faker->sentence(6, true),
            'description' => $this->faker->paragraph(3, true),
            'zip_code' => $this->faker->postcode,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'budget' => $this->faker->randomFloat(2, 50, 1000),
            'visibility' => $this->faker->randomElement(['public', 'private']),
            'status' => $this->faker->randomElement(['published', 'in_progress', 'completed', 'cancelled']),
        ];
    }
}
