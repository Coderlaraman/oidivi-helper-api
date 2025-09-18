<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\Transaction;

/**
 * Payment Model - Legacy wrapper around Transaction system
 * 
 * Este modelo mantiene compatibilidad con el sistema anterior
 * mientras delega la lógica principal al sistema de transacciones.
 * 
 * @deprecated Use Transaction model directly for new implementations
 *
 * @property int $id
 * @property int $agreement_id
 * @property int $service_request_id
 * @property int $service_offer_id
 * @property int $payer_user_id
 * @property int $payee_user_id
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string|null $stripe_payment_intent_id
 * @property string|null $stripe_session_id
 * @property array|null $stripe_metadata
 * @property array|null $metadata
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Agreement $agreement
 * @property-read ServiceRequest $serviceRequest
 * @property-read ServiceOffer $serviceOffer
 * @property-read User $payer
 * @property-read User $payee
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /** Estado: Pendiente */
    public const STATUS_PENDING = 'pending';
    /** Estado: Procesando */
    public const STATUS_PROCESSING = 'processing';
    /** Estado: Completado */
    public const STATUS_COMPLETED = 'completed';
    /** Estado: Fallido */
    public const STATUS_FAILED = 'failed';
    /** Estado: Cancelado */
    public const STATUS_CANCELED = 'canceled';
    /** Estado: Reembolsado */
    public const STATUS_REFUNDED = 'refunded';
    /** Estado: Parcialmente reembolsado */
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * Lista de todos los estados válidos para un pago.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELED,
        self::STATUS_REFUNDED,
        self::STATUS_PARTIALLY_REFUNDED,
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'agreement_id',
        'service_request_id',
        'service_offer_id',
        'payer_user_id',
        'payee_user_id',
        'amount',
        'currency',
        'status',
        'stripe_payment_intent_id',
        'stripe_session_id',
        'stripe_metadata',
        'metadata',
        'paid_at',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'stripe_metadata' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Get the agreement that owns the payment.
     */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    /**
     * Get the service request that owns the payment.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get the service offer that owns the payment.
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Get the payer user.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    /**
     * Get the payee user.
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_user_id');
    }

    /**
     * Get all transactions related to this payment.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_model_id')
            ->where('related_model_type', self::class);
    }

    /**
     * Get the main payment transaction.
     */
    public function mainTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'related_model_id')
            ->where('related_model_type', self::class)
            ->where('type', Transaction::TYPE_PAYMENT)
            ->whereNull('parent_transaction_id');
    }

    /**
     * Get refund transactions.
     */
    public function refundTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_model_id')
            ->where('related_model_type', self::class)
            ->where('type', Transaction::TYPE_REFUND);
    }

    // ========================================
    // MÉTODOS DE ESTADO - Delegados a transacciones
    // ========================================

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        if ($this->isUsingTransactions()) {
            return $this->mainTransaction?->isPending() ?? ($this->status === self::STATUS_PENDING);
        }
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is processing.
     */
    public function isProcessing(): bool
    {
        if ($this->isUsingTransactions()) {
            return $this->mainTransaction?->isProcessing() ?? ($this->status === self::STATUS_PROCESSING);
        }
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        if ($this->isUsingTransactions()) {
            return $this->mainTransaction?->isCompleted() ?? ($this->status === self::STATUS_COMPLETED);
        }
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment failed.
     */
    public function isFailed(): bool
    {
        if ($this->isUsingTransactions()) {
            return $this->mainTransaction?->isFailed() ?? ($this->status === self::STATUS_FAILED);
        }
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is cancelled.
     */
    public function isCancelled(): bool
    {
        if ($this->isUsingTransactions()) {
            return $this->mainTransaction?->isCancelled() ?? ($this->status === self::STATUS_CANCELED);
        }
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        if ($this->isUsingTransactions()) {
            return $this->refundTransactions()->exists() && 
                   $this->getTotalRefundAmount() >= $this->amount;
        }
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Check if payment is partially refunded.
     */
    public function isPartiallyRefunded(): bool
    {
        if ($this->isUsingTransactions()) {
            $refundAmount = $this->getTotalRefundAmount();
            return $refundAmount > 0 && $refundAmount < $this->amount;
        }
        return $this->status === self::STATUS_PARTIALLY_REFUNDED;
    }

    // ========================================
    // MÉTODOS DE UTILIDAD
    // ========================================

    /**
     * Get formatted amount with currency.
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && !$this->isRefunded();
    }

    /**
     * Check if payment can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->isPending() || $this->isProcessing();
    }

    /**
     * Get total refund amount from transactions.
     */
    public function getTotalRefundAmount(): float
    {
        if (!$this->isUsingTransactions()) {
            return 0.0;
        }

        return $this->refundTransactions()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');
    }

    /**
     * Get net amount after refunds.
     */
    public function getNetAmount(): float
    {
        return $this->amount - $this->getTotalRefundAmount();
    }

    /**
     * Get commission amount from transactions.
     */
    public function getCommissionAmount(): float
    {
        if (!$this->isUsingTransactions()) {
            return 0.0;
        }

        return $this->transactions()
            ->where('type', Transaction::TYPE_COMMISSION)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');
    }

    /**
     * Get transaction group ID.
     */
    public function getTransactionGroupId(): ?string
    {
        return $this->mainTransaction?->transaction_group_id ?? 
               $this->metadata['transaction_group_id'] ?? null;
    }

    /**
     * Check if this payment is using the new transaction system.
     */
    public function isUsingTransactions(): bool
    {
        return isset($this->metadata['migrated_to_transactions']) && 
               $this->metadata['migrated_to_transactions'] === true;
    }

    /**
     * Get payment summary with transaction details.
     */
    public function getSummary(): array
    {
        $summary = [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payer_id' => $this->payer_user_id,
            'payee_id' => $this->payee_user_id,
            'created_at' => $this->created_at,
            'paid_at' => $this->paid_at,
        ];

        if ($this->isUsingTransactions()) {
            $summary['transaction_details'] = [
                'group_id' => $this->getTransactionGroupId(),
                'main_transaction_id' => $this->mainTransaction?->id,
                'total_refunded' => $this->getTotalRefundAmount(),
                'commission_amount' => $this->getCommissionAmount(),
                'net_amount' => $this->getNetAmount(),
                'transaction_count' => $this->transactions()->count(),
            ];
        }

        return $summary;
    }

    // ========================================
    // MÉTODOS LEGACY - Para compatibilidad
    // ========================================

    /**
     * Update payment status (legacy method).
     * 
     * @deprecated Use TransactionService instead
     */
    public function updateStatus(string $status): bool
    {
        if ($this->isUsingTransactions()) {
            // Delegar al sistema de transacciones
            $transactionService = app(\App\Services\TransactionService::class);
            return $transactionService->updatePaymentStatus($this, $status);
        }

        return $this->update(['status' => $status]);
    }

    /**
     * Process refund (legacy method).
     * 
     * @deprecated Use PaymentService::processRefund instead
     */
    public function processRefund(float $amount, string $reason = null): bool
    {
        $paymentService = app(\App\Services\PaymentService::class);
        return $paymentService->processRefund($this, $amount, $reason);
    }

    /**
     * Marca el pago como completado (legacy method).
     *
     * @deprecated Use TransactionService instead
     * @return void
     */
    public function markAsCompleted(): void
    {
        if ($this->isUsingTransactions()) {
            $transactionService = app(\App\Services\TransactionService::class);
            $transactionService->updatePaymentStatus($this, self::STATUS_COMPLETED);
            return;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        // Create transactions for both payer and payee
        $this->createTransactions();
    }

    /**
     * Marca el pago como fallido (legacy method).
     *
     * @deprecated Use TransactionService instead
     * @return void
     */
    public function markAsFailed(): void
    {
        if ($this->isUsingTransactions()) {
            $transactionService = app(\App\Services\TransactionService::class);
            $transactionService->updatePaymentStatus($this, self::STATUS_FAILED);
            return;
        }

        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Create transactions for both payer and payee when payment is completed (legacy method).
     * 
     * @deprecated This is handled by TransactionService now
     */
    protected function createTransactions(): void
    {
        if (!$this->isCompleted()) {
            return;
        }

        // Create transaction for payer (outgoing payment)
        Transaction::create([
            'user_id' => $this->payer_user_id,
            'related_model_type' => self::class,
            'related_model_id' => $this->id,
            'type' => Transaction::TYPE_PAYMENT,
            'amount' => -$this->amount, // Negative for outgoing
            'currency' => $this->currency,
            'status' => Transaction::STATUS_COMPLETED,
            'description' => 'Payment for service request',
            'metadata' => [
                'payment_id' => $this->id,
                'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
                'agreement_id' => $this->agreement_id,
                'service_request_id' => $this->service_request_id,
            ],
            'processed_at' => $this->paid_at,
        ]);

        // Create transaction for payee (incoming payment)
        Transaction::create([
            'user_id' => $this->payee_user_id,
            'related_model_type' => self::class,
            'related_model_id' => $this->id,
            'type' => Transaction::TYPE_PAYMENT,
            'amount' => $this->amount, // Positive for incoming
            'currency' => $this->currency,
            'status' => Transaction::STATUS_COMPLETED,
            'description' => 'Payment received for service',
            'metadata' => [
                'payment_id' => $this->id,
                'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
                'agreement_id' => $this->agreement_id,
                'service_request_id' => $this->service_request_id,
            ],
            'processed_at' => $this->paid_at,
        ]);
    }

    /**
     * Create refund transactions when payment is refunded (legacy method).
     * 
     * @deprecated This is handled by TransactionService now
     */
    public function createRefundTransactions(): void
    {
        if (!$this->status === self::STATUS_REFUNDED) {
            return;
        }

        // Create refund transaction for payer (incoming refund)
        Transaction::create([
            'user_id' => $this->payer_user_id,
            'related_model_type' => self::class,
            'related_model_id' => $this->id,
            'type' => Transaction::TYPE_REFUND,
            'amount' => $this->amount, // Positive for incoming refund
            'currency' => $this->currency,
            'status' => Transaction::STATUS_COMPLETED,
            'description' => 'Refund for service payment',
            'metadata' => [
                'payment_id' => $this->id,
                'original_payment_intent_id' => $this->stripe_payment_intent_id,
            ],
            'processed_at' => now(),
        ]);

        // Create refund transaction for payee (outgoing refund)
        Transaction::create([
            'user_id' => $this->payee_user_id,
            'related_model_type' => self::class,
            'related_model_id' => $this->id,
            'type' => Transaction::TYPE_REFUND,
            'amount' => -$this->amount, // Negative for outgoing refund
            'currency' => $this->currency,
            'status' => Transaction::STATUS_COMPLETED,
            'description' => 'Refund issued for service',
            'metadata' => [
                'payment_id' => $this->id,
                'original_payment_intent_id' => $this->stripe_payment_intent_id,
            ],
            'processed_at' => now(),
        ]);
    }
}