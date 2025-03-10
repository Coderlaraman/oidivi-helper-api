<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientAuthResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientAuthController extends Controller
{
    /**
     * Registro de nuevos usuarios
     */
    public function register(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'accepted_terms' => 'required|boolean',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users',
            'zip_code' => 'required|string|max:10',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ], __('validation.custom.register'));

        if (!$validatedData['accepted_terms']) {
            return response()->json([
                'error' => 'You must accept the terms and conditions to proceed.'
            ], 422);
        }

        // Crear el usuario
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'accepted_terms' => $validatedData['accepted_terms'],
            'is_active' => true,
            'address' => $validatedData['address'],
            'phone' => $validatedData['phone'],
            'zip_code' => $validatedData['zip_code'],
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
        ]);

        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $helperRole = Role::firstOrCreate(['name' => 'helper']);
        $user->roles()->attach([$clientRole->id, $helperRole->id]);

        // Cargar los roles en el usuario
        $user->load('roles');

        // Enviar el email de verificación
        $user->sendEmailVerificationNotification();

        // Generar el token (si deseas permitir el login sin verificación o bien informarlo en el frontend)
        $token = $user->createToken('API Token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new ClientAuthResource($user),
            'message' => 'User registered successfully. Please verify your email address.'  // Mensaje adicional
        ], 'User registered successfully.', 201);
    }

    /**
     * Inicio de sesión de usuarios
     */
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], __('validation.custom.login'));

        // Buscar el usuario
        $user = User::where('email', $validatedData['email'])->first();

        // Verificar credenciales
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        // Generar un token de acceso
        $token = $user->createToken('API Token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new ClientAuthResource($user),
        ], 'Login successful.');
    }

    /**
     * Cierre de sesión del usuario autenticado
     */
    public function logout(Request $request)
    {
        // Eliminar el token actual
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse([], 'Logout successful.');
    }

    /**
     * Método privado para respuestas de éxito
     */
    private function successResponse(array $data = [], string $message = 'Operation successful', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Método privado para respuestas de error
     */
    private function errorResponse(string $message = 'An error occurred', int $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => [],
        ], $statusCode);
    }
}
