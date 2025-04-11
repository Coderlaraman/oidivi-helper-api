<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Home Maintenance',
                'description' => 'General home maintenance and repair services.'
            ],
            [
                'name' => 'Plumbing',
                'description' => 'Plumbing services including repairs, installations, and maintenance.'
            ],
            [
                'name' => 'Electrical',
                'description' => 'Electrical services including wiring, repairs, and installations.'
            ],
            [
                'name' => 'Cleaning',
                'description' => 'Professional cleaning services for homes and offices.'
            ],
            [
                'name' => 'Gardening',
                'description' => 'Garden maintenance, landscaping, and plant care services.'
            ],
            [
                'name' => 'Education',
                'description' => 'Educational services including tutoring and training.'
            ],
            [
                'name' => 'Emergency Services',
                'description' => '24/7 emergency repair and maintenance services.'
            ],
            [
                'name' => 'Moving Services',
                'description' => 'Professional moving and relocation services.'
            ],
            [
                'name' => 'Catering',
                'description' => 'Professional cooking and catering services.'
            ],
            [
                'name' => 'IT Services',
                'description' => 'Computer and network support services.'
            ],
            [
                'name' => 'Automotive',
                'description' => 'Vehicle maintenance and repair services.'
            ],
            [
                'name' => 'Health & Wellness',
                'description' => 'Health, fitness, and wellness services.'
            ],
            [
                'name' => 'Pet Care',
                'description' => 'Pet grooming, walking, and care services.'
            ],
            [
                'name' => 'Event Planning',
                'description' => 'Professional event planning and organization services.'
            ],
            [
                'name' => 'Photography',
                'description' => 'Professional photography and videography services.'
            ],
            [
                'name' => 'Legal Services',
                'description' => 'Legal consultation and assistance services.'
            ],
            [
                'name' => 'Financial Services',
                'description' => 'Financial planning and consultation services.'
            ],
            [
                'name' => 'Interior Design',
                'description' => 'Professional interior design and decoration services.'
            ],
            [
                'name' => 'Construction',
                'description' => 'Building and construction services.'
            ],
            [
                'name' => 'Renovation',
                'description' => 'Home and property renovation services.'
            ],
            [
                'name' => 'Security',
                'description' => 'Security and surveillance services.'
            ],
            [
                'name' => 'Transportation',
                'description' => 'Transportation and delivery services.'
            ],
            [
                'name' => 'Child Care',
                'description' => 'Child care and babysitting services.'
            ],
            [
                'name' => 'Elderly Care',
                'description' => 'Care services for elderly individuals.'
            ],
            [
                'name' => 'Language Services',
                'description' => 'Translation and interpretation services.'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Digital and traditional marketing services.'
            ],
            [
                'name' => 'Web Development',
                'description' => 'Website development and maintenance services.'
            ],
            [
                'name' => 'Graphic Design',
                'description' => 'Graphic design and visual communication services.'
            ],
            [
                'name' => 'Music Lessons',
                'description' => 'Music instruction and tutoring services.'
            ],
            [
                'name' => 'Personal Training',
                'description' => 'Fitness training and coaching services.'
            ]
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description']
            ]);
        }
    }
}
