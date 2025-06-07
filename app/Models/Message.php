<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Chat;
use App\Models\User;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables
     */
    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'type',
        'media_url',
        'media_type',
        'media_name',
        'metadata',
        'seen_at',
    ];

    /**
     * Casteo de atributos
     */
    protected $casts = [
        'metadata'   => 'array',
        'seen_at'    => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación: cada mensaje pertenece a un chat
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Relación: cada mensaje tiene un remitente (usuario)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Marca el mensaje como visto (setea seen_at)
     */
    public function markAsSeen(): void
    {
        if (is_null($this->seen_at)) {
            $this->update(['seen_at' => now()]);
        }
    }

    /**
     * Verifica si el mensaje tiene adjunto
     */
    public function hasMedia(): bool
    {
        return in_array($this->type, ['image', 'video', 'file']);
    }

    /**
     * Obtiene datos concretos del JSON metadata (p.ej. dimensiones de imagen)
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
}
