<?php

namespace App\Http\Controllers\Api\V1\User\Payments;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook as StripeWebhook;
use Exception;
use Stripe\Customer as StripeCustomer;
use Stripe\Transfer as StripeTransfer;
use Stripe\Refund as StripeRefund;

class UserPaymentController extends Controller
{
    public function __construct()
    {
        // Configurar Stripe con la clave secreta
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crea una sesión de pago de Stripe para una oferta aceptada.
     *
     * @param Request $request
     * @param ServiceOffer $offer
     * @return JsonResponse
     */
    public function createPaymentSession(Request $request, ServiceOffer $offer): JsonResponse
    {
        try {
            // Verificar que el usuario autenticado es el dueño de la solicitud
            if (!$offer->serviceRequest || $offer->serviceRequest->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'No tienes permisos para realizar este pago',
                    statusCode: 403
                );
            }

            // Verificar que la oferta está en estado pendiente
            if ($offer->status !== ServiceOffer::STATUS_PENDING) {
                return $this->errorResponse(
                    message: 'Esta oferta ya no está disponible para pago',
                    statusCode: 400
                );
            }

            // Verificar que existe un contrato aceptado para esta oferta
            $contract = Contract::where('service_offer_id', $offer->id)
                ->where('status', Contract::STATUS_ACCEPTED)
                ->first();

            if (!$contract) {
                return $this->errorResponse(
                    message: 'Debe existir un contrato aceptado antes de procesar el pago',
                    statusCode: 400
                );
            }

            // Verificar que el usuario autenticado es el cliente del contrato
            if ($contract->client_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'No tienes permisos para pagar este contrato',
                    statusCode: 403
                );
            }

            // Asegurar/crear el Customer de Stripe para el pagador y guardarlo
            $user = auth()->user();
            if (empty($user->stripe_customer_id)) {
                $customer = StripeCustomer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'metadata' => [
                        'app_user_id' => (string) $user->id,
                    ],
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            DB::beginTransaction();

            // Crear el registro de pago
            $payment = Payment::create([
                'service_request_id' => $offer->service_request_id,
                'service_offer_id' => $offer->id,
                'contract_id' => $contract->id,
                'payer_user_id' => auth()->id(),
                'payee_user_id' => $offer->user_id,
                'amount' => $offer->price_proposed,
                'currency' => 'USD',
                'status' => Payment::STATUS_PENDING,
            ]);

            // Crear sesión de Stripe Checkout (cobro en la cuenta de plataforma)
            $session = StripeSession::create([
                'customer' => $user->stripe_customer_id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Pago por servicio: ' . $offer->serviceRequest->title,
                            'description' => 'Pago para la oferta de ' . $offer->user->name,
                        ],
                        'unit_amount' => intval($offer->price_proposed * 100), // Stripe usa centavos
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => config('app.frontend_url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}&payment_id=' . $payment->id,
                'cancel_url' => config('app.frontend_url') . '/payment/cancel?payment_id=' . $payment->id,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'service_request_id' => $offer->service_request_id,
                    'service_offer_id' => $offer->id,
                    'payer_user_id' => auth()->id(),
                    'payee_user_id' => $offer->user_id,
                ],
                'payment_intent_data' => [
                    'setup_future_usage' => 'off_session', // guardar tarjeta para futuros cobros
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'service_request_id' => $offer->service_request_id,
                        'service_offer_id' => $offer->id,
                        'payer_user_id' => auth()->id(),
                        'payee_user_id' => $offer->user_id,
                    ],
                ],
            ]);

            // Actualizar el pago con los datos de Stripe
            $payment->update([
                'stripe_session_id' => $session->id,
                'stripe_metadata' => [
                    'session_id' => $session->id,
                    'checkout_url' => $session->url,
                ],
            ]);

            DB::commit();

            Log::info('Payment session created successfully', [
                'payment_id' => $payment->id,
                'stripe_session_id' => $session->id,
                'offer_id' => $offer->id,
                'stripe_customer_id' => $user->stripe_customer_id,
            ]);

            return $this->successResponse([
                'payment_id' => $payment->id,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ], 'Sesión de pago creada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating payment session', [
                'error' => $e->getMessage(),
                'offer_id' => $offer->id,
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse(
                message: 'Error al crear la sesión de pago',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Confirma un pago exitoso usando la sesión de Stripe.
     *
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function confirmPayment(Request $request, Payment $payment): JsonResponse
    {
        try {
            // Verificar que el usuario autenticado es el que realizó el pago
            if ($payment->payer_user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'No tienes permisos para confirmar este pago',
                    statusCode: 403
                );
            }

            $sessionId = $request->query('session_id');

            if (!$sessionId) {
                // Si no se proporciona el session_id, intentar con el registrado
                $sessionId = $payment->stripe_session_id;
            }

            if (!$sessionId) {
                return $this->errorResponse(
                    message: 'No se pudo obtener la sesión de pago',
                    statusCode: 400
                );
            }

            // Verificar el estado del pago en Stripe
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                // Finalizar pago de forma segura e idempotente (marcar HELD)
                $result = $this->finalizeSuccessfulPayment($payment, $session->payment_intent, $sessionId);

                Log::info('Payment confirmed successfully (held)', [
                    'payment_id' => $payment->id,
                    'stripe_session_id' => $sessionId,
                ]);

                return $this->successResponse([
                    'payment' => $result['payment']->load(['serviceRequest', 'serviceOffer.user', 'contract']),
                    'service_request' => $result['service_request'],
                    'service_offer' => $result['offer']->load('user'),
                    'redirect_url' => '/service-requests/' . $result['service_request']->id,
                ], 'Pago confirmado y fondos retenidos');
            } else {
                return $this->errorResponse(
                    message: 'El pago no ha sido completado',
                    statusCode: 400
                );
            }

        } catch (Exception $e) {
            Log::error('Error confirming payment', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId ?? null,
                'payment_id' => $payment->id ?? null,
            ]);

            return $this->errorResponse(
                message: 'Error al confirmar el pago',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Webhook público para eventos de Stripe.
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        if (!$endpointSecret) {
            Log::error('Stripe webhook secret no configurado');
            return response()->json(['success' => false, 'message' => 'Config error'], 500);
        }

        try {
            $event = StripeWebhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::warning('Stripe webhook payload inválido', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::warning('Stripe webhook firma inválida', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        $type = $event['type'] ?? null;
        $dataObject = $event['data']['object'] ?? null;

        try {
            switch ($type) {
                case 'checkout.session.completed':
                    $session = $dataObject; // \Stripe\Checkout\Session
                    $paymentId = $session['metadata']['payment_id'] ?? null;
                    $stripeSessionId = $session['id'] ?? null;
                    $paymentIntentId = $session['payment_intent'] ?? null;

                    if (!$paymentId && !$stripeSessionId) {
                        Log::warning('checkout.session.completed sin identificadores');
                        break;
                    }

                    $payment = null;
                    if ($paymentId) {
                        $payment = Payment::query()->where('id', $paymentId)->first();
                    }
                    if (!$payment && $stripeSessionId) {
                        $payment = Payment::query()->where('stripe_session_id', $stripeSessionId)->first();
                    }

                    if (!$payment) {
                        Log::error('Pago no encontrado para checkout.session.completed', [
                            'payment_id' => $paymentId,
                            'stripe_session_id' => $stripeSessionId,
                        ]);
                        break;
                    }

                    // Solo finalizar si aún no está completado/cancelado/refundado/held
                    if (in_array($payment->status, [Payment::STATUS_COMPLETED, Payment::STATUS_CANCELED, Payment::STATUS_REFUNDED, Payment::STATUS_HELD], true)) {
                        Log::info('Evento idempotente ignorado (ya finalizado/held)');
                        break;
                    }

                    if (($session['payment_status'] ?? null) === 'paid') {
                        $this->finalizeSuccessfulPayment($payment, $paymentIntentId, $stripeSessionId);
                    }
                    break;

                case 'payment_intent.succeeded':
                    $pi = $dataObject; // \Stripe\PaymentIntent
                    $paymentIntentId = $pi['id'] ?? null;
                    if ($paymentIntentId) {
                        $payment = Payment::query()->where('stripe_payment_intent_id', $paymentIntentId)->first();
                        if ($payment && !in_array($payment->status, [Payment::STATUS_COMPLETED, Payment::STATUS_CANCELED, Payment::STATUS_REFUNDED, Payment::STATUS_HELD], true)) {
                            $this->finalizeSuccessfulPayment($payment, $paymentIntentId, null);
                        }
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $pi = $dataObject;
                    $paymentIntentId = $pi['id'] ?? null;
                    if ($paymentIntentId) {
                        $payment = Payment::query()->where('stripe_payment_intent_id', $paymentIntentId)->first();
                        if ($payment) {
                            $payment->update(['status' => Payment::STATUS_FAILED]);
                            Log::info('Payment marked as failed from webhook', ['payment_id' => $payment->id]);
                        }
                    }
                    break;

                case 'checkout.session.expired':
                    $session = $dataObject;
                    $stripeSessionId = $session['id'] ?? null;
                    if ($stripeSessionId) {
                        $payment = Payment::query()->where('stripe_session_id', $stripeSessionId)->first();
                        if ($payment && $payment->status === Payment::STATUS_PENDING) {
                            $payment->update(['status' => Payment::STATUS_CANCELED]);
                            Log::info('Pago cancelado por sesión expirada', ['payment_id' => $payment->id]);
                        }
                    }
                    break;

                default:
                    // Eventos no manejados explícitamente
                    Log::debug('Stripe webhook evento no manejado', ['type' => $type]);
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error procesando webhook de Stripe', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Webhook processing error'], 500);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Maneja la cancelación del pago.
     *
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function cancelPayment(Request $request, Payment $payment): JsonResponse
    {
        try {
            // Verificar que el usuario autenticado es el que realizó el pago
            if ($payment->payer_user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'No tienes permisos para cancelar este pago',
                    statusCode: 403
                );
            }

            // Si ya fue reembolsado o cancelado, responder idempotentemente
            if (in_array($payment->status, [Payment::STATUS_REFUNDED, Payment::STATUS_CANCELED], true)) {
                return $this->successResponse([
                    'payment' => $payment->load(['serviceRequest', 'serviceOffer.user', 'contract'])
                ], 'Pago ya cancelado/reembolsado');
            }

            // No permitir reembolso si los fondos ya fueron liberados
            if ($payment->status === Payment::STATUS_RELEASED) {
                return $this->errorResponse('No se puede reembolsar un pago ya liberado', 400);
            }

            // Requiere Payment Intent para reembolsar
            if (!$payment->stripe_payment_intent_id) {
                return $this->errorResponse('No se puede reembolsar: falta payment_intent', 400);
            }

            // Crear refund en Stripe
            $reason = $request->input('reason');
            $refund = StripeRefund::create(array_filter([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'reason' => $reason,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'contract_id' => $payment->contract_id,
                    'service_request_id' => $payment->service_request_id,
                    'service_offer_id' => $payment->service_offer_id,
                    'payer_user_id' => $payment->payer_user_id,
                    'payee_user_id' => $payment->payee_user_id,
                ],
            ]));

            // Actualizar estado local del pago y campos de refund
            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'stripe_metadata' => array_merge($payment->stripe_metadata ?? [], [
                    'refund_id' => $refund->id,
                ]),
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            // Marcar solicitud como cancelada si estaba en progreso
            $serviceRequest = $payment->serviceRequest;
            if ($serviceRequest && $serviceRequest->status === ServiceRequest::STATUS_IN_PROGRESS) {
                $serviceRequest->update([
                    'status' => ServiceRequest::STATUS_CANCELED,
                ]);
            }

            Log::info('Payment refunded and canceled', [
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'refund_id' => $refund->id,
            ]);

            return $this->successResponse([
                'payment' => $payment->load(['serviceRequest', 'serviceOffer.user', 'contract'])
            ], 'Pago reembolsado correctamente');

        } catch (Exception $e) {
            Log::error('Error canceling/refunding payment', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id ?? null,
            ]);

            return $this->errorResponse(
                message: 'Error al cancelar/reembolsar el pago',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista los pagos relacionados al usuario autenticado (como pagador o receptor).
     * Filtros opcionales: role=payer|payee, status=<status>, per_page=<n>
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $query = Payment::query()->with(['serviceRequest', 'serviceOffer.user', 'contract']);

        $role = $request->query('role');
        if ($role === 'payer') {
            $query->where('payer_user_id', $userId);
        } elseif ($role === 'payee') {
            $query->where('payee_user_id', $userId);
        } else {
            $query->where(function ($q) use ($userId) {
                $q->where('payer_user_id', $userId)
                  ->orWhere('payee_user_id', $userId);
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 15;

        $payments = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->successResponse([
            'payments' => $payments,
        ], 'Listado de pagos');
    }

    /**
     * Muestra el detalle de un pago. Solo accesible para el pagador o el receptor.
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        $userId = auth()->id();
        if ($payment->payer_user_id !== $userId && $payment->payee_user_id !== $userId) {
            return $this->errorResponse('No tienes permisos para ver este pago', 403);
        }

        return $this->successResponse([
            'payment' => $payment->load(['serviceRequest', 'serviceOffer.user', 'contract'])
        ], 'Detalle de pago');
    }

    /**
     * Inicia el proceso de pago recibiendo offer_id y redirigiendo a createPaymentSession.
     * Útil para clientes que aún no tienen la ruta con parámetro de oferta.
     */
    public function initiatePayment(Request $request): JsonResponse
    {
        $offerId = $request->input('offer_id');
        if (!$offerId) {
            return $this->errorResponse('offer_id es requerido', 422);
        }

        $offer = ServiceOffer::find($offerId);
        if (!$offer) {
            return $this->errorResponse('Oferta no encontrada', 404);
        }

        return $this->createPaymentSession($request, $offer);
    }

    /**
     * Libera fondos retenidos hacia el helper (Transfer a cuenta conectada) y completa el contrato.
     */
    public function releaseFunds(Request $request, Payment $payment): JsonResponse
    {
        try {
            // Permitir solo al pagador
            if ($payment->payer_user_id !== auth()->id()) {
                return $this->errorResponse('No tienes permisos para liberar fondos de este pago', 403);
            }

            // El pago debe estar retenido
            if ($payment->status !== Payment::STATUS_HELD) {
                if ($payment->status === Payment::STATUS_RELEASED) {
                    return $this->successResponse([
                        'payment' => $payment->load(['serviceRequest', 'serviceOffer.user', 'contract'])
                    ], 'Pago ya liberado');
                }
                return $this->errorResponse('El pago no está en estado retenido', 400);
            }

            $helper = $payment->payee;
            if (!$helper || empty($helper->stripe_account_id)) {
                return $this->errorResponse('El proveedor no tiene una cuenta Stripe conectada', 400);
            }
            if (!$helper->stripe_payouts_enabled) {
                return $this->errorResponse('La cuenta del proveedor no tiene payouts habilitados', 400);
            }

            $platformFeePercent = $payment->platform_fee_percent ?? (int) (config('services.stripe.platform_fee_percent') ?? env('PLATFORM_FEE_PERCENT', 15));
            $platformFeeAmount = $payment->platform_fee_amount ?? round(($payment->amount * $platformFeePercent) / 100, 2);
            $netAmount = max(0, round($payment->amount - $platformFeeAmount, 2));

            if ($netAmount <= 0) {
                return $this->errorResponse('El monto neto a transferir es inválido', 400);
            }

            // Crear transferencia desde la cuenta de plataforma al helper
            $transfer = StripeTransfer::create([
                'amount' => intval(round($netAmount * 100)),
                'currency' => strtolower($payment->currency ?? 'USD'),
                'destination' => $helper->stripe_account_id,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'contract_id' => $payment->contract_id,
                    'service_request_id' => $payment->service_request_id,
                    'service_offer_id' => $payment->service_offer_id,
                    'payer_user_id' => $payment->payer_user_id,
                    'payee_user_id' => $payment->payee_user_id,
                ],
            ]);

            // Actualizar estados y campos del pago
            $payment->update([
                'status' => Payment::STATUS_RELEASED,
                'released_at' => now(),
                'stripe_transfer_id' => $transfer->id,
                'platform_fee_percent' => $platformFeePercent,
                'platform_fee_amount' => $platformFeeAmount,
            ]);

            // Actualizar contrato y solicitud a COMPLETED
            $contract = $payment->contract;
            if ($contract && $contract->status !== Contract::STATUS_COMPLETED) {
                $contract->update([
                    'status' => Contract::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            }

            $serviceRequest = $payment->serviceRequest;
            if ($serviceRequest && $serviceRequest->status !== ServiceRequest::STATUS_COMPLETED) {
                $serviceRequest->update([
                    'status' => ServiceRequest::STATUS_COMPLETED,
                ]);
            }

            Log::info('Funds released to helper', [
                'payment_id' => $payment->id,
                'transfer_id' => $transfer->id,
                'net_amount' => $netAmount,
                'platform_fee_percent' => $platformFeePercent,
                'platform_fee_amount' => $platformFeeAmount,
            ]);

            return $this->successResponse([
                'payment' => $payment->load(['serviceRequest', 'serviceOffer.user', 'contract'])
            ], 'Fondos liberados exitosamente');
        } catch (Exception $e) {
            Log::error('Error releasing funds', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id ?? null,
            ]);

            return $this->errorResponse('Error al liberar fondos', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Marca pago como HELD (escrow) e inicializa progreso de la solicitud (idempotente).
     *
     * @param Payment $payment
     * @param string|null $stripePaymentIntentId
     * @param string|null $stripeSessionId
     * @return array{payment: Payment, offer: ServiceOffer, service_request: ServiceRequest}
     */
    private function finalizeSuccessfulPayment(Payment $payment, ?string $stripePaymentIntentId = null, ?string $stripeSessionId = null): array
    {
        return DB::transaction(function () use ($payment, $stripePaymentIntentId, $stripeSessionId) {
            // Refrescar y bloquear lógicamente el registro
            $payment->refresh();

            if (in_array($payment->status, [Payment::STATUS_COMPLETED, Payment::STATUS_CANCELED, Payment::STATUS_REFUNDED, Payment::STATUS_HELD], true)) {
                // Ya finalizado, devolver estado actual
                $offer = $payment->serviceOffer;
                $serviceRequest = $payment->serviceRequest;
                return [
                    'payment' => $payment,
                    'offer' => $offer,
                    'service_request' => $serviceRequest,
                ];
            }

            // Calcular fee de plataforma (si no existe aún)
            $platformFeePercent = $payment->platform_fee_percent ?? (int) (config('services.stripe.platform_fee_percent') ?? env('PLATFORM_FEE_PERCENT', 15));
            $platformFeeAmount = $payment->platform_fee_amount ?? round(($payment->amount * $platformFeePercent) / 100, 2);

            // Actualizar pago como HELD (fondos retenidos)
            $payment->update([
                'status' => Payment::STATUS_HELD,
                'stripe_payment_intent_id' => $stripePaymentIntentId ?? $payment->stripe_payment_intent_id,
                'stripe_session_id' => $stripeSessionId ?? $payment->stripe_session_id,
                'paid_at' => $payment->paid_at ?? now(),
                'platform_fee_percent' => $platformFeePercent,
                'platform_fee_amount' => $platformFeeAmount,
            ]);

            // Actualizar oferta, solicitud (in progress) y NO completar el contrato aún
            $offer = $payment->serviceOffer;
            $serviceRequest = $payment->serviceRequest;
            $contract = $payment->contract;

            if ($offer && $offer->status !== ServiceOffer::STATUS_ACCEPTED) {
                $offer->update(['status' => ServiceOffer::STATUS_ACCEPTED]);
            }

            if ($serviceRequest && $serviceRequest->status !== ServiceRequest::STATUS_IN_PROGRESS) {
                $serviceRequest->update([
                    'status' => ServiceRequest::STATUS_IN_PROGRESS,
                    'assigned_helper_id' => $payment->payee_user_id,
                    'started_at' => $serviceRequest->started_at ?? now(),
                ]);
            } else if ($serviceRequest) {
                // Asegurar asignación aunque ya esté en progreso
                $serviceRequest->update([
                    'assigned_helper_id' => $serviceRequest->assigned_helper_id ?? $payment->payee_user_id,
                    'started_at' => $serviceRequest->started_at ?? now(),
                ]);
            }

            // Mantener contrato en ACCEPTED hasta liberar fondos
            if ($contract && $contract->status === Contract::STATUS_ACCEPTED) {
                // Sin cambios de estado aquí
            }

            // Rechazar otras ofertas
            if ($serviceRequest && $offer) {
                $serviceRequest
                    ->offers()
                    ->where('id', '!=', $offer->id)
                    ->update(['status' => ServiceOffer::STATUS_REJECTED]);
            }

            // Notificaciones
            if ($offer) {
                $offer->notifyOfferAccepted();
                if ($serviceRequest) {
                    foreach ($serviceRequest->offers()->where('id', '!=', $offer->id)->get() as $declinedOffer) {
                        if ($declinedOffer->user) {
                            $declinedOffer->notifyStatusUpdate();
                        }
                    }
                }
            }

            return [
                'payment' => $payment,
                'offer' => $offer,
                'service_request' => $serviceRequest,
            ];
        });
    }

    /**
     * Respuesta de error estandarizada.
     */
    private function errorResponse(string $message, int $statusCode, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Respuesta de éxito estandarizada.
     */
    private function successResponse(array $data = [], string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}