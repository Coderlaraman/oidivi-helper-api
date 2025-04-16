<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6, true);
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph(3, true),
            'address' => $this->faker->address,
            'zip_code' => $this->faker->postcode,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'budget' => $this->faker->randomFloat(2, 50, 1000),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'visibility' => $this->faker->randomElement(['public', 'private']),
            'service_type' => $this->faker->randomElement(['one_time', 'recurring']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'bank_transfer', 'paypal']),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(['published', 'in_progress', 'completed', 'cancelled']),
        ];
    }
}
