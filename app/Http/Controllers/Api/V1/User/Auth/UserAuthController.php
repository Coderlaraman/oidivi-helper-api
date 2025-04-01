<?php

namespace App\Http\Controllers\Api\V1\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Requests\User\Auth\ForgotPasswordRequest;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Resources\User\UserAuthResource;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Registra un nuevo usuario normal.
     *
     * Al registrarse se asigna el rol "user" de forma predeterminada,
     * se encripta la contraseña y se envía la notificación de verificación
     * de email para confirmar la cuenta.
     *
     * @param UserRegisterRequest $request
     * @return JsonResponse
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        try {
            $userData = $request->validated();

            // Verificar que los términos han sido aceptados
            if (!isset($userData['accepted_terms']) || !$userData['accepted_terms']) {
                return $this->errorResponse(
                    'You must accept the terms and conditions',
                    422
                );
            }

            // Encriptar la contraseña y asegurar que el usuario esté activo por defecto
            $userData['password'] = Hash::make($userData['password']);
            $userData['is_active'] = $userData['is_active'] ?? true;

            // Crear el usuario
            $user = User::create($userData);

            // Asignar el rol "user"
            $user->syncRolesByName(['user']);

            // Cargar relaciones
            $user->load('roles');

            // Enviar email de verificación
            $user->sendEmailVerificationNotification();

            // Generar token
            $token = $user->createToken('API Token')->plainTextToken;

            return $this->successResponse(
                [
                    'token' => $token,
                    'user' => new UserAuthResource($user)
                ],
                'User registered successfully. Please verify your email.',
                201
            );

        } catch (Exception $e) {
            Log::error('Error during user registration: ' . $e->getMessage());

            return $this->errorResponse(
                'Error during registration',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Inicia sesión un usuario normal.
     *
     * Valida las credenciales y, de ser correctas, revoca los tokens previos
     * y crea uno nuevo. Además, verifica que la cuenta esté activa y que se
     * hayan aceptado los términos (si aplica).
     *
     * @param UserLoginRequest $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
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

            if (!$user->is_active) {
                return $this->errorResponse(
                    'Your account is disabled.',
                    403
                );
            }

            // Revocar tokens anteriores
            $user->tokens()->delete();

            // Generar nuevo token
            $token = $user->createToken('API Token')->plainTextToken;

            return $this->successResponse(
                [
                    'token' => $token,
                    'user' => new UserAuthResource($user)
                ],
                'Login successful'
            );

        } catch (Exception $e) {
            Log::error('Error during user login: ' . $e->getMessage());

            return $this->errorResponse(
                'Error during login',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Cierra la sesión del usuario.
     *
     * Revoca el token actual del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            if (!auth()->check()) {
                return $this->errorResponse(
                    'User not authenticated',
                    401
                );
            }

            auth()->user()->currentAccessToken()->delete();

            return $this->successResponse(
                [],
                'Logout successful'
            );

        } catch (Exception $e) {
            Log::error('Error during user logout: ' . $e->getMessage());

            return $this->errorResponse(
                'Error during logout',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Send a reset link to the given user.
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $email = $request->validated()['email'];
            $user = User::where('email', $email)->first();
    
            if (!$user) {
                // Para seguridad, no revelamos si el email existe o no
                return $this->successResponse(
                    [],
                    'Password reset link has been sent to your email address if it exists in our system.'
                );
            }
    
            // Delete any existing tokens for this user
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();
    
            // Create a new token
            $token = Str::random(64);
            
            // Store the token in the database
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => Hash::make($token), // Store hashed token for security
                'created_at' => now()
            ]);
    
            // Send the notification with the token
            try {
                $user->notify(new ResetPasswordNotification($token));
                Log::info('Password reset notification sent to: ' . $email);
            } catch (\Exception $e) {
                Log::error('Failed to send password reset notification: ' . $e->getMessage());
                throw $e;
            }
    
            return $this->successResponse(
                [],
                'Password reset link has been sent to your email address.'
            );
    
        } catch (Exception $e) {
            Log::error('Error during password reset request: ' . $e->getMessage());
    
            return $this->errorResponse(
                'Error sending password reset email',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Reset the user's password.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // Find the token record
            $tokenRecord = DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->first();

            // Check if token exists and is valid
            if (!$tokenRecord || !Hash::check($validated['token'], $tokenRecord->token)) {
                return $this->errorResponse(
                    'Invalid or expired password reset token.',
                    400
                );
            }

            // Check if token is expired (default: 60 minutes)
            $expiresMinutes = config('auth.passwords.users.expire', 60);
            if (now()->diffInMinutes($tokenRecord->created_at) > $expiresMinutes) {
                // Delete the expired token
                DB::table('password_reset_tokens')
                    ->where('email', $validated['email'])
                    ->delete();
                    
                return $this->errorResponse(
                    'Password reset token has expired.',
                    400
                );
            }

            // Update the user's password
            $user = User::where('email', $validated['email'])->first();
            $user->password = Hash::make($validated['password']);
            $user->save();

            // Delete the token after successful reset
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();

            // Revoke all existing tokens
            $user->tokens()->delete();

            return $this->successResponse(
                [],
                'Password has been successfully reset.'
            );

        } catch (Exception $e) {
            Log::error('Error during password reset: ' . $e->getMessage());

            return $this->errorResponse(
                'Error resetting password',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
