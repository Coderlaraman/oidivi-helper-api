<?php

namespace App\Http\Controllers\Api\V1\User\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Account as StripeAccount;
use Stripe\AccountLink as StripeAccountLink;

class UserStripeConnectController extends Controller
{
    public function __construct()
    {
        // Configurar Stripe con la clave secreta
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crea (si no existe) una cuenta Connect Express para el helper
     * y devuelve un Account Link para completar el onboarding.
     */
    public function createOrGetAccount(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            // Si no existe cuenta conectada, crearla
            if (empty($user->stripe_account_id)) {
                $account = StripeAccount::create([
                    'type' => 'express',
                    'email' => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                ]);

                $user->stripe_account_id = $account->id;
                $user->stripe_charges_enabled = (bool)($account->charges_enabled ?? false);
                $user->stripe_payouts_enabled = (bool)($account->payouts_enabled ?? false);
                $user->stripe_account_status = [
                    'details_submitted' => (bool)($account->details_submitted ?? false),
                    'requirements' => $account->requirements ?? null,
                ];
                $user->save();
            } else {
                // Recuperar el estado actual de la cuenta existente
                $account = StripeAccount::retrieve($user->stripe_account_id);
                $user->stripe_charges_enabled = (bool)($account->charges_enabled ?? false);
                $user->stripe_payouts_enabled = (bool)($account->payouts_enabled ?? false);
                $user->stripe_account_status = [
                    'details_submitted' => (bool)($account->details_submitted ?? false),
                    'requirements' => $account->requirements ?? null,
                ];
                $user->save();
            }

            // Crear Account Link de onboarding/actualización
            $accountLink = StripeAccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => rtrim(config('app.frontend_url'), '/') . '/stripe/onboarding/refresh',
                'return_url' => rtrim(config('app.frontend_url'), '/') . '/stripe/onboarding/return',
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enlace de onboarding generado',
                'data' => [
                    'account_id' => $user->stripe_account_id,
                    'onboarding_url' => $accountLink->url,
                    'charges_enabled' => $user->stripe_charges_enabled,
                    'payouts_enabled' => $user->stripe_payouts_enabled,
                    'account_status' => $user->stripe_account_status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Connect createOrGetAccount error', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'No se pudo generar el enlace de onboarding'], 500);
        }
    }

    /**
     * Genera un nuevo Account Link de onboarding/actualización para una cuenta existente.
     */
    public function refreshAccountLink(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            if (empty($user->stripe_account_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aún no tienes una cuenta conectada. Debes crearla primero.',
                ], 400);
            }

            $accountLink = StripeAccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => rtrim(config('app.frontend_url'), '/') . '/stripe/onboarding/refresh',
                'return_url' => rtrim(config('app.frontend_url'), '/') . '/stripe/onboarding/return',
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nuevo enlace de onboarding generado',
                'data' => [
                    'account_id' => $user->stripe_account_id,
                    'onboarding_url' => $accountLink->url,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Connect refreshAccountLink error', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'No se pudo generar el enlace de onboarding'], 500);
        }
    }

    /**
     * Obtiene y sincroniza el estado de la cuenta conectada del helper.
     */
    public function getStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            if (empty($user->stripe_account_id)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aún no has creado una cuenta conectada',
                    'data' => [
                        'account_id' => null,
                        'charges_enabled' => false,
                        'payouts_enabled' => false,
                        'account_status' => null,
                    ],
                ]);
            }

            $account = StripeAccount::retrieve($user->stripe_account_id);

            $user->stripe_charges_enabled = (bool)($account->charges_enabled ?? false);
            $user->stripe_payouts_enabled = (bool)($account->payouts_enabled ?? false);
            $user->stripe_account_status = [
                'details_submitted' => (bool)($account->details_submitted ?? false),
                'requirements' => $account->requirements ?? null,
            ];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado de cuenta actualizado',
                'data' => [
                    'account_id' => $user->stripe_account_id,
                    'charges_enabled' => $user->stripe_charges_enabled,
                    'payouts_enabled' => $user->stripe_payouts_enabled,
                    'account_status' => $user->stripe_account_status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Connect getStatus error', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'No se pudo obtener el estado de la cuenta'], 500);
        }
    }
}