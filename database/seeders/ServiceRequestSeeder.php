<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        if ($users->isEmpty()) {
            $this->command->error('Please run UserSeeder first');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->error('Please run CategorySeeder first');
            return;
        }

        $serviceRequests = [
            [
                'title' => 'Urgent Plumbing Repair Needed',
                'description' => 'Water leak under kitchen sink needs immediate attention. The leak appears to be coming from the pipe connection and is causing water damage to the cabinet.',
                'priority' => 'urgent',
                'visibility' => 'public',
                'service_type' => 'one_time',
                'payment_method' => 'credit_card',
                'categories' => ['Plumbing', 'Emergency Services'],
            ],
            [
                'title' => 'Weekly House Cleaning Service',
                'description' => 'Looking for a professional cleaner for weekly house cleaning. 3 bedroom, 2 bathroom house. Tasks include dusting, vacuuming, mopping, and bathroom cleaning.',
                'priority' => 'medium',
                'visibility' => 'public',
                'service_type' => 'recurring',
                'payment_method' => 'bank_transfer',
                'categories' => ['Cleaning', 'Home Services'],
            ],
            [
                'title' => 'Garden Landscaping Project',
                'description' => 'Need help redesigning and landscaping backyard garden. Area is approximately 500 sq ft. Looking for someone to create a modern, low-maintenance design.',
                'priority' => 'low',
                'visibility' => 'public',
                'service_type' => 'one_time',
                'payment_method' => 'paypal',
                'categories' => ['Gardening', 'Landscaping'],
            ],
            [
                'title' => 'Private Math Tutoring for High School Student',
                'description' => 'Seeking an experienced math tutor for advanced calculus. Two sessions per week, 1.5 hours each. Must have teaching experience.',
                'priority' => 'medium',
                'visibility' => 'private',
                'service_type' => 'recurring',
                'payment_method' => 'credit_card',
                'categories' => ['Education', 'Tutoring'],
            ],
            [
                'title' => 'Emergency Electrical Repair',
                'description' => 'Power outage in half of the house. Circuit breaker issues. Need licensed electrician for immediate inspection and repair.',
                'priority' => 'urgent',
                'visibility' => 'public',
                'service_type' => 'one_time',
                'payment_method' => 'credit_card',
                'categories' => ['Electrical', 'Emergency Services'],
            ],
            [
                'title' => 'Monthly Pool Maintenance Service',
                'description' => 'Looking for regular pool maintenance including chemical balance, cleaning, and equipment check. Pool size is 15x30 feet.',
                'priority' => 'low',
                'visibility' => 'public',
                'service_type' => 'recurring',
                'payment_method' => 'bank_transfer',
                'categories' => ['Pool Service', 'Home Services'],
            ],
            [
                'title' => 'Professional Moving Assistance',
                'description' => 'Need help moving from a 2-bedroom apartment to a new house. Includes furniture disassembly/assembly. Distance is approximately 15 miles.',
                'priority' => 'medium',
                'visibility' => 'public',
                'service_type' => 'one_time',
                'payment_method' => 'paypal',
                'categories' => ['Moving', 'Heavy Lifting'],
            ],
            [
                'title' => 'Private Chef for Special Dinner',
                'description' => 'Looking for a private chef to prepare a gourmet dinner for 8 people. Mediterranean cuisine preferred. Event is next month.',
                'priority' => 'high',
                'visibility' => 'private',
                'service_type' => 'one_time',
                'payment_method' => 'credit_card',
                'categories' => ['Cooking', 'Events'],
            ],
            [
                'title' => 'Computer Network Setup for Small Office',
                'description' => 'Need IT professional to set up network for small office. Includes router configuration, printer setup, and basic security measures.',
                'priority' => 'high',
                'visibility' => 'public',
                'service_type' => 'one_time',
                'payment_method' => 'bank_transfer',
                'categories' => ['IT Services', 'Business Services'],
            ],
        ];

        foreach ($serviceRequests as $requestData) {
            $user = $users->random();
            $categoryNames = $requestData['categories'];
            unset($requestData['categories']);

            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'title' => $requestData['title'],
                'slug' => Str::slug($requestData['title']),
                'description' => $requestData['description'],
                'address' => '123 Main St, Anytown, ST 12345',
                'zip_code' => '12345',
                'latitude' => rand(-90, 90),
                'longitude' => rand(-180, 180),
                'budget' => rand(50, 1000),
                'priority' => $requestData['priority'],
                'visibility' => $requestData['visibility'],
                'service_type' => $requestData['service_type'],
                'payment_method' => $requestData['payment_method'],
                'due_date' => now()->addDays(rand(1, 30)),
                'status' => 'published',
            ]);

            // Attach categories
            $requestCategories = $categories->filter(function ($category) use ($categoryNames) {
                return in_array($category->name, $categoryNames);
            });

            $serviceRequest->categories()->attach($requestCategories->pluck('id')->toArray());
        }
    }
}

