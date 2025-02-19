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
            ['name' => 'Desarrollo Web'],
            ['name' => 'Desarrollo Móvil'],
            ['name' => 'Diseño UX/UI'],
            ['name' => 'Gestión de Proyectos'],
            ['name' => 'Seguridad de la Información'],
            ['name' => 'Análisis de Datos'],
            ['name' => 'Marketing Digital'],
            ['name' => 'Soporte Técnico'],
            ['name' => 'Gestión de Comunidades'],
            ['name' => 'Calidad y Pruebas'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
