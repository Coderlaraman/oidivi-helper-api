<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skillsByCategory = [
            // Servicios para el hogar
            'Plumbing' => [
                'Pipe Installation',
                'Leak Repair',
                'Drain Cleaning',
                'Fixture Installation',
                'Water Heater Service'
            ],
            'Electrical' => [
                'Wiring Installation',
                'Circuit Repair',
                'Lighting Installation',
                'Electrical Panel Upgrade',
                'Safety Inspection'
            ],
            'Cleaning' => [
                'Deep Cleaning',
                'Regular Maintenance',
                'Window Cleaning',
                'Carpet Cleaning',
                'Office Cleaning'
            ],
            'Gardening' => [
                'Lawn Maintenance',
                'Plant Care',
                'Garden Design',
                'Irrigation Installation',
                'Tree Trimming'
            ],
            'Home Maintenance' => [
                'General Repairs',
                'Preventive Maintenance',
                'Seasonal Preparation',
                'Home Inspection',
                'Handyman Services'
            ],
            'Painting' => [
                'Interior Painting',
                'Exterior Painting',
                'Decorative Painting',
                'Cabinet Refinishing',
                'Wallpaper Installation'
            ],
            'Carpentry' => [
                'Custom Furniture',
                'Cabinet Making',
                'Framing',
                'Trim Work',
                'Wood Repair'
            ],
            'Roofing' => [
                'Roof Installation',
                'Roof Repair',
                'Gutter Installation',
                'Roof Inspection',
                'Waterproofing'
            ],
            'Flooring' => [
                'Hardwood Installation',
                'Tile Installation',
                'Carpet Installation',
                'Vinyl Flooring',
                'Floor Refinishing'
            ],
            'HVAC' => [
                'AC Installation',
                'Heating Repair',
                'Duct Cleaning',
                'System Maintenance',
                'Thermostat Installation'
            ],
            
            // Servicios profesionales
            'Legal Services' => [
                'Contract Review',
                'Legal Consultation',
                'Document Preparation',
                'Legal Research',
                'Notary Services'
            ],
            'Accounting' => [
                'Bookkeeping',
                'Financial Statements',
                'Payroll Processing',
                'Audit Preparation',
                'Expense Management'
            ],
            'Tax Services' => [
                'Tax Preparation',
                'Tax Planning',
                'IRS Representation',
                'Tax Compliance',
                'Business Tax Filing'
            ],
            'Business Consulting' => [
                'Strategic Planning',
                'Process Improvement',
                'Market Analysis',
                'Business Development',
                'Operational Efficiency'
            ],
            'Marketing' => [
                'Brand Development',
                'Content Creation',
                'Social Media Management',
                'Email Marketing',
                'Marketing Strategy'
            ],
            
            // Educación y formación
            'Education' => [
                'Academic Coaching',
                'Curriculum Development',
                'Educational Assessment',
                'Special Education Support',
                'Educational Technology'
            ],
            'Tutoring' => [
                'Math Tutoring',
                'Science Tutoring',
                'English Tutoring',
                'Test Preparation',
                'Homework Help'
            ],
            'Language Learning' => [
                'English Instruction',
                'Spanish Instruction',
                'Translation Services',
                'Conversation Practice',
                'Business Language Training'
            ],
            'Music Lessons' => [
                'Piano Lessons',
                'Guitar Lessons',
                'Vocal Training',
                'Drum Lessons',
                'Music Theory'
            ],
            'Art Classes' => [
                'Painting Instruction',
                'Drawing Classes',
                'Sculpture Workshops',
                'Digital Art Training',
                'Art History'
            ],
            
            // Tecnología y digital
            'IT Services' => [
                'Computer Repair',
                'Network Setup',
                'Data Recovery',
                'IT Consulting',
                'Cybersecurity'
            ],
            'Web Development' => [
                'Website Design',
                'E-commerce Development',
                'CMS Implementation',
                'Web Maintenance',
                'Responsive Design'
            ],
            'Mobile App Development' => [
                'iOS Development',
                'Android Development',
                'App Design',
                'App Testing',
                'App Maintenance'
            ],
            'Graphic Design' => [
                'Logo Design',
                'Print Design',
                'Branding',
                'Illustration',
                'UI/UX Design'
            ],
            'Digital Marketing' => [
                'SEO Optimization',
                'PPC Management',
                'Social Media Marketing',
                'Content Marketing',
                'Analytics & Reporting'
            ],
            
            // Salud y bienestar
            'Health & Wellness' => [
                'Wellness Coaching',
                'Health Assessment',
                'Stress Management',
                'Holistic Health',
                'Preventive Care'
            ],
            'Fitness' => [
                'Personal Training',
                'Group Fitness',
                'Strength Training',
                'Cardio Conditioning',
                'Sports Specific Training'
            ],
            'Nutrition' => [
                'Meal Planning',
                'Nutritional Assessment',
                'Diet Consultation',
                'Weight Management',
                'Sports Nutrition'
            ],
            'Mental Health' => [
                'Counseling',
                'Therapy',
                'Stress Reduction',
                'Mindfulness Training',
                'Emotional Support'
            ],
            'Yoga' => [
                'Hatha Yoga',
                'Vinyasa Flow',
                'Meditation',
                'Prenatal Yoga',
                'Yoga Therapy'
            ],
            
            // Servicios para mascotas
            'Pet Care' => [
                'Pet Sitting',
                'Dog Walking',
                'Pet Feeding',
                'Pet Transportation',
                'Pet Medication Administration'
            ],
            'Veterinary Services' => [
                'Wellness Exams',
                'Vaccinations',
                'Pet Surgery',
                'Dental Care',
                'Emergency Care'
            ],
            'Pet Training' => [
                'Obedience Training',
                'Behavior Modification',
                'Puppy Training',
                'Agility Training',
                'Specialized Training'
            ],
            'Pet Sitting' => [
                'Overnight Care',
                'Daily Visits',
                'House Sitting',
                'Pet Exercise',
                'Pet Companionship'
            ],
            'Pet Grooming' => [
                'Bathing',
                'Haircuts',
                'Nail Trimming',
                'Ear Cleaning',
                'De-shedding Treatment'
            ],
            
            // Eventos y entretenimiento
            'Event Planning' => [
                'Event Coordination',
                'Venue Selection',
                'Vendor Management',
                'Budget Planning',
                'Theme Development'
            ],
            'Photography' => [
                'Portrait Photography',
                'Event Photography',
                'Commercial Photography',
                'Product Photography',
                'Photo Editing'
            ],
            'Catering' => [
                'Menu Planning',
                'Food Preparation',
                'Service Staff',
                'Beverage Service',
                'Dietary Accommodation'
            ],
            'DJ Services' => [
                'Event DJ',
                'Wedding DJ',
                'Sound Equipment',
                'Music Curation',
                'MC Services'
            ],
            'Wedding Planning' => [
                'Full Wedding Planning',
                'Day-of Coordination',
                'Vendor Referrals',
                'Budget Management',
                'Wedding Design'
            ],
            
            // Transporte y logística
            'Transportation' => [
                'Airport Transfer',
                'Corporate Transportation',
                'Event Transportation',
                'Medical Transportation',
                'Long Distance Transport'
            ],
            'Moving Services' => [
                'Residential Moving',
                'Commercial Moving',
                'Packing Services',
                'Furniture Assembly',
                'Storage Solutions'
            ],
            'Delivery' => [
                'Same-Day Delivery',
                'Food Delivery',
                'Package Delivery',
                'Grocery Delivery',
                'Furniture Delivery'
            ],
            'Courier Services' => [
                'Express Delivery',
                'Document Delivery',
                'International Shipping',
                'Scheduled Pickups',
                'Tracking Services'
            ],
            'Logistics' => [
                'Supply Chain Management',
                'Inventory Management',
                'Distribution Planning',
                'Freight Forwarding',
                'Warehouse Management'
            ],
            
            // Servicios de emergencia
            'Emergency Services' => [
                'Emergency Response',
                '24/7 Availability',
                'Disaster Recovery',
                'Crisis Management',
                'Emergency Planning'
            ],
            'Security' => [
                'Security Guards',
                'Surveillance Systems',
                'Security Consulting',
                'Access Control',
                'Event Security'
            ],
            'Locksmith' => [
                'Lock Installation',
                'Key Duplication',
                'Lock Repair',
                'Emergency Lockout',
                'Security Upgrades'
            ],
            'Fire Protection' => [
                'Fire Alarm Installation',
                'Sprinkler Systems',
                'Fire Extinguisher Service',
                'Fire Safety Training',
                'Fire Inspection'
            ],
            'Disaster Recovery' => [
                'Water Damage Restoration',
                'Fire Damage Cleanup',
                'Mold Remediation',
                'Storm Damage Repair',
                'Emergency Board Up'
            ],
        ];

        foreach ($skillsByCategory as $categoryName => $skills) {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                $this->command->warn("Category '{$categoryName}' not found, skipping related skills.");
                continue;
            }

            foreach ($skills as $skillName) {
                $skill = Skill::updateOrCreate(
                    ['name' => $skillName],
                    [
                        'description' => "Professional expertise in {$skillName}",
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
