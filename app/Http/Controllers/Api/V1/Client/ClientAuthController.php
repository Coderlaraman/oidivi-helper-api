<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Password};
use App\Models\User;
use App\Models\Role;
use Illuminate\Validation\ValidationException;

class ClientAuthController extends Controller
{
    /**
     * Registro de nuevos usuarios
     */
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'accepted_terms' => 'required|boolean',
                'address' => 'required|string|max:255',
                'zip_code' => 'required|string|max:10',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'profile_video' => 'nullable|mimetypes:video/mp4,video/avi,video/mpeg|max:10240'
            ], [
                'name.required' => 'The name is required.',
                'email.required' => 'The email is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been registered.',
                'password.required' => 'The password is required.',
                'password.min' => 'The password must be at least 8 characters long.',
                'password.confirmed' => 'The password confirmation does not match.',
                'accepted_terms.required' => 'You must accept the terms and conditions.',
                'address.required' => 'The address is required.',
                'zip_code.required' => 'The zip code is required.',
                'latitude.required' => 'The latitude is required.',
                'longitude.required' => 'The longitude is required.',
                'profile_photo.image' => 'The profile photo must be an image.',
                'profile_photo.mimes' => 'The profile photo must be in JPEG, PNG, JPG, or GIF format.',
                'profile_photo.max' => 'The profile photo file is too large. Maximum allowed size is 2MB.',
                'profile_video.mimetypes' => 'The profile video must be in MP4, AVI, or MPEG format.',
                'profile_video.max' => 'The profile video file is too large. Maximum allowed size is 10MB.'
            ]);

            $profilePhotoPath = null;
            $profileVideoPath = null;

            if ($request->hasFile('profile_photo')) {
                $profilePhotoPath = $this->storeFile($request->file('profile_photo'), 'profile_photos');
            }

            if ($request->hasFile('profile_video')) {
                $profileVideoPath = $this->storeFile($request->file('profile_video'), 'profile_videos');
            }

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'accepted_terms' => $validatedData['accepted_terms'],
                'address' => $validatedData['address'],
                'zip_code' => $validatedData['zip_code'],
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'is_active' => true,
                'profile_photo' => $profilePhotoPath,
                'profile_video' => $profileVideoPath,
            ]);

            $roles = Role::whereIn('name', ['client', 'helper'])->pluck('id');
            $user->roles()->sync($roles);
            $user->role = $roles->pluck('name')->toArray();
            $user->save();

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => __('messages.user_registered_successfully'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url,
                    'profile_video_url' => $user->profile_video_url,
                ],
                'token' => $token,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'error_details' => $e->getMessage(),
                ], 500);
        }
    }

    /**
     * Autenticación de usuarios existentes
     */
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'error' => __('messages.invalid_credentials'),
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'error' => __('messages.user_inactive'),
            ], 403);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => __('messages.login_successful'),
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }


    /**
     * Cierre de sesión del usuario autenticado
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('messages.logout_successful'),
        ]);
    }

    private function storeFile($file, $directory): ?string
    {
        if ($file) {
            return $file->storeAs($directory, uniqid() . '.' . $file->extension(), 'public');
        }
        return null;
    }

    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Actualizar perfil del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $request->user()->id,
            'address' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $user->update($validatedData);

        return response()->json([
            'message' => __('messages.profile_updated_successfully'),
            'user' => $user
        ]);
    }

    /**
     * Cambiar contraseña del usuario autenticado
     */
    public function changePassword(Request $request)
    {
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json(['error' => __('messages.invalid_current_password')], 422);
        }

        $user->update(['password' => Hash::make($validatedData['new_password'])]);

        return response()->json([
            'message' => __('messages.password_changed_successfully')
        ]);
    }

    /**
     * Enviar enlace para restablecer contraseña
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __('messages.reset_link_sent')])
            : response()->json(['error' => __('messages.reset_link_failed')], 422);
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $validatedData,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __('messages.password_reset_successful')])
            : response()->json(['error' => __('messages.password_reset_failed')], 422);
    }
}
