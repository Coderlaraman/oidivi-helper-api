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
                'name' => 'Plumbing',
                'description' => 'All types of plumbing services including repairs, installations, and maintenance.',
                'icon' => 'fa-wrench',
                'sort_order' => 1,
            ],
            [
                'name' => 'Electrical',
                'description' => 'Electrical services including wiring, repairs, and installations.',
                'icon' => 'fa-bolt',
                'sort_order' => 2,
            ],
            [
                'name' => 'Cleaning',
                'description' => 'Professional cleaning services for homes and offices.',
                'icon' => 'fa-broom',
                'sort_order' => 3,
            ],
            [
                'name' => 'Home Services',
                'description' => 'General home maintenance and improvement services.',
                'icon' => 'fa-home',
                'sort_order' => 4,
            ],
            [
                'name' => 'Gardening',
                'description' => 'Garden maintenance, landscaping, and plant care services.',
                'icon' => 'fa-leaf',
                'sort_order' => 5,
            ],
            [
                'name' => 'Landscaping',
                'description' => 'Professional landscape design and maintenance services.',
                'icon' => 'fa-tree',
                'sort_order' => 6,
            ],
            [
                'name' => 'Education',
                'description' => 'Educational services including tutoring and training.',
                'icon' => 'fa-graduation-cap',
                'sort_order' => 7,
            ],
            [
                'name' => 'Tutoring',
                'description' => 'One-on-one and group tutoring services for all subjects.',
                'icon' => 'fa-book',
                'sort_order' => 8,
            ],
            [
                'name' => 'Emergency Services',
                'description' => 'Urgent repair and maintenance services available 24/7.',
                'icon' => 'fa-exclamation-triangle',
                'sort_order' => 9,
            ],
            [
                'name' => 'Pool Service',
                'description' => 'Swimming pool maintenance and repair services.',
                'icon' => 'fa-swimming-pool',
                'sort_order' => 10,
            ],
            [
                'name' => 'Moving',
                'description' => 'Professional moving and relocation services.',
                'icon' => 'fa-truck',
                'sort_order' => 11,
            ],
            [
                'name' => 'Heavy Lifting',
                'description' => 'Services for moving heavy items and furniture.',
                'icon' => 'fa-dumbbell',
                'sort_order' => 12,
            ],
            [
                'name' => 'Cooking',
                'description' => 'Professional cooking and catering services.',
                'icon' => 'fa-utensils',
                'sort_order' => 13,
            ],
            [
                'name' => 'Events',
                'description' => 'Event planning and management services.',
                'icon' => 'fa-calendar',
                'sort_order' => 14,
            ],
            [
                'name' => 'IT Services',
                'description' => 'Computer and network support services.',
                'icon' => 'fa-laptop',
                'sort_order' => 15,
            ],
            [
                'name' => 'Business Services',
                'description' => 'Professional services for businesses.',
                'icon' => 'fa-briefcase',
                'sort_order' => 16,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'sort_order' => $category['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
