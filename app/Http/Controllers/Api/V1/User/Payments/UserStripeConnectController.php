<?php

namespace App\Http\Controllers\Api\V1\User\Payments;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class UserStripeConnectController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Inicia el onboarding de Stripe Connect (Express) para helpers.
     */
    public function startOnboarding(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => __('auth.unauthenticated')], 401);
        }

        if (!$user->hasRole('helper')) {
            return response()->json(['success' => false, 'message' => __('messages.connect.only_helpers')], 403);
        }

        try {
            // Crear cuenta Express si no existe
            if (!$user->stripe_account_id) {
                $account = $this->stripe->accounts->create([
                    'type' => 'express',
                    'country' => 'ES',
                    'email' => $user->email,
                    'business_type' => 'individual',
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                ]);
                $user->stripe_account_id = $account->id;
                $user->save();
            }

            $returnUrl = config('app.frontend_url') . '/connect/return';
            $refreshUrl = config('app.frontend_url') . '/connect/refresh';

            $accountLink = $this->stripe->accountLinks->create([
                'account' => $user->stripe_account_id,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $accountLink->url,
                    'expires_at' => $accountLink->expires_at,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error starting Stripe Connect onboarding', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => __('messages.connect.onboarding_error'),
            ], 500);
        }
    }

    /**
     * Obtiene el estado de onboarding y capabilities del helper.
     */
    public function getOnboardingStatus(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('auth.unauthenticated')], 401);
        }
        if (!$user->hasRole('helper') || !$user->stripe_account_id) {
            return response()->json(['success' => false, 'message' => __('messages.connect.only_helpers')], 403);
        }

        try {
            $account = $this->stripe->accounts->retrieve($user->stripe_account_id, []);

            // Persistir estado principal
            $user->stripe_charges_enabled = (bool) $account->charges_enabled;
            $user->stripe_payouts_enabled = (bool) $account->payouts_enabled;
            $user->stripe_requirements = $account->requirements ? $account->requirements->toArray() : null;
            if ($account->charges_enabled && $account->payouts_enabled && !$user->stripe_onboarded_at) {
                $user->stripe_onboarded_at = now();
            }
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'charges_enabled' => $account->charges_enabled,
                    'payouts_enabled' => $account->payouts_enabled,
                    'details_submitted' => $account->details_submitted,
                    'requirements' => $account->requirements,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching Stripe account status', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => __('messages.connect.status_error'),
            ], 500);
        }
    }

    /**
     * Refresca un account link de onboarding si expiró.
     */
    public function refreshOnboardingLink(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('auth.unauthenticated')], 401);
        }
        if (!$user->hasRole('helper') || !$user->stripe_account_id) {
            return response()->json(['success' => false, 'message' => __('messages.connect.only_helpers')], 403);
        }
        try {
            $returnUrl = config('app.frontend_url') . '/connect/return';
            $refreshUrl = config('app.frontend_url') . '/connect/refresh';

            $accountLink = $this->stripe->accountLinks->create([
                'account' => $user->stripe_account_id,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $accountLink->url,
                    'expires_at' => $accountLink->expires_at,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error refreshing Stripe Connect account link', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => __('messages.connect.refresh_error'),
            ], 500);
        }
    }
}