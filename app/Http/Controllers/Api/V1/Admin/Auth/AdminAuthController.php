<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginAuthRequest;
use App\Http\Resources\Admin\AdminAuthResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Iniciar sesión como administrador
     */
    public function login(AdminLoginAuthRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Provided credentials don’t match our records.'],
            ]);
        }

        if (!$user->hasRole('admin')) {
            return $this->errorResponse('You can\'t access this resource!', 403);
        }

        if (!$user->isActive()) {
            return $this->errorResponse('Your account is disabled.', 403);
        }

        if (!$user->accepted_terms) {
            return $this->errorResponse('You must accept the terms and conditions.', 403);
        }

        // Revoca tokens anteriores
        $user->tokens()->delete();

        $token = $user->createToken('admin-token')->plainTextToken;

        Log::info('Admin login successful', ['email' => $user->email, 'ip' => request()->ip()]);

        return $this->successResponse([
            'token' => $token,
            'user' => new AdminAuthResource($user),
            'roles' => $user->roles->pluck('name'),
        ], 'Login exitoso');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoca todos los tokens del usuario
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Session closed successfully');
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new AdminAuthResource($request->user()), 'User authenticated');
    }
}
