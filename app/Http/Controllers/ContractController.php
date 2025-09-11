<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\ContractResource;
use App\Models\Contract;
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
class ContractController extends Controller
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

            $query = Contract::query()
                ->where(function ($q) use ($user) {
                    $q->where('client_id', $user->id)
                      ->orWhere('provider_id', $user->id);
                })
                ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, Contract::STATUSES)) {
                $query->where('status', $status);
            }

            $contracts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => ContractResource::collection($contracts->items()),
                    'current_page' => $contracts->currentPage(),
                    'last_page' => $contracts->lastPage(),
                    'per_page' => $contracts->perPage(),
                    'total' => $contracts->total(),
                ],
                'message' => __('messages.contracts.index_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching contracts', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.index_error')
            ], 500);
        }
    }

    /**
     * Muestra un contrato específico.
     *
     * @param Contract $contract
     * @return JsonResponse
     */
    public function show(Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario tenga acceso al contrato
            if ($contract->client_id !== $user->id && $contract->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized')
                ], 403);
            }

            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider', 'payments']);

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.show_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.show_error')
            ], 500);
        }
    }

    /**
     * Crea un nuevo contrato basado en una oferta de servicio.
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
                    'message' => __('messages.contracts.unauthorized_create')
                ], 403);
            }

            // Verificar que la oferta esté pendiente
            if ($serviceOffer->status !== ServiceOffer::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.offer_not_pending')
                ], 400);
            }

            // Verificar que no exista ya un contrato para esta oferta
            if (Contract::where('service_offer_id', $serviceOffer->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.already_exists')
                ], 400);
            }

            DB::beginTransaction();

            $contract = Contract::create([
                'service_request_id' => $serviceOffer->service_request_id,
                'service_offer_id' => $serviceOffer->id,
                'client_id' => $user->id,
                'provider_id' => $serviceOffer->user_id,
                'status' => Contract::STATUS_DRAFT,
                'terms' => $validated['terms'] ?? null,
                'expires_at' => $validated['expires_at'] ?? now()->addDays(7)
            ]);

            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.created_success')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating contract', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.create_error')
            ], 500);
        }
    }

    /**
     * Actualiza un contrato existente.
     *
     * @param Request $request
     * @param Contract $contract
     * @return JsonResponse
     */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede actualizar el contrato y solo si está en draft
            if ($contract->client_id !== $user->id || $contract->status !== Contract::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized_update')
                ], 403);
            }

            $validated = $request->validate([
                'terms' => 'nullable|array',
                'expires_at' => 'nullable|date|after:now'
            ]);

            $contract->update($validated);
            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.updated_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.update_error')
            ], 500);
        }
    }

    /**
     * Envía el contrato al proveedor.
     *
     * @param Contract $contract
     * @return JsonResponse
     */
    public function send(Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede enviar el contrato
            if ($contract->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized_send')
                ], 403);
            }

            if (!$contract->markAsSent()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.cannot_send')
                ], 400);
            }

            // TODO: Implementar notificación al proveedor

            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.sent_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.send_error')
            ], 500);
        }
    }

    /**
     * Acepta el contrato (solo el proveedor).
     *
     * @param Contract $contract
     * @return JsonResponse
     */
    public function accept(Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el proveedor puede aceptar el contrato
            if ($contract->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized_accept')
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
                            'contract_id' => $contract->id,
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

            if (!$contract->markAsAccepted()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.cannot_accept')
                ], 400);
            }

            // TODO: Implementar notificación al cliente

            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.accepted_success')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error accepting contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.accept_error')
            ], 500);
        }
    }

    /**
     * Rechaza el contrato (solo el proveedor).
     *
     * @param Request $request
     * @param Contract $contract
     * @return JsonResponse
     */
    public function reject(Request $request, Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el proveedor puede rechazar el contrato
            if ($contract->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized_reject')
                ], 403);
            }

            $validated = $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);

            if (!$contract->markAsRejected($validated['reason'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.cannot_reject')
                ], 400);
            }

            // TODO: Implementar notificación al cliente

            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.rejected_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.reject_error')
            ], 500);
        }
    }

    /**
     * Cancela el contrato.
     *
     * @param Request $request
     * @param Contract $contract
     * @return JsonResponse
     */
    public function cancel(Request $request, Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente o proveedor pueden cancelar el contrato
            if ($contract->client_id !== $user->id && $contract->provider_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized_cancel')
                ], 403);
            }

            $validated = $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);

            if (!$contract->markAsCancelled($validated['reason'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.cannot_cancel')
                ], 400);
            }

            // TODO: Implementar notificación a la otra parte

            $contract->load(['serviceRequest', 'serviceOffer', 'client', 'provider']);

            return response()->json([
                'success' => true,
                'data' => new ContractResource($contract),
                'message' => __('messages.contracts.cancelled_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.cancel_error')
            ], 500);
        }
    }

    /**
     * Elimina un contrato (solo si está en draft).
     *
     * @param Contract $contract
     * @return JsonResponse
     */
    public function destroy(Contract $contract): JsonResponse
    {
        try {
            $user = Auth::user();

            // Solo el cliente puede eliminar el contrato y solo si está en draft
            if ($contract->client_id !== $user->id || $contract->status !== Contract::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.contracts.unauthorized_delete')
                ], 403);
            }

            $contract->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.contracts.deleted_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting contract', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.delete_error')
            ], 500);
        }
    }

    /**
     * Obtiene los contratos donde el usuario autenticado es el cliente.
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

            $query = Contract::query()
                ->where('client_id', $user->id)
                ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, Contract::STATUSES)) {
                $query->where('status', $status);
            }

            $contracts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => ContractResource::collection($contracts->items()),
                    'current_page' => $contracts->currentPage(),
                    'last_page' => $contracts->lastPage(),
                    'per_page' => $contracts->perPage(),
                    'total' => $contracts->total(),
                ],
                'message' => __('messages.contracts.index_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching client contracts', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.index_error')
            ], 500);
        }
    }

    /**
     * Obtiene los contratos donde el usuario autenticado es el proveedor.
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

            $query = Contract::query()
                ->where('provider_id', $user->id)
                ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, Contract::STATUSES)) {
                $query->where('status', $status);
            }

            $contracts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => ContractResource::collection($contracts->items()),
                    'current_page' => $contracts->currentPage(),
                    'last_page' => $contracts->lastPage(),
                    'per_page' => $contracts->perPage(),
                    'total' => $contracts->total(),
                ],
                'message' => __('messages.contracts.index_success')
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching provider contracts', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.contracts.index_error')
            ], 500);
        }
    }
}