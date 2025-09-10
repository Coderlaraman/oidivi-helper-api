<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Payment
 *
 * Modelo que representa un pago realizado a través de Stripe para una oferta de servicio aceptada.
 *
 * @property int $id
 * @property int $contract_id
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
 * @property Carbon|null $paid_at
 * @property Carbon|null $released_at
 * @property string|null $stripe_transfer_id
 * @property int|null $platform_fee_percent
 * @property float|null $platform_fee_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Contract $contract
 * @property-read ServiceRequest $serviceRequest
 * @property-read ServiceOffer $serviceOffer
 * @property-read User $payer
 * @property-read User $payee
 */
class Payment extends Model
{
    use HasFactory;

    // Estados del pago
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_REFUNDED = 'refunded';
    // Nuevos estados para lógica de escrow
    public const STATUS_HELD = 'held';
    public const STATUS_RELEASED = 'released';

    protected $fillable = [
        'service_request_id',
        'service_offer_id',
        'contract_id',
        'payer_user_id',
        'payee_user_id',
        'amount',
        'currency',
        'status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_metadata',
        'paid_at',
        // Escrow
        'released_at',
        'stripe_transfer_id',
        'platform_fee_percent',
        'platform_fee_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'platform_fee_percent' => 'integer',
        'stripe_metadata' => 'array',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    // Relaciones
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_user_id');
    }

    // Helpers de estado
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isHeld(): bool
    {
        return $this->status === self::STATUS_HELD;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => $this->paid_at ?? now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }
}