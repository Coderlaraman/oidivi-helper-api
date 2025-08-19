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

            // Verificar el estado del pago en Stripe
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                DB::beginTransaction();

                // Actualizar el pago como completado
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'paid_at' => now(),
                ]);

                // Actualizar los estados de la oferta y solicitud
                $offer = $payment->serviceOffer;
                $serviceRequest = $payment->serviceRequest;

                $offer->update(['status' => ServiceOffer::STATUS_ACCEPTED]);
                $serviceRequest->update(['status' => ServiceRequest::STATUS_IN_PROGRESS]);

                // Rechazar las demás ofertas
                $serviceRequest
                    ->offers()
                    ->where('id', '!=', $offer->id)
                    ->update(['status' => ServiceOffer::STATUS_REJECTED]);

                // Notificar a los usuarios correspondientes
                $offer->notifyOfferAccepted();
                
                foreach ($serviceRequest->offers()->where('id', '!=', $offer->id)->get() as $declinedOffer) {
                    if ($declinedOffer->user) {
                        $declinedOffer->notifyStatusUpdate();
                    }
                }

                DB::commit();

                Log::info('Payment confirmed successfully', [
                    'payment_id' => $payment->id,
                    'stripe_session_id' => $sessionId,
                ]);

                return $this->successResponse([
                    'payment' => $payment->load(['serviceRequest', 'serviceOffer.user']),
                    'service_request' => $serviceRequest,
                    'service_offer' => $offer->load('user'),
                    'redirect_url' => '/service-requests/' . $serviceRequest->id,
                ], 'Pago confirmado exitosamente');
            } else {
                return $this->errorResponse(
                    message: 'El pago no ha sido completado',
                    statusCode: 400
                );
            }

        } catch (Exception $e) {
            if (isset($payment)) {
                DB::rollBack();
            }
            
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