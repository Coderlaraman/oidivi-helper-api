<?php

namespace App\Models;

use App\Constants\NotificationType;
use App\Events\AgreementAcceptedNotification;
use App\Events\AgreementRejectedNotification;
use App\Events\AgreementSentNotification;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class Agreement
 *
 * Modelo que representa un acuerdo entre cliente y proveedor de servicios.
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
 * @property Carbon|null $completed_at
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
class Agreement extends Model
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
     * Lista de todos los estados válidos para un acuerdo.
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
        'completed_at',
        'rejection_reason',
        'cancellation_reason',
        // Campos de versionado/revisión
        'version',
        'edited_by',
        'revision_note',
        'edited_at',
        're_sent_at',
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
        'completed_at' => 'datetime',
        'edited_at' => 'datetime',
        're_sent_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Relación: Solicitud de servicio asociada al acuerdo.
     *
     * @return BelongsTo
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relación: Oferta de servicio asociada al acuerdo.
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
     * Relación: Pagos asociados al acuerdo.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all transactions related to this agreement.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_model_id')
            ->where('related_model_type', self::class);
    }

    /**
     * Get payment transactions for this agreement.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_PAYMENT);
    }

    /**
     * Get refund transactions for this agreement.
     */
    public function refundTransactions(): HasMany
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_REFUND);
    }

    // --- MÉTODOS DE UTILIDAD ---

    /**
     * Verifica si el acuerdo puede ser pagado.
     *
     * @return bool
     */
    public function canBePaid(): bool
    {
        // Verificar usando el nuevo sistema de transacciones si está disponible
        if ($this->isUsingTransactions()) {
            return $this->status === self::STATUS_ACCEPTED && 
                   !$this->paymentTransactions()
                       ->where('status', Transaction::STATUS_COMPLETED)
                       ->exists();
        }
        
        // Fallback al sistema legacy
        return in_array($this->status, self::PAYABLE_STATUSES);
    }

    /**
     * Verifica si el acuerdo está en un estado final.
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES);
    }

    /**
     * Verifica si el acuerdo ha expirado.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Marca el acuerdo como enviado.
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
            're_sent_at' => ($this->version && $this->version > 1) ? now() : $this->re_sent_at,
        ]);
        
        if ($saved) {
            $this->notifyAgreementSent();
        }
        
        return $saved;
    }

    /**
     * Marca el acuerdo como aceptado.
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
            $this->notifyAgreementAccepted();
        }
        
        return $saved;
    }

    /**
     * Marca el acuerdo como rechazado.
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
            $this->notifyAgreementRejected();
        }
        
        return $saved;
    }

    /**
     * Marca el acuerdo como cancelado.
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
            $this->notifyAgreementCancelled();
        }

        return $saved;
    }

    /**
     * Marca el acuerdo como expirado.
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

    /**
     * Crea una nueva revisión del acuerdo incrementando la versión y pasando a draft.
     * Limpia campos de respuesta y motivo de rechazo.
     *
     * @param array{terms?: array|null, revision_note?: string|null} $attributes
     * @param int $editorId
     * @return bool
     */
    public function revise(array $attributes, int $editorId): bool
    {
        if ($this->status !== self::STATUS_REJECTED) {
            return false;
        }

        $payload = [
            'status' => self::STATUS_DRAFT,
            'responded_at' => null,
            'rejection_reason' => null,
            'edited_by' => $editorId,
            'edited_at' => now(),
            'version' => (int) ($this->version ?? 1) + 1,
        ];

        if (array_key_exists('terms', $attributes)) {
            $payload['terms'] = $attributes['terms'];
        }
        if (array_key_exists('revision_note', $attributes)) {
            $payload['revision_note'] = $attributes['revision_note'];
        }

        return $this->update($payload);
    }

    // --- SCOPES ---

    /**
     * Scope: Acuerdos por estado.
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
     * Scope: Acuerdos expirados.
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
     * Scope: Acuerdos que pueden ser pagados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePayable($query)
    {
        return $query->whereIn('status', self::PAYABLE_STATUSES);
    }

    /**
     * Get the total amount paid for this agreement.
     */
    public function getTotalPaid(): float
    {
        // Usar el nuevo sistema de transacciones si está disponible
        if ($this->isUsingTransactions()) {
            return $this->paymentTransactions()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('amount');
        }
        
        // Fallback al sistema legacy
        return $this->payments()
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount');
    }

    /**
     * Get the total amount refunded for this agreement.
     */
    public function getTotalRefunded(): float
    {
        if ($this->isUsingTransactions()) {
            return $this->refundTransactions()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('amount');
        }
        
        // Fallback: calcular desde pagos legacy
        return $this->payments()
            ->whereIn('status', [Payment::STATUS_REFUNDED, Payment::STATUS_PARTIALLY_REFUNDED])
            ->sum('amount'); // Simplificado para el ejemplo
    }

    /**
     * Get the net amount (paid - refunded) for this agreement.
     */
    public function getNetAmount(): float
    {
        return $this->getTotalPaid() - $this->getTotalRefunded();
    }

    /**
     * Get commission amount for this agreement.
     */
    public function getCommissionAmount(): float
    {
        if ($this->isUsingTransactions()) {
            return $this->transactions()
                ->where('type', Transaction::TYPE_COMMISSION)
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('amount');
        }
        
        // Calcular comisión basada en pagos completados (5% por defecto)
        return $this->getTotalPaid() * 0.05;
    }

    /**
     * Check if this agreement is using the new transaction system.
     */
    public function isUsingTransactions(): bool
    {
        return $this->transactions()->exists();
    }

    /**
     * Get payment status for this agreement.
     */
    public function getPaymentStatus(): string
    {
        if ($this->isUsingTransactions()) {
            $completedPayments = $this->paymentTransactions()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->exists();
            
            $refundAmount = $this->getTotalRefunded();
            $paidAmount = $this->getTotalPaid();
            
            if (!$completedPayments) {
                return 'unpaid';
            }
            
            if ($refundAmount >= $paidAmount && $refundAmount > 0) {
                return 'refunded';
            }
            
            if ($refundAmount > 0) {
                return 'partially_refunded';
            }
            
            return 'paid';
        }
        
        // Fallback al sistema legacy
        $completedPayment = $this->payments()
            ->where('status', Payment::STATUS_COMPLETED)
            ->first();
            
        if (!$completedPayment) {
            return 'unpaid';
        }
        
        if ($completedPayment->status === Payment::STATUS_REFUNDED) {
            return 'refunded';
        }
        
        if ($completedPayment->status === Payment::STATUS_PARTIALLY_REFUNDED) {
            return 'partially_refunded';
        }
        
        return 'paid';
    }

    /**
     * Get financial summary for this agreement.
     */
    public function getFinancialSummary(): array
    {
        return [
            'total_paid' => $this->getTotalPaid(),
            'total_refunded' => $this->getTotalRefunded(),
            'net_amount' => $this->getNetAmount(),
            'commission_amount' => $this->getCommissionAmount(),
            'payment_status' => $this->getPaymentStatus(),
            'using_transactions' => $this->isUsingTransactions(),
            'transaction_count' => $this->isUsingTransactions() ? $this->transactions()->count() : 0,
        ];
    }

    /**
     * Notify client that agreement has been sent.
     */
    protected function notifyAgreementSent(): void
    {
      try {
        $title = $this->serviceRequest?->title ?? '';
        
        // Datos del acuerdo para incluir en las notificaciones
        $agreementData = [
            'agreement_id' => $this->id,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'provider_id' => $this->provider_id,
            'service_request_id' => $this->service_request_id,
            'expires_at' => $this->expires_at?->toISOString(),
        ];
        
        // Notificar al proveedor (helper) que recibió un acuerdo (BD + broadcast)
        $this->createNotification(
          userIds: [$this->provider_id],
          type: NotificationType::AGREEMENT_SENT,
            title: __('notifications.types.agreement_sent'),
            message: __('notifications.messages.agreement_sent', [
            'title' => $title,
          ]),
          data: $agreementData
        );
        event(new AgreementSentNotification($this, $this->provider_id));

        // Notificación de confirmación al cliente (solo BD)
        $this->createNotification(
          userIds: [$this->client_id],
          type: NotificationType::AGREEMENT_SENT,
            title: __('notifications.types.agreement_sent_client'),
            message: __('notifications.messages.agreement_sent_client', [
            'title' => $title,
          ]),
          data: $agreementData
        );
      } catch (\Exception $e) {
        Log::error('Error notifying agreement sent', [
          'error' => $e->getMessage(),
          'agreement_id' => $this->id,
        ]);
      }
    }

    /**
     * Notify provider that agreement has been accepted.
     */
    protected function notifyAgreementAccepted(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            
            // Datos del acuerdo para incluir en las notificaciones
            $agreementData = [
                'agreement_id' => $this->id,
                'status' => $this->status,
                'client_id' => $this->client_id,
                'provider_id' => $this->provider_id,
                'service_request_id' => $this->service_request_id,
            ];
            
            // Notificar al cliente que su acuerdo fue aceptado
            $this->createNotification(
                userIds: [$this->client_id],
                type: NotificationType::AGREEMENT_ACCEPTED,
                title: __('notifications.types.agreement_accepted'),
                message: __('notifications.messages.agreement_accepted', [
                    'title' => $title
                ]),
                data: $agreementData
            );

            event(new AgreementAcceptedNotification($this, $this->client_id));
        } catch (\Exception $e) {
            Log::error('Error notifying agreement accepted', [
                'error' => $e->getMessage(),
                'agreement_id' => $this->id
            ]);
        }
    }

    /**
     * Notify provider that agreement has been rejected.
     */
    protected function notifyAgreementRejected(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            
            // Datos del acuerdo para incluir en las notificaciones
            $agreementData = [
                'agreement_id' => $this->id,
                'status' => $this->status,
                'client_id' => $this->client_id,
                'provider_id' => $this->provider_id,
                'service_request_id' => $this->service_request_id,
            ];
            
            // Notificar al cliente que su acuerdo fue rechazado
            $this->createNotification(
                userIds: [$this->client_id],
                type: NotificationType::AGREEMENT_REJECTED,
                title: __('notifications.types.agreement_rejected'),
                message: __('notifications.messages.agreement_rejected', [
                    'title' => $title
                ]),
                data: $agreementData
            );

            event(new AgreementRejectedNotification($this, $this->client_id));
        } catch (\Exception $e) {
            Log::error('Error notifying agreement rejected', [
                'error' => $e->getMessage(),
                'agreement_id' => $this->id
            ]);
        }
    }

    /**
     * Notify both parties that the agreement has been cancelled.
     */
    protected function notifyAgreementCancelled(): void
    {
        try {
            $title = $this->serviceRequest?->title ?? '';
            
            // Datos del acuerdo para incluir en las notificaciones
            $agreementData = [
                'agreement_id' => $this->id,
                'status' => $this->status,
                'client_id' => $this->client_id,
                'provider_id' => $this->provider_id,
                'service_request_id' => $this->service_request_id,
            ];

            // Crear notificación para ambas partes
            $this->createNotification(
                userIds: [$this->client_id],
                type: NotificationType::AGREEMENT_CANCELLED,
                title: __('notifications.types.agreement_cancelled'),
                message: __('notifications.messages.agreement_cancelled', [
                    'title' => $title
                ]),
                data: $agreementData
            );

            $this->createNotification(
                userIds: [$this->provider_id],
                type: NotificationType::AGREEMENT_CANCELLED,
                title: __('notifications.types.agreement_cancelled'),
                message: __('notifications.messages.agreement_cancelled', [
                    'title' => $title
                ]),
                data: $agreementData
            );

            // Emitir broadcast a ambos canales privados
            event(new \App\Events\AgreementCancelledNotification($this, $this->client_id));
        event(new \App\Events\AgreementCancelledNotification($this, $this->provider_id));
        } catch (\Exception $e) {
            Log::error('Error notifying agreement cancelled', [
                'error' => $e->getMessage(),
                'agreement_id' => $this->id
            ]);
        }
    }
}