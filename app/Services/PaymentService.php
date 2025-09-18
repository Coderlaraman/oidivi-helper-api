<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Agreement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para el manejo de pagos.
 * 
 * Este servicio maneja la lógica específica de pagos, trabajando en conjunto
 * con el TransactionService para crear las transacciones correspondientes.
 */
class PaymentService
{
    protected TransactionService $transactionService;
    
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    
    /**
     * Crea un pago en estado pending y genera las transacciones relacionadas.
     * No modifica el estado del acuerdo; las transacciones quedan en pending
     * hasta la confirmación del proveedor de pago (Stripe).
     *
     * @param array $paymentData
     * @return Payment
     * @throws Exception
     */
    public function createPayment(array $paymentData): Payment
    {
        try {
            // Valores por defecto
            $paymentData = array_merge([
                'status' => Payment::STATUS_PENDING,
                'currency' => 'USD',
            ], $paymentData);

            // Crear registro de pago
            $payment = Payment::create($paymentData);

            // Crear transacciones asociadas en estado pending
            $this->transactionService->processPayment($payment);

            Log::info('Payment created successfully', [
                'payment_id' => $payment->id,
                'agreement_id' => $payment->agreement_id ?? null,
                'amount' => $payment->amount,
            ]);

            return $payment->fresh();
        } catch (Exception $e) {
            Log::error('Failed to create payment', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);
            throw $e;
        }
    }
    
    /**
     * Procesa un pago completo desde el acuerdo hasta las transacciones.
     *
     * @param Agreement $agreement
     * @param array $paymentData
     * @return Payment
     * @throws Exception
     */
    public function processAgreementPayment(Agreement $agreement, array $paymentData): Payment
    {
        DB::beginTransaction();
        
        try {
            // Validar que el acuerdo puede ser pagado
            if (!$agreement->canBePaid()) {
                throw new Exception('Agreement cannot be paid in its current state');
            }
            
            // Crear el registro de pago
            $payment = $this->createPaymentRecord($agreement, $paymentData);
            
            // Procesar las transacciones asociadas
            $transactions = $this->transactionService->processPayment($payment);
            
            // Actualizar el estado del acuerdo
            $this->updateAgreementAfterPayment($agreement, $payment);
            
            DB::commit();
            
            Log::info('Agreement payment processed successfully', [
                'agreement_id' => $agreement->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'transactions_created' => count($transactions),
            ]);
            
            return $payment->load('transactions');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process agreement payment', [
                'agreement_id' => $agreement->id,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);
            throw $e;
        }
    }
    
    /**
     * Procesa un reembolso completo o parcial.
     *
     * @param Payment $payment
     * @param float $refundAmount
     * @param string $reason
     * @param array $refundData
     * @return array
     * @throws Exception
     */
    public function processRefund(Payment $payment, float $refundAmount, string $reason = '', array $refundData = []): array
    {
        DB::beginTransaction();
        
        try {
            // Validar que el pago puede ser reembolsado
            $this->validateRefundRequest($payment, $refundAmount);
            
            // Procesar las transacciones de reembolso
            $transactions = $this->transactionService->processRefund($payment, $refundAmount, $reason);
            
            // Actualizar el estado del pago
            $this->updatePaymentAfterRefund($payment, $refundAmount, $reason, $refundData);
            
            // Actualizar el acuerdo si es necesario
            if ($payment->agreement) {
                $this->updateAgreementAfterRefund($payment->agreement, $refundAmount);
            }
            
            DB::commit();
            
            Log::info('Refund processed successfully', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'reason' => $reason,
                'transactions_created' => count($transactions),
            ]);
            
            return [
                'payment' => $payment->fresh(),
                'transactions' => $transactions,
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process refund', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Confirma un pago pendiente.
     *
     * @param Payment $payment
     * @param array $confirmationData
     * @return Payment
     * @throws Exception
     */
    public function confirmPayment(Payment $payment, array $confirmationData = []): Payment
    {
        DB::beginTransaction();
        
        try {
            if ($payment->status !== Payment::STATUS_PENDING) {
                throw new Exception('Only pending payments can be confirmed');
            }
            
            // Actualizar el pago
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => now(),
                'stripe_metadata' => array_merge(
                    $payment->stripe_metadata ?? [],
                    $confirmationData
                ),
            ]);
            
            // Completar las transacciones asociadas
            $this->completePaymentTransactions($payment);
            
            // Actualizar el acuerdo
            if ($payment->agreement) {
                $payment->agreement->update([
                    'status' => Agreement::STATUS_PAID,
                    'paid_at' => now(),
                ]);
            }
            
            DB::commit();
            
            Log::info('Payment confirmed', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);
            
            return $payment->fresh();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Cancela un pago pendiente.
     *
     * @param Payment $payment
     * @param string $reason
     * @return Payment
     * @throws Exception
     */
    public function cancelPayment(Payment $payment, string $reason = ''): Payment
    {
        DB::beginTransaction();
        
        try {
            if (!in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])) {
                throw new Exception('Only pending or processing payments can be cancelled');
            }
            
            // Actualizar el pago
            $payment->update([
                'status' => Payment::STATUS_CANCELLED,
                'stripe_metadata' => array_merge(
                    $payment->stripe_metadata ?? [],
                    [
                        'cancellation_reason' => $reason,
                        'cancelled_at' => now()->toISOString(),
                    ]
                ),
            ]);
            
            // Cancelar las transacciones asociadas
            $this->cancelPaymentTransactions($payment, $reason);
            
            DB::commit();
            
            Log::info('Payment cancelled', [
                'payment_id' => $payment->id,
                'reason' => $reason,
            ]);
            
            return $payment->fresh();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Obtiene el resumen de pagos de un usuario.
     *
     * @param User $user
     * @param string $role 'payer' o 'payee'
     * @param array $filters
     * @return array
     */
    public function getUserPaymentSummary(User $user, string $role = 'both', array $filters = []): array
    {
        $query = Payment::query();
        
        // Filtrar por rol
        if ($role === 'payer') {
            $query->where('payer_user_id', $user->id);
        } elseif ($role === 'payee') {
            $query->where('payee_user_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('payer_user_id', $user->id)
                  ->orWhere('payee_user_id', $user->id);
            });
        }
        
        // Aplicar filtros adicionales
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        // Calcular estadísticas
        $payments = $query->get();
        
        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'completed_payments' => $payments->where('status', Payment::STATUS_COMPLETED)->count(),
            'completed_amount' => $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'pending_payments' => $payments->where('status', Payment::STATUS_PENDING)->count(),
            'pending_amount' => $payments->where('status', Payment::STATUS_PENDING)->sum('amount'),
            'refunded_payments' => $payments->where('status', Payment::STATUS_REFUNDED)->count(),
            'refunded_amount' => $payments->where('status', Payment::STATUS_REFUNDED)->sum('amount'),
            'by_currency' => $payments->groupBy('currency')->map(function ($currencyPayments) {
                return [
                    'count' => $currencyPayments->count(),
                    'total_amount' => $currencyPayments->sum('amount'),
                    'completed_amount' => $currencyPayments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
                ];
            }),
        ];
    }
    
    /**
     * Crea el registro de pago en la base de datos.
     *
     * @param Agreement $agreement
     * @param array $paymentData
     * @return Payment
     */
    private function createPaymentRecord(Agreement $agreement, array $paymentData): Payment
    {
        $paymentData = array_merge([
            'agreement_id' => $agreement->id,
            'service_request_id' => $agreement->service_request_id,
            'service_offer_id' => $agreement->service_offer_id,
            'payer_user_id' => $agreement->serviceRequest->user_id,
            'payee_user_id' => $agreement->serviceOffer->user_id,
            'amount' => $agreement->agreed_price,
            'currency' => $agreement->currency ?? 'USD',
            'status' => Payment::STATUS_PENDING,
        ], $paymentData);
        
        return Payment::create($paymentData);
    }
    
    /**
     * Actualiza el acuerdo después de un pago exitoso.
     *
     * @param Agreement $agreement
     * @param Payment $payment
     */
    private function updateAgreementAfterPayment(Agreement $agreement, Payment $payment): void
    {
        $agreement->update([
            'status' => Agreement::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }
    
    /**
     * Valida una solicitud de reembolso.
     *
     * @param Payment $payment
     * @param float $refundAmount
     * @throws Exception
     */
    private function validateRefundRequest(Payment $payment, float $refundAmount): void
    {
        if ($payment->status !== Payment::STATUS_COMPLETED) {
            throw new Exception('Only completed payments can be refunded');
        }
        
        if ($refundAmount <= 0) {
            throw new Exception('Refund amount must be greater than zero');
        }
        
        if ($refundAmount > $payment->amount) {
            throw new Exception('Refund amount cannot exceed payment amount');
        }
        
        // Verificar si ya hay reembolsos previos
        $previousRefunds = Transaction::where('related_model_type', Payment::class)
            ->where('related_model_id', $payment->id)
            ->where('type', Transaction::TYPE_REFUND)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');
            
        if (($previousRefunds + $refundAmount) > $payment->amount) {
            throw new Exception('Total refund amount would exceed payment amount');
        }
    }
    
    /**
     * Actualiza el pago después de un reembolso.
     *
     * @param Payment $payment
     * @param float $refundAmount
     * @param string $reason
     * @param array $refundData
     */
    private function updatePaymentAfterRefund(Payment $payment, float $refundAmount, string $reason, array $refundData): void
    {
        $totalRefunded = Transaction::where('related_model_type', Payment::class)
            ->where('related_model_id', $payment->id)
            ->where('type', Transaction::TYPE_REFUND)
            ->sum('amount');
            
        $newStatus = ($totalRefunded >= $payment->amount) 
            ? Payment::STATUS_REFUNDED 
            : Payment::STATUS_PARTIALLY_REFUNDED;
            
        $payment->update([
            'status' => $newStatus,
            'stripe_metadata' => array_merge(
                $payment->stripe_metadata ?? [],
                [
                    'refund_amount' => $totalRefunded,
                    'refund_reason' => $reason,
                    'refunded_at' => now()->toISOString(),
                ],
                $refundData
            ),
        ]);
    }
    
    /**
     * Actualiza el acuerdo después de un reembolso.
     *
     * @param Agreement $agreement
     * @param float $refundAmount
     */
    private function updateAgreementAfterRefund(Agreement $agreement, float $refundAmount): void
    {
        // Lógica para actualizar el estado del acuerdo según el reembolso
        // Esto dependerá de las reglas de negocio específicas
        $agreement->update([
            'status' => Agreement::STATUS_REFUNDED,
        ]);
    }
    
    /**
     * Completa las transacciones asociadas a un pago.
     *
     * @param Payment $payment
     */
    private function completePaymentTransactions(Payment $payment): void
    {
        $transactions = Transaction::where('related_model_type', Payment::class)
            ->where('related_model_id', $payment->id)
            ->where('status', Transaction::STATUS_PENDING)
            ->get();
            
        foreach ($transactions as $transaction) {
            $this->transactionService->completeTransaction($transaction);
        }
    }
    
    /**
     * Cancela las transacciones asociadas a un pago.
     *
     * @param Payment $payment
     * @param string $reason
     */
    private function cancelPaymentTransactions(Payment $payment, string $reason): void
    {
        $transactions = Transaction::where('related_model_type', Payment::class)
            ->where('related_model_id', $payment->id)
            ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_PROCESSING])
            ->get();
            
        foreach ($transactions as $transaction) {
            $transaction->cancel($reason);
        }
    }
}