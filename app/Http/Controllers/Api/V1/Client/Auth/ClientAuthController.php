<?php

namespace App\Http\Controllers\Api\V1\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Auth\LoginClientAuthRequest;
use App\Http\Requests\Client\Auth\RegisterClientAuthRequest;
use App\Http\Resources\Client\ClientAuthResource;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientAuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Registra un nuevo usuario cliente.
     *
     * @param  RegisterClientAuthRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterClientAuthRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (!$validated['accepted_terms']) {
                return $this->errorResponse(
                    'You must accept the terms and conditions',
                    422
                );
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'accepted_terms' => $validated['accepted_terms'],
                'is_active' => true,
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'zip_code' => $validated['zip_code'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Asignar roles
            $clientRole = Role::firstOrCreate(['name' => 'client']);
            $helperRole = Role::firstOrCreate(['name' => 'helper']);
            $user->roles()->attach([$clientRole->id, $helperRole->id]);

            // Cargar relaciones
            $user->load('roles');

            // Enviar email de verificación
            $user->sendEmailVerificationNotification();

            // Generar token
            $token = $user->createToken('API Token')->plainTextToken;

            return $this->successResponse(
                [
                    'token' => $token,
                    'user' => new ClientAuthResource($user)
                ],
                'User registered successfully. Please verify your email.',
                201
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error during registration',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Inicia sesión para un usuario cliente existente.
     *
     * @param  LoginClientAuthRequest  $request
     * @return JsonResponse
     */
    public function login(LoginClientAuthRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse(
                    'Invalid credentials',
                    401
                );
            }

            if (!$user->hasVerifiedEmail()) {
                return $this->errorResponse(
                    'Please verify your email before logging in',
                    403
                );
            }

            $token = $user->createToken('API Token')->plainTextToken;

            return $this->successResponse(
                [
                    'token' => $token,
                    'user' => new ClientAuthResource($user)
                ],
                'Login successful'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error during login',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Cierra la sesión del usuario autenticado.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            auth()->user()->currentAccessToken()->delete();

            return $this->successResponse(
                [],
                'Logout successful'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error during logout',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
