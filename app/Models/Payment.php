<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'provider',
        'token',
        'billing_details',
        'is_default',
        'status',
        'event',
        'amount',
        'currency',
        'details'
    ];

    protected $casts = [
        'billing_details' => 'array',
        'details' => 'array',
        'is_default' => 'boolean',
        'amount' => 'decimal:2'
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la transacción
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Scope para obtener métodos de pago por defecto
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope para obtener pagos por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para obtener pagos por proveedor
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope para obtener pagos por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}