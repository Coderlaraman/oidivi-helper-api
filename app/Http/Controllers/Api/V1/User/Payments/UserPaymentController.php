<?php

namespace App\Http\Controllers\Api\V1\User\Payments;

use App\Http\Controllers\Controller;
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

            DB::beginTransaction();

            // Crear el registro de pago
            $payment = Payment::create([
                'service_request_id' => $offer->service_request_id,
                'service_offer_id' => $offer->id,
                'payer_user_id' => auth()->id(),
                'payee_user_id' => $offer->user_id,
                'amount' => $offer->price_proposed,
                'currency' => 'USD',
                'status' => Payment::STATUS_PENDING,
            ]);

            // Crear sesión de Stripe Checkout
            $session = StripeSession::create([
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
     * Confirma el pago exitoso y actualiza los estados correspondientes.
     *
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function confirmPayment(Request $request, Payment $payment): JsonResponse
    {
        try {
            $sessionId = $request->input('session_id');

            if (!$sessionId) {
                return $this->errorResponse(
                    message: 'Session ID requerido',
                    statusCode: 400
                );
            }

            // Verificar que el usuario autenticado es el que realizó el pago
            if ($payment->payer_user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'No tienes permisos para confirmar este pago',
                    statusCode: 403
                );
            }

            // Atajo de desarrollo: permitir forzar confirmación en entornos local/testing
            if ((app()->environment(['local', 'testing']) || config('app.debug')) && $request->boolean('dev_force')) {
                $result = $this->finalizeSuccessfulPayment($payment, null, $sessionId);

                Log::warning('Payment confirmed via dev_force (development shortcut)', [
                    'payment_id' => $payment->id,
                    'stripe_session_id' => $sessionId,
                    'user_id' => auth()->id(),
                ]);

                return $this->successResponse([
                    'payment' => $result['payment']->load(['serviceRequest', 'serviceOffer.user']),
                    'service_request' => $result['service_request'],
                    'service_offer' => $result['offer']->load('user'),
                    'redirect_url' => '/service-requests/' . $result['service_request']->id,
                ], 'Pago confirmado en modo desarrollo');
            }

            // Verificar el estado del pago en Stripe
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                // Finalizar pago de forma segura e idempotente
                $result = $this->finalizeSuccessfulPayment($payment, $session->payment_intent, $sessionId);

                Log::info('Payment confirmed successfully', [
                    'payment_id' => $payment->id,
                    'stripe_session_id' => $sessionId,
                ]);

                return $this->successResponse([
                    'payment' => $result['payment']->load(['serviceRequest', 'serviceOffer.user']),
                    'service_request' => $result['service_request'],
                    'service_offer' => $result['offer']->load('user'),
                    'redirect_url' => '/service-requests/' . $result['service_request']->id,
                ], 'Pago confirmado exitosamente');
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

                    // Solo finalizar si aún no está completado/cancelado/refundado
                    if (in_array($payment->status, [Payment::STATUS_COMPLETED, Payment::STATUS_CANCELED, Payment::STATUS_REFUNDED], true)) {
                        Log::info('Evento idempotente ignorado (ya finalizado)');
                        break;
                    }

                    if (($session['payment_status'] ?? null) === 'paid') {
                        $this->finalizeSuccessfulPayment($payment, $paymentIntentId, $stripeSessionId);
                    }
                    break;

                case 'payment_intent.succeeded':
                    $pi = $dataObject; // \Stripe\PaymentIntent
                    $paymentIntentId = $pi['id'] ?? null;
                    $paymentId = $pi['metadata']['payment_id'] ?? null;

                    $payment = null;
                    if ($paymentId) {
                        $payment = Payment::query()->where('id', $paymentId)->first();
                    }
                    if (!$payment && $paymentIntentId) {
                        $payment = Payment::query()->where('stripe_payment_intent_id', $paymentIntentId)->first();
                    }

                    if ($payment && !in_array($payment->status, [Payment::STATUS_COMPLETED, Payment::STATUS_CANCELED, Payment::STATUS_REFUNDED], true)) {
                        $this->finalizeSuccessfulPayment($payment, $paymentIntentId, null);
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $pi = $dataObject;
                    $paymentIntentId = $pi['id'] ?? null;
                    if ($paymentIntentId) {
                        $payment = Payment::query()->where('stripe_payment_intent_id', $paymentIntentId)->first();
                        if ($payment) {
                            $payment->update(['status' => Payment::STATUS_FAILED]);
                            Log::info('Pago marcado como fallido', ['payment_id' => $payment->id]);
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

            $payment->update(['status' => Payment::STATUS_CANCELED]);

            Log::info('Payment canceled', [
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
            ]);

            return $this->successResponse([
                'redirect_url' => '/service-requests/' . $payment->service_request_id,
            ], 'Pago cancelado');

        } catch (Exception $e) {
            Log::error('Error canceling payment', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id ?? null,
            ]);

            return $this->errorResponse(
                message: 'Error al cancelar el pago',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista los pagos del usuario autenticado (como pagador o receptor).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();

            $query = Payment::query()
                ->where(function ($q) use ($userId) {
                    $q->where('payer_user_id', $userId)
                      ->orWhere('payee_user_id', $userId);
                })
                ->with(['serviceRequest', 'serviceOffer.user', 'payer', 'payee']);

            // Filtro por estado
            if ($request->filled('status')) {
                $query->where('status', $request->query('status'));
            }

            // Ordenamiento
            $sortBy = $request->query('sort_by', 'created_at');
            $sortDirection = strtolower($request->query('sort_direction', 'desc'));
            $allowedSort = ['created_at', 'amount'];
            if (!in_array($sortBy, $allowedSort, true)) {
                $sortBy = 'created_at';
            }
            if (!in_array($sortDirection, ['asc', 'desc'], true)) {
                $sortDirection = 'desc';
            }
            $query->orderBy($sortBy, $sortDirection);

            // Paginación opcional (el frontend espera un array en data)
            $perPage = (int) $request->query('per_page', 0);
            if ($perPage > 0) {
                $paginator = $query->simplePaginate($perPage);
                $items = collect($paginator->items());
            } else {
                $items = $query->get();
            }

            return $this->successResponse($items->toArray(), 'Pagos obtenidos correctamente');
        } catch (Exception $e) {
            Log::error('Error listing payments', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse(
                message: 'Error al obtener los pagos',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Muestra un pago específico del usuario autenticado.
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        try {
            $userId = auth()->id();
            if ($payment->payer_user_id !== $userId && $payment->payee_user_id !== $userId) {
                return $this->errorResponse(
                    message: 'No tienes permisos para ver este pago',
                    statusCode: 403
                );
            }

            $payment->load(['serviceRequest', 'serviceOffer.user', 'payer', 'payee']);

            return $this->successResponse($payment->toArray(), 'Pago obtenido correctamente');
        } catch (Exception $e) {
            Log::error('Error getting payment', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id ?? null,
            ]);

            return $this->errorResponse(
                message: 'Error al obtener el pago',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Marca pago como completado y actualiza oferta y solicitud (idempotente).
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

            if (in_array($payment->status, [Payment::STATUS_COMPLETED, Payment::STATUS_CANCELED, Payment::STATUS_REFUNDED], true)) {
                // Ya finalizado, devolver estado actual
                $offer = $payment->serviceOffer;
                $serviceRequest = $payment->serviceRequest;
                return [
                    'payment' => $payment,
                    'offer' => $offer,
                    'service_request' => $serviceRequest,
                ];
            }

            // Actualizar pago como completado
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'stripe_payment_intent_id' => $stripePaymentIntentId ?? $payment->stripe_payment_intent_id,
                'stripe_session_id' => $stripeSessionId ?? $payment->stripe_session_id,
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            // Actualizar oferta y solicitud
            $offer = $payment->serviceOffer;
            $serviceRequest = $payment->serviceRequest;

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