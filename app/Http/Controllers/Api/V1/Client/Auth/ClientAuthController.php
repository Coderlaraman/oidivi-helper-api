<?php

namespace App\Http\Controllers\Api\V1\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\LoginClientAuthRequest;
use App\Http\Requests\Client\RegisterClientAuthRequest;
use App\Http\Resources\Client\ClientAuthResource;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponseTrait;
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
    public function register(RegisterClientAuthRequest $request)
    {
        $data = $request->validated();

        if (!$data['accepted_terms']) {
            return $this->errorResponse('You must accept the terms and conditions to proceed.', 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'accepted_terms' => $data['accepted_terms'],
            'is_active' => true,
            'address' => $data['address'],
            'phone' => $data['phone'],
            'zip_code' => $data['zip_code'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // Asignar roles de 'client' y 'helper'
        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $helperRole = Role::firstOrCreate(['name' => 'helper']);
        $user->roles()->attach([$clientRole->id, $helperRole->id]);

        // Cargar roles para incluirlos en la respuesta
        $user->load('roles');

        // Enviar email de verificación
        $user->sendEmailVerificationNotification();

        // Generar token de acceso
        $token = $user->createToken('API Token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new ClientAuthResource($user),
        ], 'User registered successfully. Please verify your email address.', 201);
    }

    /**
     * Inicia sesión para un usuario cliente existente.
     *
     * @param  LoginClientAuthRequest  $request
     * @return JsonResponse
     */
    public function login(LoginClientAuthRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        // Validar si el usuario ha verificado su correo
        if (!$user->hasVerifiedEmail()) {
            return $this->errorResponse('Please verify your email before logging in.', 403);
        }

        // Generar token de acceso
        $token = $user->createToken('API Token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new ClientAuthResource($user),
        ], 'Login successful.');
    }


    /**
     * Cierra la sesión del usuario autenticado.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

        return $this->successResponse([], 'Logout successful.');
    }
}
