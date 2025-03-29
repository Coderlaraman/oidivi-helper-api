<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skillsByCategory = [
            'Home Services' => [
                'House Cleaning',
                'Plumbing',
                'Electrical Work',
                'Gardening & Landscaping',
                'Painting',
                'Carpentry',
                'HVAC Service & Repair',
                'Moving Services',
                'Pest Control',
                'Home Organization',
            ],
            'Professional Services' => [
                'Legal Consultation',
                'Accounting & Bookkeeping',
                'Tax Preparation',
                'Financial Planning',
                'Business Consulting',
                'Translation Services',
                'Notary Services',
                'Insurance Consulting',
                'Real Estate Services',
                'Career Counseling',
            ],
            'Personal Care' => [
                'Hair Styling',
                'Makeup Application',
                'Massage Therapy',
                'Nail Care',
                'Personal Training',
                'Nutrition Consulting',
                'Skincare Treatment',
                'Physical Therapy',
                'Life Coaching',
                'Personal Shopping',
            ],
            'Education & Tutoring' => [
                'Mathematics Tutoring',
                'Language Teaching',
                'Science Tutoring',
                'Music Lessons',
                'Art Classes',
                'Test Preparation',
                'Academic Writing',
                'Computer Skills Training',
                'Study Skills Coaching',
                'Special Education Support',
            ],
            'Technology & Digital' => [
                'Web Development',
                'Mobile App Development',
                'IT Support',
                'Digital Marketing',
                'Data Analysis',
                'Cybersecurity Services',
                'Cloud Computing',
                'Social Media Management',
                'SEO Optimization',
                'Computer Repair',
            ],
            'Events & Entertainment' => [
                'Photography',
                'Videography',
                'DJ Services',
                'Event Planning',
                'Catering',
                'Live Music Performance',
                'Party Decoration',
                'Wedding Planning',
                'MC Services',
                'Sound & Lighting',
            ],
            'Health & Wellness' => [
                'Yoga Instruction',
                'Fitness Training',
                'Nutritional Counseling',
                'Mental Health Counseling',
                'Meditation Guidance',
                'Physical Therapy',
                'Alternative Medicine',
                'Health Coaching',
                'Stress Management',
                'Wellness Workshops',
            ],
            'Automotive' => [
                'Car Repair',
                'Auto Detailing',
                'Oil Change Service',
                'Tire Service',
                'Body Work & Painting',
                'Engine Diagnostics',
                'Car Washing',
                'Vehicle Inspection',
                'Mobile Mechanic Service',
                'Auto Electronics Repair',
            ],
            'Pet Services' => [
                'Pet Grooming',
                'Dog Walking',
                'Pet Sitting',
                'Veterinary Services',
                'Pet Training',
                'Pet Transportation',
                'Pet Photography',
                'Animal Behavior Consulting',
                'Pet Boarding',
                'Pet Dental Care',
            ],
            'Business Services' => [
                'Marketing Strategy',
                'Content Writing',
                'Graphic Design',
                'Business Plan Development',
                'Market Research',
                'Social Media Marketing',
                'Brand Development',
                'Virtual Assistant Services',
                'Project Management',
                'Data Entry',
            ],
            'Creative & Design' => [
                'Logo Design',
                'Interior Design',
                'Fashion Design',
                'Product Design',
                'Illustration',
                'Animation',
                'Web Design',
                'Print Design',
                'Brand Identity Design',
                'UI/UX Design',
            ],
            'Maintenance & Repair' => [
                'Appliance Repair',
                'Furniture Repair',
                'Electronics Repair',
                'Bicycle Repair',
                'Watch & Jewelry Repair',
                'Phone & Tablet Repair',
                'Computer Repair',
                'Musical Instrument Repair',
                'Home Equipment Repair',
                'Tool Repair',
            ],
        ];

        foreach ($skillsByCategory as $categoryName => $skills) {
            $category = Category::query()
                ->where('name', $categoryName)
                ->first();

            if (!$category) {
                continue;
            }

            foreach ($skills as $skillName) {
                $skill = Skill::updateOrCreate(
                    ['name' => $skillName],
                    [
                        'description' => "Descripción de {$skillName}",
                        'experience_level' => rand(1, 5),
                        'is_active' => true
                    ]
                );
                
                if (!$skill->categories()->where('categories.id', $category->id)->exists()) {
                    $skill->categories()->attach($category->id);
                }
            }
        }
    }
}
