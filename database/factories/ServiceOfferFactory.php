<?php

namespace Database\Factories;

use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOfferFactory extends Factory
{
    protected $model = ServiceOffer::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'user_id' => User::factory(),
            'price_proposed' => $this->faker->randomFloat(2, 10, 1000),
            // estimated_time en horas
            'estimated_time' => $this->faker->numberBetween(1, 72),
            'message' => $this->faker->boolean(70) ? $this->faker->sentence(12) : null,
            'status' => ServiceOffer::STATUS_PENDING,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn () => ['status' => ServiceOffer::STATUS_PENDING]);
    }

    public function inReview(): self
    {
        return $this->state(fn () => ['status' => ServiceOffer::STATUS_IN_REVIEW]);
    }

    public function accepted(): self
    {
        return $this->state(fn () => ['status' => ServiceOffer::STATUS_ACCEPTED]);
    }

    public function rejected(): self
    {
        return $this->state(fn () => ['status' => ServiceOffer::STATUS_REJECTED]);
    }
}