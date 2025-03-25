<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'event',
        'details'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}


// Ejemplo de implementación:

// use App\Models\PaymentLog;

// // Ejemplo en un controlador o servicio:
// PaymentLog::create([
//     'transaction_id' => $transaction->id,
//     'event' => 'created', // o 'confirmed', 'refunded', etc.
//     'details' => json_encode([
//         'amount' => $transaction->amount,
//         'payment_method' => $transaction->paymentMethod->type,
//         // otros detalles relevantes
//     ])
// ]);
