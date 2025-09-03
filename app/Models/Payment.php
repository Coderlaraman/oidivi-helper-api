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
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'contract_id',
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
        'paid_at' => 'datetime',
    ];

    /**
     * Relación: Contrato asociado al pago.
     *
     * @return BelongsTo
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relación: Solicitud de servicio asociada al pago.
     *
     * @return BelongsTo
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relación: Oferta de servicio asociada al pago.
     *
     * @return BelongsTo
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Relación: Usuario que realiza el pago.
     *
     * @return BelongsTo
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    /**
     * Relación: Usuario que recibe el pago.
     *
     * @return BelongsTo
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_user_id');
    }

    /**
     * Verifica si el pago está completado.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si el pago está pendiente.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si el pago falló.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Marca el pago como completado.
     *
     * @return void
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
    }

    /**
     * Marca el pago como fallido.
     *
     * @return void
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }
}