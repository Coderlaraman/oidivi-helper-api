<?php

namespace App\Http\Controllers\Api\V1\User\Payments;

use App\Http\Controllers\Api\V1\User\Payments\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserTransactionResource;
use App\Models\Transaction;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class UserPaymentController extends Controller
{
    use ApiResponseTrait;

    public function processPayment(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'service_request_id' => 'required|exists:service_requests,id'
            ]);

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $validated['amount'] * 100,
                'currency' => 'usd',
                'payment_method_types' => ['card']
            ]);

            return $this->successResponse([
                'client_secret' => $paymentIntent->client_secret
            ], __('messages.payments.intent_created'));

        } catch (ValidationException $e) {
            return $this->errorResponse(
                __('messages.validation_error'),
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.payments.failed'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function confirmPayment(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string',
                'service_request_id' => 'required|exists:service_requests,id'
            ]);

            $transaction = Transaction::where('transaction_id', $validated['transaction_id'])->first();

            if (!$transaction) {
                return $this->errorResponse(
                    __('messages.payments.transaction_not_found'),
                    404
                );
            }

            $transaction->update(['status' => 'completed']);

            return $this->successResponse(
                new UserTransactionResource($transaction),
                __('messages.payments.confirmed')
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                __('messages.validation_error'),
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.payments.confirmation_failed'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}



// Implementación en el frontrend

// import { loadStripe } from "@stripe/stripe-js";

// const stripe = await loadStripe("TU_STRIPE_PUBLIC_KEY");

// const handlePayment = async () => {
//   const response = await fetch("/api/payment/process", {
//     method: "POST",
//     body: JSON.stringify({ amount: 100, payment_method: "credit_card", service_request_id: 1 }),
//     headers: { "Content-Type": "application/json" }
//   });

//   const { client_secret } = await response.json();

//   const result = await stripe.confirmCardPayment(client_secret, {
//     payment_method: {
//       card: elements.getElement(CardElement),
//     },
//   });

//   if (result.paymentIntent.status === "succeeded") {
//     await fetch("/api/payment/confirm", {
//       method: "POST",
//       body: JSON.stringify({ transaction_id: result.paymentIntent.id, service_request_id: 1 }),
//       headers: { "Content-Type": "application/json" }
//     });

//     alert("Pago exitoso");
//   }
// };
