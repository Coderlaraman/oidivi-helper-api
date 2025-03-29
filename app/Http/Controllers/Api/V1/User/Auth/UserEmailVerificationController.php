<?php

namespace App\Http\Controllers\Api\V1\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserEmailVerificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Reenvía el email de verificación.
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return $this->successResponse(
                    [],
                    'Email already verified'
                );
            }

            $user->sendEmailVerificationNotification();

            return $this->successResponse(
                [],
                'Verification email sent successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error sending verification email',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Verifica el email del usuario.
     */
    public function verify(Request $request, $id, $hash): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            if (!hash_equals(
                (string) $hash,
                sha1($user->getEmailForVerification())
            )) {
                return $this->errorResponse(
                    'Invalid verification link',
                    403
                );
            }

            if ($user->hasVerifiedEmail()) {
                return $this->successResponse(
                    [],
                    'Email already verified'
                );
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return $this->successResponse(
                [],
                'Email verified successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error verifying email',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function resend(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse(
                    'Email already verified',
                    400
                );
            }

            $user->sendEmailVerificationNotification();

            return $this->successResponse(
                [],
                'Verification email resent successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error resending verification email',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
