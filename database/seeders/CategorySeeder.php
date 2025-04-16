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
            // Servicios para el hogar
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
                'name' => 'Home Maintenance',
                'description' => 'General home maintenance and repair services.'
            ],
            [
                'name' => 'Painting',
                'description' => 'Professional painting services for interior and exterior surfaces.'
            ],
            [
                'name' => 'Carpentry',
                'description' => 'Custom woodworking, furniture building, and repair services.'
            ],
            [
                'name' => 'Roofing',
                'description' => 'Roof installation, repair, and maintenance services.'
            ],
            [
                'name' => 'Flooring',
                'description' => 'Installation and repair of various flooring types.'
            ],
            [
                'name' => 'HVAC',
                'description' => 'Heating, ventilation, and air conditioning services.'
            ],
            
            // Servicios profesionales
            [
                'name' => 'Legal Services',
                'description' => 'Legal consultation and assistance services.'
            ],
            [
                'name' => 'Accounting',
                'description' => 'Accounting, bookkeeping, and financial record services.'
            ],
            [
                'name' => 'Tax Services',
                'description' => 'Tax preparation, planning, and consultation services.'
            ],
            [
                'name' => 'Business Consulting',
                'description' => 'Strategic business advice and consulting services.'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Digital and traditional marketing services.'
            ],
            
            // Educación y formación
            [
                'name' => 'Education',
                'description' => 'Educational services including tutoring and training.'
            ],
            [
                'name' => 'Tutoring',
                'description' => 'One-on-one academic assistance and tutoring services.'
            ],
            [
                'name' => 'Language Learning',
                'description' => 'Language instruction and translation services.'
            ],
            [
                'name' => 'Music Lessons',
                'description' => 'Music instruction and tutoring services.'
            ],
            [
                'name' => 'Art Classes',
                'description' => 'Art instruction and creative workshops.'
            ],
            
            // Tecnología y digital
            [
                'name' => 'IT Services',
                'description' => 'Computer and network support services.'
            ],
            [
                'name' => 'Web Development',
                'description' => 'Website development and maintenance services.'
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'Design and development of mobile applications.'
            ],
            [
                'name' => 'Graphic Design',
                'description' => 'Graphic design and visual communication services.'
            ],
            [
                'name' => 'Digital Marketing',
                'description' => 'Online marketing, SEO, and social media services.'
            ],
            
            // Salud y bienestar
            [
                'name' => 'Health & Wellness',
                'description' => 'Health, fitness, and wellness services.'
            ],
            [
                'name' => 'Fitness',
                'description' => 'Personal training and fitness instruction services.'
            ],
            [
                'name' => 'Nutrition',
                'description' => 'Nutritional counseling and diet planning services.'
            ],
            [
                'name' => 'Mental Health',
                'description' => 'Counseling and mental health support services.'
            ],
            [
                'name' => 'Yoga',
                'description' => 'Yoga instruction and practice services.'
            ],
            
            // Servicios para mascotas
            [
                'name' => 'Pet Care',
                'description' => 'Pet grooming, walking, and care services.'
            ],
            [
                'name' => 'Veterinary Services',
                'description' => 'Medical care and health services for animals.'
            ],
            [
                'name' => 'Pet Training',
                'description' => 'Behavioral training and obedience services for pets.'
            ],
            [
                'name' => 'Pet Sitting',
                'description' => 'In-home pet care and sitting services.'
            ],
            [
                'name' => 'Pet Grooming',
                'description' => 'Grooming, bathing, and styling services for pets.'
            ],
            
            // Eventos y entretenimiento
            [
                'name' => 'Event Planning',
                'description' => 'Professional event planning and organization services.'
            ],
            [
                'name' => 'Photography',
                'description' => 'Professional photography and videography services.'
            ],
            [
                'name' => 'Catering',
                'description' => 'Food preparation and service for events.'
            ],
            [
                'name' => 'DJ Services',
                'description' => 'Music and entertainment services for events.'
            ],
            [
                'name' => 'Wedding Planning',
                'description' => 'Specialized planning and coordination for weddings.'
            ],
            
            // Transporte y logística
            [
                'name' => 'Transportation',
                'description' => 'Transportation and delivery services.'
            ],
            [
                'name' => 'Moving Services',
                'description' => 'Professional moving and relocation services.'
            ],
            [
                'name' => 'Delivery',
                'description' => 'Package and item delivery services.'
            ],
            [
                'name' => 'Courier Services',
                'description' => 'Express delivery and courier services.'
            ],
            [
                'name' => 'Logistics',
                'description' => 'Supply chain and logistics management services.'
            ],
            
            // Servicios de emergencia
            [
                'name' => 'Emergency Services',
                'description' => '24/7 emergency repair and maintenance services.'
            ],
            [
                'name' => 'Security',
                'description' => 'Security and surveillance services.'
            ],
            [
                'name' => 'Locksmith',
                'description' => 'Lock installation, repair, and key services.'
            ],
            [
                'name' => 'Fire Protection',
                'description' => 'Fire prevention and protection services.'
            ],
            [
                'name' => 'Disaster Recovery',
                'description' => 'Services for recovery after natural disasters or emergencies.'
            ],
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
