<?php

namespace App\Http\Controllers\Api\V1\Client\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientTransactionResource;
use App\Models\Transaction;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ClientTransactionController extends Controller
{
    use ApiResponseTrait;

    /**
     * Listar todas las transacciones del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $transactions = Transaction::query()
                ->where(function($query) {
                    $query->where('payer_id', auth()->id())
                          ->orWhere('payee_id', auth()->id());
                })
                ->with(['payer', 'payee', 'serviceRequest', 'paymentMethod'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse(
                ClientTransactionResource::collection($transactions),
                'Transactions retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving transactions',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Mostrar el detalle de una transacción en específico.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        try {
            if ($transaction->payer_id !== auth()->id() && 
                $transaction->payee_id !== auth()->id()) {
                return $this->errorResponse(
                    'Unauthorized access',
                    403
                );
            }

            return $this->successResponse(
                new ClientTransactionResource(
                    $transaction->load(['payer', 'payee', 'serviceRequest', 'paymentMethod'])
                ),
                'Transaction details retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving transaction details',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Permitir solicitar un reembolso para una transacción.
     */
    public function refund(Request $request, Transaction $transaction): JsonResponse
    {
        try {
            if ($transaction->payer_id !== auth()->id()) {
                return $this->errorResponse(
                    'Unauthorized to request refund',
                    403
                );
            }

            if ($transaction->status !== 'completed') {
                return $this->errorResponse(
                    'Transaction cannot be refunded',
                    400
                );
            }

            $transaction->update(['status' => 'refunded']);

            return $this->successResponse(
                new ClientTransactionResource($transaction),
                'Refund processed successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error processing refund',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
