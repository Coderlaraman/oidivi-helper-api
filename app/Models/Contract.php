<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Contract
 *
 * Modelo que representa un contrato generado a partir de una oferta aceptada en una solicitud de servicio.
 *
 * @property int $id
 * @property int $service_offer_id
 * @property int $service_request_id
 * @property int $provider_id
 * @property int $client_id
 * @property float $price
 * @property int $estimated_time
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read ServiceOffer $serviceOffer
 * @property-read ServiceRequest $serviceRequest
 * @property-read User $provider
 * @property-read User $client
 */
class Contract extends Model
{
    use HasFactory;

    /** Estado: Pendiente */
    public const STATUS_PENDING = 'pending';
    /** Estado: En progreso */
    public const STATUS_IN_PROGRESS = 'in_progress';
    /** Estado: Completado */
    public const STATUS_COMPLETED = 'completed';
    /** Estado: Cancelado */
    public const STATUS_CANCELED = 'canceled';
    /** Estado: Pagado */
    public const STATUS_PAID = 'paid';

    /**
     * Lista de todos los estados válidos para un contrato.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELED,
        self::STATUS_PAID,
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'service_offer_id',
        'service_request_id',
        'provider_id',
        'client_id',
        'price',
        'estimated_time',
        'start_date',
        'end_date',
        'status'
    ];

    /**
     * Relación: Oferta de servicio asociada al contrato.
     *
     * @return BelongsTo
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Relación: Solicitud de servicio asociada al contrato.
     *
     * @return BelongsTo
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relación: Usuario proveedor (quien realiza el servicio).
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Relación: Usuario cliente (quien solicita el servicio).
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
