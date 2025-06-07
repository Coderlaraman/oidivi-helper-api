<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ServiceOffer;
use App\Models\Message;
use App\Models\User;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables
     */
    protected $fillable = [
        'service_offer_id',
    ];

    /**
     * Relaciones y métodos
     */

    /**
     * Cada Chat pertenece a una ServiceOffer
     */
    public function serviceOffer()
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Obtener usuario que hizo la oferta (offerer)
     */
    public function offerer()
    {
        return $this->serviceOffer->user();
    }

    /**
     * Obtener usuario que publicó la solicitud (requester)
     */
    public function requester()
    {
        return $this->serviceOffer->serviceRequest->user();
    }

    /**
     * Mensajes del chat
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Devuelve el otro participante en un chat 1:1
     *
     * @param  User|int  $user
     * @return User|null
     */
    public function getOtherParticipant($user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        // Si el usuario es quien hizo la oferta, el otro es el solicitante
        if ($userId === $this->serviceOffer->user_id) {
            return $this->requester()->first();
        }

        // Si el usuario es quien solicitó, el otro es el oferente
        if ($userId === $this->serviceOffer->serviceRequest->user_id) {
            return $this->offerer()->first();
        }

        return null;
    }

    /**
     * Marca todos los mensajes no leídos como leídos para un usuario dado
     *
     * @param  User|int  $user
     * @return int  Cantidad de mensajes marcados
     */
    public function markAsRead($user): int
    {
        $userId = $user instanceof User ? $user->id : $user;
        $unread = $this->messages()
                       ->where('sender_id', '<>', $userId)
                       ->whereNull('seen_at')
                       ->get();

        $count = 0;
        foreach ($unread as $msg) {
            $msg->markAsSeen();
            $count++;
        }

        return $count;
    }

    /**
     * Cuenta mensajes no leídos para un usuario
     *
     * @param  User|int  $user
     * @return int
     */
    public function getUnreadCount($user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->messages()
                    ->where('sender_id', '<>', $userId)
                    ->whereNull('seen_at')
                    ->count();
    }
}
