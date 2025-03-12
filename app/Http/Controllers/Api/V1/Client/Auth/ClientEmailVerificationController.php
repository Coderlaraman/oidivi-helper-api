<?php

namespace App\Http\Controllers\Api\V1\Client\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class ClientEmailVerificationController extends Controller
{
    /**
     * Reenvía el email de verificación.
     */
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.'
        ]);
    }

    /**
     * Verifica el email del usuario.
     */
    public function verify(Request $request, $id, $hash)
    {
        // 1. Buscar al usuario por ID
        $user = User::findOrFail($id);

        // 2. Validar que el hash coincida con el correo del usuario
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification hash.'
            ], 403);
        }

        // 3. Verificar si el usuario ya tiene el email verificado
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.'
            ]);
        }

        // 4. Marcar como verificado
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email successfully verified.'
        ]);
    }
}
