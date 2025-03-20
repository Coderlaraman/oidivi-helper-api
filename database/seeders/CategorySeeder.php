<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Home Services'],
            ['name' => 'Professional Services'],
            ['name' => 'Personal Care'],
            ['name' => 'Education & Tutoring'],
            ['name' => 'Technology & Digital'],
            ['name' => 'Events & Entertainment'],
            ['name' => 'Health & Wellness'],
            ['name' => 'Automotive'],
            ['name' => 'Pet Services'],
            ['name' => 'Business Services'],
            ['name' => 'Creative & Design'],
            ['name' => 'Maintenance & Repair'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
