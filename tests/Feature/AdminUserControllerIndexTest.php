<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;


class AdminUserControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles necesarios para la prueba
        Role::factory()->create(['name' => 'admin']);
        Role::factory()->create(['name' => 'user']);

        // Crear un usuario administrador para autenticarse en la prueba
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        // Asignar rol de administrador utilizando el método centralizado
        $this->adminUser->syncRolesByName(['admin']);

        // Autenticar al usuario administrador usando Sanctum
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    #[Test]
    public function it_can_list_users()
    {
        // Crear algunos usuarios adicionales para probar la paginación y el listado
        User::factory()->count(5)->create();

        // Realizar la solicitud GET al endpoint de listado de usuarios
        $response = $this->getJson(route('admin.users.index'));

        // Validar que la respuesta tenga el código HTTP 200 y la estructura esperada
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',   // Array de usuarios
                    'links',  // Paginación
                    'meta'
                ]
            ]);

        // Adicional: Verificar que la lista de usuarios no esté vacía
        $responseData = $response->json('data.data');
        $this->assertNotEmpty($responseData);
    }
}
