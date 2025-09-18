<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Payment;
use App\Models\Agreement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para el manejo centralizado de transacciones.
 * 
 * Este servicio actúa como la capa de lógica de negocio para todas las operaciones
 * relacionadas con transacciones financieras en el sistema.
 */
class TransactionService
{
    /**
     * Crea una nueva transacción.
     *
     * @param array $data Datos de la transacción
     * @return Transaction
     * @throws Exception
     */
    public function createTransaction(array $data): Transaction
    {
        DB::beginTransaction();
        
        try {
            // Validar datos requeridos
            $this->validateTransactionData($data);
            
            // Generar IDs únicos si no se proporcionan
            if (empty($data['transaction_group_id'])) {
                $data['transaction_group_id'] = Transaction::generateTransactionGroupId();
            }
            
            if (empty($data['reference'])) {
                $data['reference'] = Transaction::generateReference($data['type']);
            }
            
            // Calcular monto neto si no se proporciona
            if (!isset($data['net_amount']) && isset($data['fee_amount'])) {
                $data['net_amount'] = $data['amount'] - $data['fee_amount'];
            }
            
            // Establecer valores por defecto
            $data = array_merge([
                'status' => Transaction::STATUS_PENDING,
                'fee_amount' => 0.00,
                'reconciliation_status' => Transaction::RECONCILIATION_PENDING,
                'compliance_status' => Transaction::COMPLIANCE_APPROVED,
                'risk_score' => 0,
            ], $data);
            
            $transaction = Transaction::create($data);
            
            // Log de la creación
            Log::info('Transaction created', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'user_id' => $transaction->user_id,
            ]);
            
            DB::commit();
            return $transaction;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create transaction', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }
    
    /**
     * Procesa un pago creando las transacciones correspondientes.
     *
     * @param Payment $payment
     * @return array Array de transacciones creadas
     * @throws Exception
     */
    public function processPayment(Payment $payment): array
    {
        DB::beginTransaction();
        
        try {
            $transactions = [];
            $groupId = Transaction::generateTransactionGroupId();
            
            // 1. Transacción principal de pago
            $paymentTransaction = $this->createTransaction([
                'transaction_group_id' => $groupId,
                'user_id' => $payment->payer_user_id,
                'type' => Transaction::TYPE_PAYMENT,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'description' => 'Payment for service - Agreement #' . $payment->agreement_id,
                'related_model_type' => Payment::class,
                'related_model_id' => $payment->id,
                'payment_method' => $payment->payment_method ?? 'stripe',
                'payment_provider_id' => $payment->stripe_payment_intent_id,
                'payment_provider_data' => [
                    'stripe_session_id' => $payment->stripe_session_id,
                    'stripe_metadata' => $payment->stripe_metadata,
                ],
            ]);
            $transactions[] = $paymentTransaction;
            
            // 2. Calcular y crear transacción de comisión
            $commissionAmount = $this->calculateCommission($payment->amount);
            if ($commissionAmount > 0) {
                $commissionTransaction = $this->createTransaction([
                    'transaction_group_id' => $groupId,
                    'parent_transaction_id' => $paymentTransaction->id,
                    'user_id' => $payment->payee_user_id,
                    'type' => Transaction::TYPE_COMMISSION,
                    'amount' => $commissionAmount,
                    'currency' => $payment->currency,
                    'description' => 'Platform commission for payment #' . $paymentTransaction->id,
                    'related_model_type' => Payment::class,
                    'related_model_id' => $payment->id,
                ]);
                $transactions[] = $commissionTransaction;
            }
            
            // 3. Transacción de depósito para el helper (monto - comisión)
            $helperAmount = $payment->amount - $commissionAmount;
            $depositTransaction = $this->createTransaction([
                'transaction_group_id' => $groupId,
                'parent_transaction_id' => $paymentTransaction->id,
                'user_id' => $payment->payee_user_id,
                'type' => Transaction::TYPE_DEPOSIT,
                'amount' => $helperAmount,
                'currency' => $payment->currency,
                'description' => 'Service payment received - Agreement #' . $payment->agreement_id,
                'related_model_type' => Payment::class,
                'related_model_id' => $payment->id,
            ]);
            $transactions[] = $depositTransaction;
            
            // Actualizar el payment con la referencia a la transacción principal
            $payment->update([
                'metadata' => array_merge($payment->metadata ?? [], [
                    'main_transaction_id' => $paymentTransaction->id,
                    'transaction_group_id' => $groupId,
                ])
            ]);
            
            DB::commit();
            
            Log::info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'transaction_group_id' => $groupId,
                'transactions_count' => count($transactions),
            ]);
            
            return $transactions;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Procesa un reembolso creando las transacciones correspondientes.
     *
     * @param Payment $originalPayment
     * @param float $refundAmount
     * @param string $reason
     * @return array Array de transacciones creadas
     * @throws Exception
     */
    public function processRefund(Payment $originalPayment, float $refundAmount, string $reason = ''): array
    {
        DB::beginTransaction();
        
        try {
            $transactions = [];
            $groupId = Transaction::generateTransactionGroupId();
            
            // Obtener la transacción original de pago
            $originalTransaction = Transaction::where('related_model_type', Payment::class)
                ->where('related_model_id', $originalPayment->id)
                ->where('type', Transaction::TYPE_PAYMENT)
                ->first();
            
            if (!$originalTransaction) {
                throw new Exception('Original payment transaction not found');
            }
            
            // 1. Transacción de reembolso al cliente
            $refundTransaction = $this->createTransaction([
                'transaction_group_id' => $groupId,
                'parent_transaction_id' => $originalTransaction->id,
                'user_id' => $originalPayment->payer_user_id,
                'type' => Transaction::TYPE_REFUND,
                'amount' => $refundAmount,
                'currency' => $originalPayment->currency,
                'description' => 'Refund for payment #' . $originalTransaction->id . ($reason ? ' - ' . $reason : ''),
                'related_model_type' => Payment::class,
                'related_model_id' => $originalPayment->id,
                'metadata' => [
                    'original_transaction_id' => $originalTransaction->id,
                    'refund_reason' => $reason,
                ],
            ]);
            $transactions[] = $refundTransaction;
            
            // 2. Transacción de retiro del helper
            $withdrawalTransaction = $this->createTransaction([
                'transaction_group_id' => $groupId,
                'parent_transaction_id' => $originalTransaction->id,
                'user_id' => $originalPayment->payee_user_id,
                'type' => Transaction::TYPE_WITHDRAWAL,
                'amount' => $refundAmount,
                'currency' => $originalPayment->currency,
                'description' => 'Refund deduction for payment #' . $originalTransaction->id,
                'related_model_type' => Payment::class,
                'related_model_id' => $originalPayment->id,
                'metadata' => [
                    'original_transaction_id' => $originalTransaction->id,
                    'refund_transaction_id' => $refundTransaction->id,
                ],
            ]);
            $transactions[] = $withdrawalTransaction;
            
            DB::commit();
            
            Log::info('Refund processed successfully', [
                'original_payment_id' => $originalPayment->id,
                'refund_amount' => $refundAmount,
                'transaction_group_id' => $groupId,
            ]);
            
            return $transactions;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process refund', [
                'payment_id' => $originalPayment->id,
                'refund_amount' => $refundAmount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Procesa un retiro de helper.
     *
     * @param User $user
     * @param float $amount
     * @param string $currency
     * @param array $withdrawalData
     * @return Transaction
     * @throws Exception
     */
    public function processWithdrawal(User $user, float $amount, string $currency, array $withdrawalData = []): Transaction
    {
        DB::beginTransaction();
        
        try {
            // Verificar saldo disponible
            $availableBalance = $this->getUserAvailableBalance($user->id, $currency);
            if ($availableBalance < $amount) {
                throw new Exception('Insufficient balance for withdrawal');
            }
            
            $withdrawalTransaction = $this->createTransaction([
                'user_id' => $user->id,
                'type' => Transaction::TYPE_WITHDRAWAL,
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Withdrawal request',
                'metadata' => array_merge([
                    'withdrawal_method' => $withdrawalData['method'] ?? 'bank_transfer',
                    'requested_at' => now()->toISOString(),
                ], $withdrawalData),
            ]);
            
            DB::commit();
            
            Log::info('Withdrawal processed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'transaction_id' => $withdrawalTransaction->id,
            ]);
            
            return $withdrawalTransaction;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process withdrawal', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Calcula el saldo disponible de un usuario en una moneda específica.
     *
     * @param int $userId
     * @param string $currency
     * @return float
     */
    public function getUserAvailableBalance(int $userId, string $currency): float
    {
        $income = Transaction::where('user_id', $userId)
            ->where('currency', $currency)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->whereIn('type', [
                Transaction::TYPE_DEPOSIT,
                Transaction::TYPE_BONUS,
                Transaction::TYPE_REFUND,
                Transaction::TYPE_ESCROW_RELEASE,
            ])
            ->sum('amount');
            
        $expenses = Transaction::where('user_id', $userId)
            ->where('currency', $currency)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->whereIn('type', [
                Transaction::TYPE_PAYMENT,
                Transaction::TYPE_WITHDRAWAL,
                Transaction::TYPE_FEE,
                Transaction::TYPE_COMMISSION,
                Transaction::TYPE_PENALTY,
                Transaction::TYPE_ESCROW_HOLD,
            ])
            ->sum('amount');
            
        return $income - $expenses;
    }
    
    /**
     * Obtiene el historial de transacciones de un usuario.
     *
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserTransactionHistory(int $userId, array $filters = [])
    {
        $query = Transaction::where('user_id', $userId)
            ->with(['parentTransaction', 'childTransactions', 'relatedModel'])
            ->orderBy('created_at', 'desc');
            
        // Aplicar filtros
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
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
        
        return $query->paginate($filters['per_page'] ?? 15);
    }
    
    /**
     * Marca una transacción como completada.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function completeTransaction(Transaction $transaction): Transaction
    {
        $transaction->update([
            'status' => Transaction::STATUS_COMPLETED,
            'processed_at' => now(),
        ]);
        
        Log::info('Transaction completed', [
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
        ]);
        
        return $transaction->fresh();
    }
    
    /**
     * Calcula la comisión de la plataforma para un monto dado.
     *
     * @param float $amount
     * @return float
     */
    private function calculateCommission(float $amount): float
    {
        // Lógica de cálculo de comisión (ejemplo: 5%)
        $commissionRate = config('app.commission_rate', 0.05);
        return round($amount * $commissionRate, 2);
    }
    
    /**
     * Valida los datos de una transacción.
     *
     * @param array $data
     * @throws Exception
     */
    private function validateTransactionData(array $data): void
    {
        $required = ['type', 'amount', 'currency', 'description'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }
        
        if (!in_array($data['type'], Transaction::TYPES)) {
            throw new Exception('Invalid transaction type');
        }
        
        if ($data['amount'] <= 0) {
            throw new Exception('Amount must be greater than zero');
        }
        
        if (isset($data['status']) && !in_array($data['status'], Transaction::STATUSES)) {
            throw new Exception('Invalid transaction status');
        }
    }
}