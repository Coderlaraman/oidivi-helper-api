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
        $user = User::first() ?? User::factory()->create();

        $sampleRequests = [
            [
                'title' => 'Need Professional House Cleaning Service',
                'description' => 'Looking for a thorough house cleaning service for a 3-bedroom home. Deep cleaning required for kitchen and bathrooms. Must have experience with eco-friendly cleaning products.',
                'category' => 'Home Services',
                'budget' => 150.00,
            ],
            [
                'title' => 'Website Development for Small Business',
                'description' => 'Need a professional web developer to create a modern, responsive website for my local bakery. Should include an online ordering system and gallery section.',
                'category' => 'Technology & Digital',
                'budget' => 2500.00,
            ],
            [
                'title' => 'Personal Trainer for Weight Loss Journey',
                'description' => 'Seeking a certified personal trainer to help with weight loss goals. Prefer someone with experience in nutrition planning and working with beginners.',
                'category' => 'Health & Wellness',
                'budget' => 400.00,
            ],
            [
                'title' => 'Car AC Repair Needed',
                'description' => 'My car\'s AC isn\'t cooling properly. Need a qualified mechanic to diagnose and fix the issue. 2018 Toyota Camry.',
                'category' => 'Automotive',
                'budget' => 300.00,
            ],
            [
                'title' => 'Wedding Photography Services',
                'description' => 'Looking for an experienced photographer for a wedding in June. Need both ceremony and reception coverage, plus engagement photos.',
                'category' => 'Events & Entertainment',
                'budget' => 1800.00,
            ],
            [
                'title' => 'Math Tutor for High School Student',
                'description' => 'Seeking a math tutor for advanced algebra and pre-calculus. Twice weekly sessions needed for a high school junior.',
                'category' => 'Education & Tutoring',
                'budget' => 45.00,
            ],
            [
                'title' => 'Logo Design for Tech Startup',
                'description' => 'Need a professional logo designer to create a modern, minimalist logo for our AI startup. Should include brand guidelines and multiple formats.',
                'category' => 'Creative & Design',
                'budget' => 750.00,
            ],
            [
                'title' => 'Dog Training Sessions Needed',
                'description' => 'Looking for a professional dog trainer for our 6-month-old German Shepherd. Focus on basic obedience and leash training.',
                'category' => 'Pet Services',
                'budget' => 85.00,
            ],
            [
                'title' => 'Tax Preparation Services',
                'description' => 'Need a certified accountant for personal and small business tax preparation. Must be familiar with freelance income and deductions.',
                'category' => 'Professional Services',
                'budget' => 350.00,
            ],
            [
                'title' => 'Plumbing Repair for Bathroom',
                'description' => 'Need a licensed plumber to fix a leaking shower and replace bathroom faucet. Urgent request.',
                'category' => 'Maintenance & Repair',
                'budget' => 200.00,
            ],
        ];

        foreach ($sampleRequests as $request) {
            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'title' => $request['title'],
                'description' => $request['description'],
                'budget' => $request['budget'],
                'zip_code' => '90210',
                'latitude' => rand(3300, 4800) / 100,
                'longitude' => rand(-11800, -11700) / 100,
                'visibility' => 'public',
                'status' => 'published',
            ]);

            // Assign the corresponding category
            $category = Category::where('name', $request['category'])->first();
            if ($category) {
                $serviceRequest->categories()->attach($category->id);
            }
        }
    }
}
