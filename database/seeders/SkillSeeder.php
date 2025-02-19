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
            'Desarrollo Web' => [
                'Programación en PHP/Laravel',
                'Desarrollo Frontend (HTML, CSS, JavaScript)',
                'Diseño de bases de datos',
                'Implementación de APIs RESTful',
                'Optimización de rendimiento web',
                'Seguridad web',
                'Desarrollo de comercio electrónico',
                'Integración de pasarelas de pago',
                'Desarrollo de sistemas de autenticación',
                'Pruebas y depuración de aplicaciones web',
            ],
            'Desarrollo Móvil' => [
                'Desarrollo de aplicaciones iOS (Swift)',
                'Desarrollo de aplicaciones Android (Kotlin/Java)',
                'Diseño de interfaces de usuario móviles',
                'Implementación de notificaciones push',
                'Integración de servicios de geolocalización',
                'Optimización de rendimiento móvil',
                'Desarrollo de aplicaciones híbridas (React Native, Flutter)',
                'Implementación de almacenamiento local',
                'Integración de SDKs de terceros',
                'Pruebas de aplicaciones móviles',
            ],
            'Diseño UX/UI' => [
                'Diseño de interfaces de usuario',
                'Creación de prototipos interactivos',
                'Diseño responsivo',
                'Arquitectura de información',
                'Diseño de experiencia de usuario',
                'Investigación de usuarios',
                'Creación de wireframes',
                'Diseño de iconos y elementos gráficos',
                'Optimización de flujos de usuario',
                'Evaluación heurística',
            ],
            'Gestión de Proyectos' => [
                'Planificación de proyectos',
                'Gestión de recursos',
                'Análisis de riesgos',
                'Metodologías ágiles (Scrum, Kanban)',
                'Seguimiento y reporte de progreso',
                'Gestión de stakeholders',
                'Estimación de tiempos y costos',
                'Resolución de problemas',
                'Gestión de calidad',
                'Liderazgo de equipos',
            ],
            'Seguridad de la Información' => [
                'Implementación de protocolos de seguridad',
                'Auditoría de seguridad',
                'Gestión de vulnerabilidades',
                'Encriptación de datos',
                'Implementación de autenticación de dos factores',
                'Seguridad en la nube',
                'Prevención de ataques DDoS',
                'Gestión de accesos y permisos',
                'Cumplimiento de normativas (GDPR, PCI DSS)',
                'Respuesta a incidentes de seguridad',
            ],
            'Análisis de Datos' => [
                'Minería de datos',
                'Visualización de datos',
                'Análisis predictivo',
                'Procesamiento de big data',
                'Implementación de machine learning',
                'Análisis de comportamiento de usuario',
                'Creación de dashboards',
                'Optimización de conversión',
                'Análisis de métricas de rendimiento',
                'Segmentación de usuarios',
            ],
            'Marketing Digital' => [
                'SEO (Optimización para motores de búsqueda)',
                'SEM (Marketing en motores de búsqueda)',
                'Marketing de contenidos',
                'Email marketing',
                'Marketing en redes sociales',
                'Análisis de métricas de marketing',
                'Optimización de conversión',
                'Gestión de campañas publicitarias',
                'Branding digital',
                'Estrategias de crecimiento y retención de usuarios',
            ],
            'Soporte Técnico' => [
                'Resolución de problemas técnicos',
                'Gestión de tickets de soporte',
                'Documentación técnica',
                'Capacitación de usuarios',
                'Mantenimiento de sistemas',
                'Gestión de actualizaciones',
                'Soporte remoto',
                'Diagnóstico de hardware y software',
                'Gestión de backups',
                'Optimización de rendimiento de sistemas',
            ],
            'Gestión de Comunidades' => [
                'Moderación de contenido',
                'Gestión de redes sociales',
                'Organización de eventos virtuales',
                'Resolución de conflictos entre usuarios',
                'Creación de contenido para la comunidad',
                'Análisis de engagement',
                'Implementación de programas de fidelización',
                'Gestión de feedback de usuarios',
                'Desarrollo de políticas de comunidad',
                'Facilitación de discusiones y foros',
            ],
            'Calidad y Pruebas' => [
                'Pruebas funcionales',
                'Pruebas de usabilidad',
                'Pruebas de rendimiento',
                'Pruebas de seguridad',
                'Automatización de pruebas',
                'Gestión de casos de prueba',
                'Pruebas de compatibilidad',
                'Pruebas de integración',
                'Pruebas de aceptación de usuario',
                'Análisis y reporte de errores',
            ],
        ];

        foreach ($skillsByCategory as $categoryName => $skills) {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                continue; // Evita errores si la categoría no existe
            }

            foreach ($skills as $skillName) {
                $skill = Skill::updateOrCreate(['name' => $skillName], ['description' => '']);

                // Asocia la habilidad con la categoría
                $category->skills()->syncWithoutDetaching([$skill->id]);
            }
        }
    }
}
