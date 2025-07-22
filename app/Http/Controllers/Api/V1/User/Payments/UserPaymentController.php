<?php

namespace App\Http\Controllers\Api\V1\User\Payments;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use App\Models\Contract;
use App\Models\Transaction;
use App\Models\PaymentLog;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class UserPaymentController extends Controller
{
    use ApiResponseTrait;

    public function initiatePayment(Request $request)
    {
        $request->validate([
            'service_offer_id' => 'required|exists:service_offers,id'
        ]);

        $offer = ServiceOffer::findOrFail($request->service_offer_id);
        $serviceRequest = $offer->serviceRequest;

        if ($serviceRequest->user_id !== auth()->id()) {
            return $this->errorResponse('No autorizado', 403);
        }
        if ($offer->status !== 'pending') {
            return $this->errorResponse('La oferta no está disponible para pago', 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $paymentIntent = PaymentIntent::create([
            'amount' => intval(floatval($offer->price_proposed) * 100),
            'currency' => 'usd',
            'metadata' => [
                'service_offer_id' => $offer->id,
                'service_request_id' => $serviceRequest->id,
                'user_id' => auth()->id()
            ]
        ]);

        // Opcional: guardar el PaymentIntent ID en la base de datos

        return $this->successResponse([
            'client_secret' => $paymentIntent->client_secret
        ]);
    }

    public function handleStripeWebhook(Request $request)
    {
        $event = $request->input('type');
        $paymentIntent = $request->input('data.object');

        if ($event === 'payment_intent.succeeded') {
            $offerId = $paymentIntent['metadata']['service_offer_id'] ?? null;
            $serviceRequestId = $paymentIntent['metadata']['service_request_id'] ?? null;
            $userId = $paymentIntent['metadata']['user_id'] ?? null;

            $offer = ServiceOffer::find($offerId);
            if ($offer && $offer->status === 'pending') {
                DB::transaction(function () use ($offer, $serviceRequestId, $userId, $paymentIntent) {
                    $offer->update(['status' => 'accepted']);
                    $contract = Contract::create([
                        'service_request_id' => $serviceRequestId,
                        'service_offer_id' => $offer->id,
                        'status' => 'in_progress'
                    ]);
                    $transaction = Transaction::create([
                        'payer_id' => $userId,
                        'payee_id' => $offer->user_id,
                        'service_request_id' => $serviceRequestId,
                        'amount' => $offer->amount,
                        'system_fee' => 0,
                        'final_amount' => $offer->amount,
                        'status' => 'completed',
                        'payment_method_id' => null,
                        'transaction_id' => $paymentIntent['id']
                    ]);
                    PaymentLog::create([
                        'transaction_id' => $transaction->id,
                        'event' => 'confirmed',
                        'details' => $paymentIntent
                    ]);
                });
            }
        }

        return response()->json(['status' => 'success']);
    }
}
