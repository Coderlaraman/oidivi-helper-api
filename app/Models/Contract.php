<?php

namespace App\Models;

use App\Constants\NotificationType;
use App\Events\ContractAcceptedNotification;
use App\Events\ContractRejectedNotification;
use App\Events\ContractSentNotification;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class Contract
 *
 * Modelo que representa un contrato entre cliente y proveedor de servicios.
 *
 * @property int $id
 * @property int $service_request_id
 * @property int $service_offer_id
 * @property int $client_id
 * @property int $provider_id
 * @property string $status
 * @property array|null $terms
 * @property Carbon|null $sent_at
 * @property Carbon|null $responded_at
 * @property Carbon|null $expires_at
 * @property string|null $rejection_reason
 * @property string|null $cancellation_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ServiceRequest $serviceRequest
 * @property-read ServiceOffer $serviceOffer
 * @property-read User $client
 * @property-read User $provider
 * @property-read \Illuminate\Database\Eloquent\Collection|Payment[] $payments
 */
class Contract extends Model
{
    use HasFactory, Notifiable;

    /** Estado: Borrador */
    public const STATUS_DRAFT = 'draft';
    /** Estado: Enviado al proveedor */
    public const STATUS_SENT = 'sent';
    /** Estado: Aceptado por el proveedor */
    public const STATUS_ACCEPTED = 'accepted';
    /** Estado: Rechazado por el proveedor */
    public const STATUS_REJECTED = 'rejected';
    /** Estado: Cancelado */
    public const STATUS_CANCELLED = 'cancelled';
    /** Estado: Expirado */
    public const STATUS_EXPIRED = 'expired';
    /** Estado: Completado */
    public const STATUS_COMPLETED = 'completed';

    /**
     * Lista de todos los estados válidos para un contrato.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SENT,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
        self::STATUS_COMPLETED,
    ];

    /**
     * Estados que permiten generar pagos.
     *
     * @var array<int, string>
     */
    public const PAYABLE_STATUSES = [
        self::STATUS_ACCEPTED,
    ];

    /**
     * Estados finales (no se pueden cambiar).
     *
     * @var array<int, string>
     */
    public const FINAL_STATUSES = [
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
        self::STATUS_COMPLETED,
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'service_request_id',
        'service_offer_id',
        'client_id',
        'provider_id',
        'status',
        'terms',
        'sent_at',
        'responded_at',
        'expires_at',
        'rejection_reason',
        'cancellation_reason',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'terms' => 'array',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // --- RELACIONES ---

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
     * Relación: Oferta de servicio asociada al contrato.
     *
     * @return BelongsTo
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Relación: Cliente (usuario que solicita el servicio).
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relación: Proveedor (usuario que ofrece el servicio).
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Relación: Pagos asociados al contrato.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // --- MÉTODOS DE UTILIDAD ---

    /**
     * Verifica si el contrato puede ser pagado.
     *
     * @return bool
     */
    public function canBepaid(): bool
    {
        return in_array($this->status, self::PAYABLE_STATUSES);
    }

    /**
     * Verifica si el contrato está en un estado final.
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES);
    }

    /**
     * Verifica si el contrato ha expirado.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Marca el contrato como enviado.
     *
     * @param Carbon|null $expiresAt
     * @return bool
     */
    public function markAsSent(?Carbon $expiresAt = null): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $saved = $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'expires_at' => $expiresAt ?? now()->addDays(7), // Expira en 7 días por defecto
        ]);
        
        if ($saved) {
            $this->notifyContractSent();
        }
        
        return $saved;
    }

    /**
     * Marca el contrato como aceptado.
     *
     * @return bool
     */
    public function markAsAccepted(): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        $saved = $this->update([
            'status' => self::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
        
        if ($saved) {
            $this->notifyContractAccepted();
        }
        
        return $saved;
    }

    /**
     * Marca el contrato como rechazado.
     *
     * @param string|null $reason
     * @return bool
     */
    public function markAsRejected(?string $reason = null): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        $saved = $this->update([
            'status' => self::STATUS_REJECTED,
            'responded_at' => now(),
            'rejection_reason' => $reason,
        ]);
        
        if ($saved) {
            $this->notifyContractRejected();
        }
        
        return $saved;
    }

    /**
     * Marca el contrato como cancelado.
     *
     * @param string|null $reason
     * @return bool
     */
    public function markAsCancelled(?string $reason = null): bool
    {
        if ($this->isFinal()) {
            return false;
        }

        $saved = $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        if ($saved) {
            $this->notifyContractCancelled();
        }

        return $saved;
    }

    /**
     * Marca el contrato como expirado.
     *
     * @return bool
     */
    public function markAsExpired(): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    // --- SCOPES ---

    /**
     * Scope: Contratos por estado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Contratos expirados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->where('status', self::STATUS_SENT);
    }

    /**
     * Scope: Contratos que pueden ser pagados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePayable($query)
    {
        return $query->whereIn('status', self::PAYABLE_STATUSES);
    }

    /**
     * Notify client that contract has been sent.
     */
    protected function notifyContractSent(): void
    {
      try {
        $title = $this->serviceRequest?->title ?? '';
        // Notificar al proveedor (helper) que recibió un contrato (BD + broadcast)
        $this->createNotification(
          userIds: [$this->provider_id],
          type: NotificationType::CONTRACT_SENT,
          title: __('notifications.types.contract_sent'),
          message: __('notifications.messages.contract_sent', [
            'title' => $title,
          ])
        );
        event(new ContractSentNotification($this, $this->provider_id));

        // Notificación de confirmación al cliente (solo BD)
        $this->createNotification(
          userIds: [$this->client_id],
          type: NotificationType::CONTRACT_SENT,
          title: __('notifications.types.contract_sent_client'),
          message: __('notifications.messages.contract_sent_client', [
            'title' => $title,
          ])
        );
      } catch (\Exception $e) {
        Log::error('Error notifying contract sent', [
          'error' => $e->getMessage(),
          'contract_id' => $this->id,
        ]);
      }
    }

    /**
     * Notify provider that contract has been accepted.
     */
    protected function notifyContractAccepted(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            // Notificar al cliente que su contrato fue aceptado
            $this->createNotification(
                userIds: [$this->client_id],
                type: NotificationType::CONTRACT_ACCEPTED,
                title: __('notifications.types.contract_accepted'),
                message: __('notifications.messages.contract_accepted', [
                    'title' => $title
                ])
            );

            event(new ContractAcceptedNotification($this, $this->client_id));
        } catch (\Exception $e) {
            Log::error('Error notifying contract accepted', [
                'error' => $e->getMessage(),
                'contract_id' => $this->id
            ]);
        }
    }

    /**
     * Notify provider that contract has been rejected.
     */
    protected function notifyContractRejected(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            // Notificar al cliente que su contrato fue rechazado
            $this->createNotification(
                userIds: [$this->client_id],
                type: NotificationType::CONTRACT_REJECTED,
                title: __('notifications.types.contract_rejected'),
                message: __('notifications.messages.contract_rejected', [
                    'title' => $title
                ])
            );

            event(new ContractRejectedNotification($this, $this->client_id));
        } catch (\Exception $e) {
            Log::error('Error notifying contract rejected', [
                'error' => $e->getMessage(),
                'contract_id' => $this->id
            ]);
        }
    }

    /**
     * Notify both parties that the contract has been cancelled.
     */
    protected function notifyContractCancelled(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';

            // Crear notificación para ambas partes
            $this->createNotification(
                userIds: [$this->client_id],
                type: NotificationType::CONTRACT_CANCELLED,
                title: __('notifications.types.contract_cancelled'),
                message: __('notifications.messages.contract_cancelled', [
                    'title' => $title
                ])
            );

            $this->createNotification(
                userIds: [$this->provider_id],
                type: NotificationType::CONTRACT_CANCELLED,
                title: __('notifications.types.contract_cancelled'),
                message: __('notifications.messages.contract_cancelled', [
                    'title' => $title
                ])
            );

            // Emitir broadcast a ambos canales privados
            event(new \App\Events\ContractCancelledNotification($this, $this->client_id));
            event(new \App\Events\ContractCancelledNotification($this, $this->provider_id));
        } catch (\Exception $e) {
            Log::error('Error notifying contract cancelled', [
                'error' => $e->getMessage(),
                'contract_id' => $this->id
            ]);
        }
    }
}