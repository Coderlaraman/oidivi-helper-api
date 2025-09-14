<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\AgreementResource;
use App\Models\Agreement;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Controlador para gestionar contratos entre clientes y proveedores de servicios.
 */
class AgreementController extends Controller
{
    /**
     * Obtiene todos los contratos del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $query = Agreement::query()
                ->where(function ($q) use ($user) {
                    $q->where('client_id', $user->id)
                      ->orWhere('provider_id', $user->id);
                })
                ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, Agreement::STATUSES)) {
                $query->where('status', $status);
            }

            $agreements = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => AgreementResource::collection($agreements->items()),
                    'current_page' => $agreements->currentPage(),
                    'last_page' => $agreements->lastPage(),
                    'per_page' => $agreements->perPage(),
                    'total' => $agreements->total(),
                ],
                'message' => __('messages.agreements.index_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching agreements', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.index_error')
            ], 500);
        }
    }

    /**
     * Muestra un contrato específico.
     *
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function show(Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario tenga acceso al acuerdo
            if ($agreement->client_id !== $user->id && $agreement->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized')
                ], 403);
            }

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider', 'payments']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.show_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.show_error')
            ], 500);
        }
    }

    /**
     * Crea un nuevo acuerdo basado en una oferta de servicio.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'service_offer_id' => 'required|exists:service_offers,id',
                'terms' => 'nullable|array',
                'expires_at' => 'nullable|date|after:now'
            ]);

            $user = Auth::user();
            $serviceOffer = ServiceOffer::with('serviceRequest')->findOrFail($validated['service_offer_id']);

            // Verificar que el usuario sea el dueño de la solicitud
            if ($serviceOffer->serviceRequest->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_create')
                ], 403);
            }

            // Verificar que la oferta esté pendiente
            if ($serviceOffer->status !== ServiceOffer::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.offer_not_pending')
                ], 400);
            }

            // Verificar que no exista ya un acuerdo para esta oferta
            if (Agreement::where('service_offer_id', $serviceOffer->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.already_exists')
                ], 400);
            }

            DB::beginTransaction();

            $agreement = Agreement::create([
                'service_request_id' => $serviceOffer->service_request_id,
                'service_offer_id' => $serviceOffer->id,
                'client_id' => $user->id,
                'provider_id' => $serviceOffer->user_id,
                'status' => Agreement::STATUS_DRAFT,
                'terms' => $validated['terms'] ?? null,
                'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
                'version' => 1,
            ]);

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.created_success')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating agreement', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.create_error')
            ], 500);
        }
    }

    /**
     * Actualiza un acuerdo existente.
     *
     * @param Request $request
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function update(Request $request, Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede actualizar el acuerdo y solo si está en draft
            if ($agreement->client_id !== $user->id || $agreement->status !== Agreement::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_update')
                ], 403);
            }

            $validated = $request->validate([
                'terms' => 'nullable|array',
                'expires_at' => 'nullable|date|after:now'
            ]);

            $agreement->update($validated);
            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.updated_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.update_error')
            ], 500);
        }
    }

    /**
     * Envía el acuerdo al proveedor.
     *
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function send(Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede enviar el acuerdo
            if ($agreement->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_send')
                ], 403);
            }

            if (!$agreement->markAsSent()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.cannot_send')
                ], 400);
            }

            // TODO: Implementar notificación al proveedor

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.sent_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.send_error')
            ], 500);
        }
    }

    /**
     * Acepta el acuerdo (solo el proveedor).
     *
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function accept(Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el proveedor puede aceptar el contrato
            if ($agreement->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_accept')
                ], 403);
            }

            // Gating: requerir Stripe Connect onboarding completo
            if ($user->hasRole('helper')) {
                if (!($user->stripe_charges_enabled && $user->stripe_payouts_enabled)) {
                    // Intentar crear link de onboarding si hay cuenta
                    $onboardingUrl = null;
                    try {
                        if ($user->stripe_account_id) {
                            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                            $returnUrl = config('app.frontend_url') . '/connect/return';
                            $refreshUrl = config('app.frontend_url') . '/connect/refresh';
                            $link = $stripe->accountLinks->create([
                                'account' => $user->stripe_account_id,
                                'refresh_url' => $refreshUrl,
                                'return_url' => $returnUrl,
                                'type' => 'account_onboarding',
                            ]);
                            $onboardingUrl = $link->url;
                        }
                    } catch (\Throwable $e) {
                        // No bloquear si falla la generación del link; solo informar gating
                        \Log::warning('No se pudo generar account link de Connect al aceptar contrato', [
                            'user_id' => $user->id,
                            'agreement_id' => $agreement->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => __('messages.connect.gated_accept'),
                        'data' => [
                            'onboarding_url' => $onboardingUrl,
                            'charges_enabled' => (bool) $user->stripe_charges_enabled,
                            'payouts_enabled' => (bool) $user->stripe_payouts_enabled,
                        ],
                    ], 409);
                }
            }

            if (!$agreement->markAsAccepted()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.cannot_accept')
                ], 400);
            }

            // TODO: Implementar notificación al cliente

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.accepted_success')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error accepting agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.accept_error')
            ], 500);
        }
    }

    /**
     * Rechaza el acuerdo (solo el proveedor).
     *
     * @param Request $request
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function reject(Request $request, Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el proveedor puede rechazar el acuerdo
            if ($agreement->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_reject')
                ], 403);
            }

            $validated = $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);

            if (!$agreement->markAsRejected($validated['reason'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.cannot_reject')
                ], 400);
            }

            // TODO: Implementar notificación al cliente

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.rejected_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.reject_error')
            ], 500);
        }
    }

    /**
     * Cancela el acuerdo.
     *
     * @param Request $request
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function cancel(Request $request, Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente o proveedor pueden cancelar el acuerdo
            if ($agreement->client_id !== $user->id && $agreement->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_cancel')
                ], 403);
            }

            $validated = $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);

            if (!$agreement->markAsCancelled($validated['reason'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.cannot_cancel')
                ], 400);
            }

            // TODO: Implementar notificación a la otra parte

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.cancelled_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.cancel_error')
            ], 500);
        }
    }

    /**
     * Elimina un acuerdo (solo si está en draft).
     *
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function destroy(Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede eliminar el acuerdo y solo si está en draft
            if ($agreement->client_id !== $user->id || $agreement->status !== Agreement::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_delete')
                ], 403);
            }

            $agreement->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.agreements.deleted_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.delete_error')
            ], 500);
        }
    }

    /**
     * Obtiene los acuerdos donde el usuario autenticado es el cliente.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function client(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $query = Agreement::query()
                ->where('client_id', $user->id)
                ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, Agreement::STATUSES)) {
                $query->where('status', $status);
            }

            $agreements = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => AgreementResource::collection($agreements->items()),
                    'current_page' => $agreements->currentPage(),
                    'last_page' => $agreements->lastPage(),
                    'per_page' => $agreements->perPage(),
                    'total' => $agreements->total(),
                ],
                'message' => __('messages.agreements.index_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching client agreements', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.index_error')
            ], 500);
        }
    }

    /**
     * Obtiene los acuerdos donde el usuario autenticado es el proveedor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function provider(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $query = Agreement::query()
                ->where('provider_id', $user->id)
                ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, Agreement::STATUSES)) {
                $query->where('status', $status);
            }

            $agreements = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => AgreementResource::collection($agreements->items()),
                    'current_page' => $agreements->currentPage(),
                    'last_page' => $agreements->lastPage(),
                    'per_page' => $agreements->perPage(),
                    'total' => $agreements->total(),
                ],
                'message' => __('messages.agreements.index_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching provider agreements', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.index_error')
            ], 500);
        }
    }

    /**
     * Revisa un acuerdo rechazado (solo el cliente) y lo regresa a borrador con versión incrementada.
     *
     * @param Request $request
     * @param Agreement $agreement
     * @return JsonResponse
     */
    public function revise(Request $request, Agreement $agreement): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede revisar y solo si está REJECTED
            if ($agreement->client_id !== $user->id || $agreement->status !== Agreement::STATUS_REJECTED) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.unauthorized_update')
                ], 403);
            }

            $validated = $request->validate([
                'terms' => 'nullable|array',
                'revision_note' => 'nullable|string|max:500',
            ]);

            if (!$agreement->revise($validated, $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.agreements.update_error')
                ], 400);
            }

            $agreement->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new AgreementResource($agreement),
                'message' => __('messages.agreements.updated_success')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error revising agreement', [
                'error' => $e->getMessage(),
                'agreement_id' => $agreement->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.agreements.update_error')
            ], 500);
        }
    }
}