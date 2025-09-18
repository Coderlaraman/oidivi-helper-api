<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Agreement;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para el manejo de escrow (retención de fondos).
 * 
 * Este servicio maneja la lógica de retención y liberación de fondos
 * para garantizar la seguridad de las transacciones entre clientes y helpers.
 */
class EscrowService
{
    protected TransactionService $transactionService;
    
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    
    /**
     * Retiene fondos en escrow para un acuerdo.
     *
     * @param Agreement $agreement
     * @param float $amount
     * @param string $reason
     * @return Transaction
     * @throws Exception
     */
    public function holdFunds(Agreement $agreement, float $amount, string $reason = 'Service agreement escrow'): Transaction
    {
        DB::beginTransaction();
        
        try {
            // Verificar que el acuerdo está en estado válido para escrow
            if (!$this->canHoldFundsForAgreement($agreement)) {
                throw new Exception('Agreement is not in a valid state for escrow hold');
            }
            
            $escrowTransaction = $this->transactionService->createTransaction([
                'user_id' => $agreement->serviceOffer->user_id, // Helper
                'type' => Transaction::TYPE_ESCROW_HOLD,
                'amount' => $amount,
                'currency' => $agreement->currency ?? 'USD',
                'description' => $reason,
                'status' => Transaction::STATUS_HELD,
                'related_model_type' => Agreement::class,
                'related_model_id' => $agreement->id,
                'metadata' => [
                    'agreement_id' => $agreement->id,
                    'service_request_id' => $agreement->service_request_id,
                    'service_offer_id' => $agreement->service_offer_id,
                    'hold_reason' => $reason,
                    'held_at' => now()->toISOString(),
                ],
            ]);
            
            // Actualizar el acuerdo con información del escrow
            $this->updateAgreementEscrowStatus($agreement, 'held', $escrowTransaction->id);
            
            DB::commit();
            
            Log::info('Funds held in escrow', [
                'agreement_id' => $agreement->id,
                'amount' => $amount,
                'transaction_id' => $escrowTransaction->id,
                'helper_id' => $agreement->serviceOffer->user_id,
            ]);
            
            return $escrowTransaction;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to hold funds in escrow', [
                'agreement_id' => $agreement->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Libera fondos del escrow.
     *
     * @param Transaction $escrowTransaction
     * @param string $reason
     * @return Transaction
     * @throws Exception
     */
    public function releaseFunds(Transaction $escrowTransaction, string $reason = 'Service completed successfully'): Transaction
    {
        DB::beginTransaction();
        
        try {
            // Validar que es una transacción de escrow válida
            if (!$this->isValidEscrowTransaction($escrowTransaction)) {
                throw new Exception('Invalid escrow transaction for release');
            }
            
            if ($escrowTransaction->status !== Transaction::STATUS_HELD) {
                throw new Exception('Only held escrow transactions can be released');
            }
            
            // Crear transacción de liberación
            $releaseTransaction = $this->transactionService->createTransaction([
                'transaction_group_id' => $escrowTransaction->transaction_group_id,
                'parent_transaction_id' => $escrowTransaction->id,
                'user_id' => $escrowTransaction->user_id,
                'type' => Transaction::TYPE_ESCROW_RELEASE,
                'amount' => $escrowTransaction->amount,
                'currency' => $escrowTransaction->currency,
                'description' => $reason,
                'status' => Transaction::STATUS_COMPLETED,
                'related_model_type' => $escrowTransaction->related_model_type,
                'related_model_id' => $escrowTransaction->related_model_id,
                'processed_at' => now(),
                'metadata' => [
                    'original_escrow_transaction_id' => $escrowTransaction->id,
                    'release_reason' => $reason,
                    'released_at' => now()->toISOString(),
                ],
            ]);
            
            // Actualizar la transacción de escrow original
            $escrowTransaction->update([
                'status' => Transaction::STATUS_RELEASED,
                'processed_at' => now(),
                'metadata' => array_merge(
                    $escrowTransaction->metadata ?? [],
                    [
                        'release_transaction_id' => $releaseTransaction->id,
                        'released_at' => now()->toISOString(),
                    ]
                ),
            ]);
            
            // Actualizar el acuerdo
            if ($escrowTransaction->related_model_type === Agreement::class) {
                $agreement = Agreement::find($escrowTransaction->related_model_id);
                if ($agreement) {
                    $this->updateAgreementEscrowStatus($agreement, 'released', $releaseTransaction->id);
                }
            }
            
            DB::commit();
            
            Log::info('Escrow funds released', [
                'escrow_transaction_id' => $escrowTransaction->id,
                'release_transaction_id' => $releaseTransaction->id,
                'amount' => $escrowTransaction->amount,
                'user_id' => $escrowTransaction->user_id,
            ]);
            
            return $releaseTransaction;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to release escrow funds', [
                'escrow_transaction_id' => $escrowTransaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Libera fondos automáticamente después de completar un servicio.
     *
     * @param Agreement $agreement
     * @return Transaction|null
     * @throws Exception
     */
    public function autoReleaseFunds(Agreement $agreement): ?Transaction
    {
        // Buscar transacciones de escrow activas para este acuerdo
        $escrowTransaction = Transaction::where('related_model_type', Agreement::class)
            ->where('related_model_id', $agreement->id)
            ->where('type', Transaction::TYPE_ESCROW_HOLD)
            ->where('status', Transaction::STATUS_HELD)
            ->first();
            
        if (!$escrowTransaction) {
            return null;
        }
        
        return $this->releaseFunds($escrowTransaction, 'Automatic release after service completion');
    }
    
    /**
     * Obtiene el saldo total en escrow de un usuario.
     *
     * @param User $user
     * @param string $currency
     * @return float
     */
    public function getUserEscrowBalance(User $user, string $currency = 'USD'): float
    {
        return Transaction::where('user_id', $user->id)
            ->where('currency', $currency)
            ->where('type', Transaction::TYPE_ESCROW_HOLD)
            ->where('status', Transaction::STATUS_HELD)
            ->sum('amount');
    }
    
    /**
     * Obtiene todas las transacciones de escrow activas de un usuario.
     *
     * @param User $user
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserActiveEscrowTransactions(User $user, array $filters = [])
    {
        $query = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_ESCROW_HOLD)
            ->where('status', Transaction::STATUS_HELD)
            ->with(['relatedModel', 'childTransactions'])
            ->orderBy('created_at', 'desc');
            
        if (!empty($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->get();
    }
    
    /**
     * Obtiene el historial completo de escrow de un usuario.
     *
     * @param User $user
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserEscrowHistory(User $user, array $filters = [])
    {
        $query = Transaction::where('user_id', $user->id)
            ->whereIn('type', [Transaction::TYPE_ESCROW_HOLD, Transaction::TYPE_ESCROW_RELEASE])
            ->with(['relatedModel', 'parentTransaction', 'childTransactions'])
            ->orderBy('created_at', 'desc');
            
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
     * Fuerza la liberación de fondos (solo para administradores).
     *
     * @param Transaction $escrowTransaction
     * @param string $reason
     * @param User $adminUser
     * @return Transaction
     * @throws Exception
     */
    public function forceReleaseFunds(Transaction $escrowTransaction, string $reason, User $adminUser): Transaction
    {
        DB::beginTransaction();
        
        try {
            if (!$adminUser->hasRole('admin')) {
                throw new Exception('Only administrators can force release escrow funds');
            }
            
            $releaseTransaction = $this->releaseFunds($escrowTransaction, $reason);
            
            // Agregar información del administrador que forzó la liberación
            $releaseTransaction->update([
                'metadata' => array_merge(
                    $releaseTransaction->metadata ?? [],
                    [
                        'forced_release' => true,
                        'admin_user_id' => $adminUser->id,
                        'admin_action_at' => now()->toISOString(),
                    ]
                ),
            ]);
            
            DB::commit();
            
            Log::warning('Escrow funds force released by admin', [
                'escrow_transaction_id' => $escrowTransaction->id,
                'release_transaction_id' => $releaseTransaction->id,
                'admin_user_id' => $adminUser->id,
                'reason' => $reason,
            ]);
            
            return $releaseTransaction;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Verifica si se pueden retener fondos para un acuerdo.
     *
     * @param Agreement $agreement
     * @return bool
     */
    private function canHoldFundsForAgreement(Agreement $agreement): bool
    {
        // Verificar que el acuerdo está en un estado válido
        $validStatuses = [Agreement::STATUS_ACCEPTED, Agreement::STATUS_IN_PROGRESS];
        
        if (!in_array($agreement->status, $validStatuses)) {
            return false;
        }
        
        // Verificar que no hay fondos ya retenidos
        $existingEscrow = Transaction::where('related_model_type', Agreement::class)
            ->where('related_model_id', $agreement->id)
            ->where('type', Transaction::TYPE_ESCROW_HOLD)
            ->where('status', Transaction::STATUS_HELD)
            ->exists();
            
        return !$existingEscrow;
    }
    
    /**
     * Verifica si una transacción es válida para operaciones de escrow.
     *
     * @param Transaction $transaction
     * @return bool
     */
    private function isValidEscrowTransaction(Transaction $transaction): bool
    {
        return $transaction->type === Transaction::TYPE_ESCROW_HOLD &&
               in_array($transaction->status, [Transaction::STATUS_HELD, Transaction::STATUS_PENDING]);
    }
    
    /**
     * Actualiza el estado de escrow en el acuerdo.
     *
     * @param Agreement $agreement
     * @param string $escrowStatus
     * @param int $transactionId
     */
    private function updateAgreementEscrowStatus(Agreement $agreement, string $escrowStatus, int $transactionId): void
    {
        $metadata = $agreement->metadata ?? [];
        $metadata['escrow_status'] = $escrowStatus;
        $metadata['escrow_transaction_id'] = $transactionId;
        $metadata['escrow_updated_at'] = now()->toISOString();
        
        $agreement->update(['metadata' => $metadata]);
    }
}