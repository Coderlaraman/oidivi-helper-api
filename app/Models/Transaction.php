<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class Transaction
 *
 * Modelo que representa una transacción financiera en el sistema.
 * Esta es la entidad central para todos los movimientos financieros.
 *
 * @property int $id
 * @property string|null $transaction_group_id
 * @property int|null $parent_transaction_id
 * @property int|null $user_id
 * @property string $type
 * @property string $status
 * @property float $amount
 * @property string $currency
 * @property string|null $payment_method
 * @property string|null $payment_provider_id
 * @property array|null $payment_provider_data
 * @property float $fee_amount
 * @property float|null $net_amount
 * @property float|null $exchange_rate
 * @property float|null $original_amount
 * @property string|null $original_currency
 * @property string $description
 * @property string|null $reference
 * @property array|null $metadata
 * @property string|null $related_model_type
 * @property int|null $related_model_id
 * @property Carbon|null $processed_at
 * @property Carbon|null $settlement_date
 * @property string $reconciliation_status
 * @property int|null $risk_score
 * @property string $compliance_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User|null $user
 * @property-read Transaction|null $parentTransaction
 * @property-read \Illuminate\Database\Eloquent\Collection|Transaction[] $childTransactions
 * @property-read Model|null $relatedModel
 */
class Transaction extends Model
{
    use HasFactory;

    // === TIPOS DE TRANSACCIÓN ===
    
    /** Tipo: Pago de cliente a helper */
    public const TYPE_PAYMENT = 'payment';
    /** Tipo: Reembolso de helper a cliente */
    public const TYPE_REFUND = 'refund';
    /** Tipo: Comisión de plataforma */
    public const TYPE_COMMISSION = 'commission';
    /** Tipo: Retiro de helper */
    public const TYPE_WITHDRAWAL = 'withdrawal';
    /** Tipo: Depósito a helper */
    public const TYPE_DEPOSIT = 'deposit';
    /** Tipo: Bonificación */
    public const TYPE_BONUS = 'bonus';
    /** Tipo: Tarifa adicional */
    public const TYPE_FEE = 'fee';
    /** Tipo: Contracargo */
    public const TYPE_CHARGEBACK = 'chargeback';
    /** Tipo: Ajuste manual */
    public const TYPE_ADJUSTMENT = 'adjustment';
    /** Tipo: Retención en escrow */
    public const TYPE_ESCROW_HOLD = 'escrow_hold';
    /** Tipo: Liberación de escrow */
    public const TYPE_ESCROW_RELEASE = 'escrow_release';
    /** Tipo: Penalización */
    public const TYPE_PENALTY = 'penalty';

    // === ESTADOS DE TRANSACCIÓN ===
    
    /** Estado: Pendiente de procesamiento */
    public const STATUS_PENDING = 'pending';
    /** Estado: En procesamiento */
    public const STATUS_PROCESSING = 'processing';
    /** Estado: Completada exitosamente */
    public const STATUS_COMPLETED = 'completed';
    /** Estado: Falló el procesamiento */
    public const STATUS_FAILED = 'failed';
    /** Estado: Cancelada */
    public const STATUS_CANCELED = 'canceled';
    /** Estado: En disputa */
    public const STATUS_DISPUTED = 'disputed';
    /** Estado: Reembolsada */
    public const STATUS_REFUNDED = 'refunded';
    /** Estado: Parcialmente reembolsada */
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    /** Estado: Retenida (escrow) */
    public const STATUS_HELD = 'held';
    /** Estado: Liberada de retención */
    public const STATUS_RELEASED = 'released';
    /** Estado: Revertida */
    public const STATUS_REVERSED = 'reversed';

    /**
     * Lista de todos los tipos válidos para una transacción.
     *
     * @var array<int, string>
     */
    public const TYPES = [
        self::TYPE_PAYMENT,
        self::TYPE_REFUND,
        self::TYPE_COMMISSION,
        self::TYPE_WITHDRAWAL,
        self::TYPE_DEPOSIT,
        self::TYPE_BONUS,
        self::TYPE_FEE,
        self::TYPE_CHARGEBACK,
        self::TYPE_ADJUSTMENT,
        self::TYPE_ESCROW_HOLD,
        self::TYPE_ESCROW_RELEASE,
        self::TYPE_PENALTY,
    ];

    /**
     * Lista de todos los estados válidos para una transacción.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELED,
        self::STATUS_DISPUTED,
        self::STATUS_REFUNDED,
        self::STATUS_PARTIALLY_REFUNDED,
        self::STATUS_HELD,
        self::STATUS_RELEASED,
        self::STATUS_REVERSED,
    ];

    // === ESTADOS DE RECONCILIACIÓN ===
    
    /** Reconciliación: Pendiente */
    public const RECONCILIATION_PENDING = 'pending';
    /** Reconciliación: Reconciliada */
    public const RECONCILIATION_RECONCILED = 'reconciled';
    /** Reconciliación: En disputa */
    public const RECONCILIATION_DISPUTED = 'disputed';

    // === ESTADOS DE COMPLIANCE ===
    
    /** Compliance: Aprobada */
    public const COMPLIANCE_APPROVED = 'approved';
    /** Compliance: Bajo revisión */
    public const COMPLIANCE_UNDER_REVIEW = 'under_review';
    /** Compliance: Rechazada */
    public const COMPLIANCE_REJECTED = 'rejected';

    /**
     * Tipos que representan ingresos para el usuario.
     *
     * @var array<int, string>
     */
    public const INCOME_TYPES = [
        self::TYPE_PAYMENT,
        self::TYPE_BONUS,
        self::TYPE_DEPOSIT,
    ];

    /**
     * Tipos que representan gastos para el usuario.
     *
     * @var array<int, string>
     */
    public const EXPENSE_TYPES = [
        self::TYPE_REFUND,
        self::TYPE_FEE,
        self::TYPE_WITHDRAWAL,
        self::TYPE_COMMISSION,
        self::TYPE_PENALTY,
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_group_id',
        'parent_transaction_id',
        'user_id',
        'type',
        'status',
        'amount',
        'currency',
        'payment_method',
        'payment_provider_id',
        'payment_provider_data',
        'fee_amount',
        'net_amount',
        'exchange_rate',
        'original_amount',
        'original_currency',
        'description',
        'reference',
        'metadata',
        'related_model_type',
        'related_model_id',
        'processed_at',
        'settlement_date',
        'reconciliation_status',
        'risk_score',
        'compliance_status',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'original_amount' => 'decimal:2',
        'risk_score' => 'integer',
        'payment_provider_data' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'settlement_date' => 'datetime',
    ];

    /**
     * Relación: Usuario propietario de la transacción.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la transacción padre (para transacciones relacionadas).
     *
     * @return BelongsTo
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Relación con las transacciones hijas (transacciones derivadas).
     *
     * @return HasMany
     */
    public function childTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Relación polimórfica: Modelo relacionado con la transacción.
     *
     * @return MorphTo
     */
    public function relatedModel(): MorphTo
    {
        return $this->morphTo('related_model', 'related_model_type', 'related_model_id');
    }

    // === MÉTODOS DE ESTADO ===

    /**
     * Verifica si la transacción está completada.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si la transacción está pendiente.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la transacción está en procesamiento.
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Verifica si la transacción falló.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Verifica si la transacción está cancelada.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Verifica si la transacción está en disputa.
     *
     * @return bool
     */
    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    /**
     * Verifica si la transacción ha sido reembolsada.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Verifica si la transacción está retenida en escrow.
     *
     * @return bool
     */
    public function isHeld(): bool
    {
        return $this->status === self::STATUS_HELD;
    }

    /**
     * Verifica si la transacción ha sido liberada del escrow.
     *
     * @return bool
     */
    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    /**
     * Verifica si la transacción representa un ingreso para el usuario.
     *
     * @return bool
     */
    public function isIncome(): bool
    {
        return in_array($this->type, self::INCOME_TYPES);
    }

    /**
     * Verifica si la transacción representa un gasto para el usuario.
     *
     * @return bool
     */
    public function isExpense(): bool
    {
        return in_array($this->type, self::EXPENSE_TYPES);
    }

    // === MÉTODOS DE TIPO ===

    /**
     * Verifica si es una transacción de pago.
     */
    public function isPayment(): bool
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    /**
     * Verifica si es una transacción de reembolso.
     */
    public function isRefund(): bool
    {
        return $this->type === self::TYPE_REFUND;
    }

    /**
     * Verifica si es una transacción de comisión.
     */
    public function isCommission(): bool
    {
        return $this->type === self::TYPE_COMMISSION;
    }

    /**
     * Verifica si es una transacción de escrow.
     */
    public function isEscrow(): bool
    {
        return in_array($this->type, [self::TYPE_ESCROW_HOLD, self::TYPE_ESCROW_RELEASE]);
    }

    // === MÉTODOS DE CÁLCULO ===

    /**
     * Calcula el monto neto (amount - fee_amount).
     */
    public function calculateNetAmount(): float
    {
        return $this->amount - $this->fee_amount;
    }

    /**
     * Actualiza el monto neto basado en el monto y la tarifa.
     */
    public function updateNetAmount(): void
    {
        $this->net_amount = $this->calculateNetAmount();
    }

    /**
     * Obtiene el monto en la moneda original si hay conversión.
     */
    public function getOriginalAmountOrDefault(): float
    {
        return $this->original_amount ?? $this->amount;
    }

    /**
     * Obtiene la moneda original o la moneda por defecto.
     */
    public function getOriginalCurrencyOrDefault(): string
    {
        return $this->original_currency ?? $this->currency;
    }

    // === MÉTODOS DE FORMATO ===

    /**
     * Obtiene el monto formateado con la moneda.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Obtiene el monto neto formateado con la moneda.
     */
    public function getFormattedNetAmountAttribute(): string
    {
        $netAmount = $this->net_amount ?? $this->calculateNetAmount();
        return number_format($netAmount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Obtiene la tarifa formateada con la moneda.
     */
    public function getFormattedFeeAmountAttribute(): string
    {
        return number_format($this->fee_amount, 2) . ' ' . strtoupper($this->currency);
    }

    // === MÉTODOS DE NEGOCIO ===

    /**
     * Genera un ID único para el grupo de transacciones.
     */
    public static function generateTransactionGroupId(): string
    {
        return 'TXG_' . strtoupper(Str::random(12)) . '_' . time();
    }

    /**
     * Genera una referencia única para la transacción.
     */
    public static function generateReference(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        return $prefix . '_' . strtoupper(Str::random(8)) . '_' . time();
    }

    /**
     * Marca la transacción como completada.
     *
     * @return void
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Marca la transacción como fallida.
     *
     * @return void
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Marca la transacción como fallida con razón.
     */
    public function markAsFailedWithReason(string $reason = null): void
    {
        $metadata = $this->metadata ?? [];
        if ($reason) {
            $metadata['failure_reason'] = $reason;
            $metadata['failed_at'] = now()->toISOString();
        }

        $this->update([
            'status' => self::STATUS_FAILED,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Cancela la transacción.
     */
    public function cancel(string $reason = null): void
    {
        $metadata = $this->metadata ?? [];
        if ($reason) {
            $metadata['cancellation_reason'] = $reason;
            $metadata['cancelled_at'] = now()->toISOString();
        }

        $this->update([
            'status' => self::STATUS_CANCELED,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope: Transacciones completadas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Transacciones de ingresos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncome($query)
    {
        return $query->whereIn('type', self::INCOME_TYPES);
    }

    /**
     * Scope: Transacciones de gastos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpense($query)
    {
        return $query->whereIn('type', self::EXPENSE_TYPES);
    }

    /**
     * Scope: Transacciones por tipo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Transacciones en un rango de fechas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por estado de transacción.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por grupo de transacciones.
     */
    public function scopeInGroup($query, string $groupId)
    {
        return $query->where('transaction_group_id', $groupId);
    }

    /**
     * Scope para filtrar transacciones padre (sin parent_transaction_id).
     */
    public function scopeParentTransactions($query)
    {
        return $query->whereNull('parent_transaction_id');
    }

    /**
     * Scope para filtrar transacciones hijas.
     */
    public function scopeChildTransactions($query)
    {
        return $query->whereNotNull('parent_transaction_id');
    }

    /**
     * Scope para filtrar por usuario.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por moneda.
     */
    public function scopeInCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Obtiene el nombre legible del tipo de transacción.
     *
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        $typeNames = [
            self::TYPE_PAYMENT => 'Pago',
            self::TYPE_REFUND => 'Reembolso',
            self::TYPE_FEE => 'Comisión',
            self::TYPE_BONUS => 'Bonificación',
            self::TYPE_WITHDRAWAL => 'Retiro',
            self::TYPE_DEPOSIT => 'Depósito',
            self::TYPE_COMMISSION => 'Comisión',
            self::TYPE_PENALTY => 'Penalización',
        ];

        return $typeNames[$this->type] ?? $this->type;
    }

    /**
     * Obtiene el nombre legible del estado de la transacción.
     *
     * @return string
     */
    public function getStatusNameAttribute(): string
    {
        $statusNames = [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_PROCESSING => 'Procesando',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_FAILED => 'Fallida',
            self::STATUS_CANCELED => 'Cancelada',
            self::STATUS_REVERSED => 'Revertida',
        ];

        return $statusNames[$this->status] ?? $this->status;
    }

    /**
     * Crea una transacción desde un pago.
     *
     * @param Payment $payment
     * @param string $type
     * @return static
     */
    public static function createFromPayment(Payment $payment, string $type = self::TYPE_PAYMENT): static
    {
        return static::create([
            'user_id' => $type === self::TYPE_PAYMENT ? $payment->payee_user_id : $payment->payer_user_id,
            'related_model_type' => Payment::class,
            'related_model_id' => $payment->id,
            'type' => $type,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->isCompleted() ? self::STATUS_COMPLETED : self::STATUS_PENDING,
            'description' => "Transacción generada desde pago #{$payment->id}",
            'reference_id' => $payment->stripe_payment_intent_id,
            'processed_at' => $payment->paid_at,
            'metadata' => [
                'payment_id' => $payment->id,
                'agreement_id' => $payment->agreement_id,
                'service_request_id' => $payment->service_request_id,
                'service_offer_id' => $payment->service_offer_id,
            ],
        ]);
    }
}