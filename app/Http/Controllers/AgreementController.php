<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\AgreementResource;
use App\Models\Agreement;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Services\TransactionService;
use App\Services\PaymentService;
use App\Services\EscrowService;
use App\Http\Resources\User\UserTransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

/**
 * Controlador para gestionar contratos entre clientes y proveedores de servicios.
 */
class AgreementController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private PaymentService $paymentService,
        private EscrowService $escrowService
    ) {}
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

    /**
     * Get financial summary for an agreement.
     */
    public function financialSummary(Agreement $agreement): JsonResponse
    {
        $this->authorize('view', $agreement);
        
        $summary = $agreement->getFinancialSummary();
        
        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
    
    /**
     * Get all transactions related to an agreement.
     */
    public function transactions(Agreement $agreement): JsonResponse
    {
        $this->authorize('view', $agreement);
        
        $transactions = $agreement->transactions()
            ->with(['user', 'parentTransaction', 'childTransactions'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
                'success' => true,
                'data' => UserTransactionResource::collection($transactions),
            ]);
    }
    
    /**
     * Get payment transactions for an agreement.
     */
    public function paymentTransactions(Agreement $agreement): JsonResponse
    {
        $this->authorize('view', $agreement);
        
        $transactions = $agreement->paymentTransactions()
            ->with(['user', 'parentTransaction', 'childTransactions'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
                'success' => true,
                'data' => UserTransactionResource::collection($transactions),
            ]);
    }
    
    /**
     * Get refund transactions for an agreement.
     */
    public function refundTransactions(Agreement $agreement): JsonResponse
    {
        $this->authorize('view', $agreement);
        
        $transactions = $agreement->refundTransactions()
            ->with(['user', 'parentTransaction', 'childTransactions'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
                'success' => true,
                'data' => UserTransactionResource::collection($transactions),
            ]);
    }
    
    /**
     * Process payment for an agreement.
     */
    public function processPayment(Request $request, Agreement $agreement): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:stripe,paypal,bank_transfer',
            'payment_intent_id' => 'nullable|string',
        ]);
        
        $this->authorize('pay', $agreement);
        
        try {
            $result = $this->paymentService->processAgreementPayment(
                $agreement,
                $request->amount,
                $request->payment_method,
                $request->payment_intent_id
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_transaction' => new UserTransactionResource($result['payment_transaction']),
                    'commission_transaction' => new UserTransactionResource($result['commission_transaction']),
                    'transaction_group_id' => $result['transaction_group_id'],
                    'net_amount_to_helper' => $result['net_amount_to_helper'],
                ],
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Hold funds in escrow for an agreement.
     */
    public function holdFunds(Request $request, Agreement $agreement): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'hold_until' => 'nullable|date|after:now',
            'reason' => 'nullable|string|max:500',
        ]);
        
        $this->authorize('holdFunds', $agreement);
        
        try {
            $result = $this->escrowService->holdFunds(
                $agreement->client,
                $request->amount,
                $agreement,
                $request->hold_until,
                $request->reason
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Funds held in escrow successfully',
                'data' => [
                    'escrow_transaction' => new UserTransactionResource($result['escrow_transaction']),
                    'hold_until' => $result['hold_until'],
                    'escrow_balance' => $result['escrow_balance'],
                ],
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hold funds: ' . $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Release funds from escrow for an agreement.
     */
    public function releaseFunds(Request $request, Agreement $agreement): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);
        
        $this->authorize('releaseFunds', $agreement);
        
        try {
            $result = $this->escrowService->releaseFunds(
                $agreement->helper,
                $request->amount,
                $agreement,
                $request->reason
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Funds released from escrow successfully',
                'data' => [
                    'release_transaction' => new UserTransactionResource($result['release_transaction']),
                    'remaining_escrow_balance' => $result['remaining_escrow_balance'],
                ],
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to release funds: ' . $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Get escrow balance for an agreement.
     */
    public function escrowBalance(Agreement $agreement): JsonResponse
    {
        $this->authorize('view', $agreement);
        
        $balance = $this->escrowService->getEscrowBalance($agreement->client, $agreement);
        
        return response()->json([
            'success' => true,
            'data' => [
                'escrow_balance' => $balance,
                'formatted_balance' => '$' . number_format($balance, 2),
            ],
        ]);
    }
    
    /**
     * Get payment status for an agreement.
     */
    public function paymentStatus(Agreement $agreement): JsonResponse
    {
        $this->authorize('view', $agreement);
        
        $status = $agreement->getPaymentStatus();
        
        return response()->json([
            'success' => true,
            'data' => [
                'payment_status' => $status,
                'can_be_paid' => $agreement->canBePaid(),
                'total_paid' => $agreement->getTotalPaid(),
                'total_refunded' => $agreement->getTotalRefunded(),
                'net_amount' => $agreement->getNetAmount(),
                'commission_amount' => $agreement->getCommissionAmount(),
                'is_using_transactions' => $agreement->isUsingTransactions(),
            ],
        ]);
    }
}