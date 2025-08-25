<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ServiceOffer;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    use ApiResponseTrait;

    /**
     * Listar todos los chats asociados al usuario autenticado.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $chats = Chat::whereHas('serviceOffer', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('serviceRequest', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
        ->with([
            'serviceOffer.user',
            'serviceOffer.serviceRequest.user',
            'messages' => function ($q) {
                $q->latest()->limit(1); // solo el último mensaje
            },
        ])
        ->latest()
        ->get();

        $payload = $chats->map(function ($chat) use ($user) {
            $lastMessage = $chat->messages->first();

            $isRequester = $chat->serviceOffer->serviceRequest->user->id === $user->id;

            $otherUser = $isRequester
                ? $chat->serviceOffer->user
                : $chat->serviceOffer->serviceRequest->user;

            return [
                'id'               => $chat->id,
                'service_offer_id' => $chat->service_offer_id,
                'created_at'       => $chat->created_at->toDateTimeString(),
                'updated_at'       => $chat->updated_at->toDateTimeString(),
                'service_request' => [
                    'id'    => $chat->serviceOffer->serviceRequest->id,
                    'title' => $chat->serviceOffer->serviceRequest->title,
                ],
                'other_participant' => [
                    'id'                => $otherUser->id,
                    'name'              => $otherUser->name,
                    'profile_photo_url' => $otherUser->profile_photo_url ? Storage::url($otherUser->profile_photo_url) : null,
                ],
                'last_message' => $lastMessage ? [
                    'id'         => $lastMessage->id,
                    'type'       => $lastMessage->type,
                    'message'    => $lastMessage->message,
                    'media_url'  => $lastMessage->media_url,
                    'created_at' => $lastMessage->created_at->toDateTimeString(),
                ] : null,
            ];
        });

        return $this->successResponse($payload);
    }

    /**
     * Obtener o crear el chat asociado a una oferta.
     */
    public function showOrCreate(int $offerId): JsonResponse
    {
        $user  = Auth::user();
        $offer = ServiceOffer::with('serviceRequest.user')->find($offerId);

        if (! $offer) {
            return $this->notFoundResponse('Oferta no encontrada');
        }

        $requesterId = $offer->serviceRequest->user_id;
        $offererId   = $offer->user_id;

        if ($user->id !== $requesterId && $user->id !== $offererId) {
            return $this->unauthorizedResponse('No autorizado');
        }

        $chat = Chat::firstOrCreate(
            ['service_offer_id' => $offer->id],
            ['service_offer_id' => $offer->id]
        );

        $chat->load([
            'serviceOffer.serviceRequest.user',
            'serviceOffer.user',
            'messages.sender'
        ]);

        $payload = [
            'chat' => [
                'id'               => $chat->id,
                'service_offer_id' => $chat->service_offer_id,
                'created_at'       => $chat->created_at->toDateTimeString(),
                'updated_at'       => $chat->updated_at->toDateTimeString(),
                'requester'        => [
                    'id'   => $chat->serviceOffer->serviceRequest->user->id,
                    'name' => $chat->serviceOffer->serviceRequest->user->name,
                ],
                'offerer' => [
                    'id'   => $chat->serviceOffer->user->id,
                    'name' => $chat->serviceOffer->user->name,
                ],
            ],
            'messages' => $chat->messages->map(function ($msg) {
                return [
                    'id'          => $msg->id,
                    'sender_id'   => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'message'     => $msg->message,
                    'type'        => $msg->type,
                    'media_url'   => $msg->media_url,
                    'media_type'  => $msg->media_type,
                    'media_name'  => $msg->media_name,
                    'metadata'    => $msg->metadata,
                    'seen_at'     => $msg->seen_at?->toDateTimeString(),
                    'created_at'  => $msg->created_at->toDateTimeString(),
                ];
            })->all(),
        ];

        return $this->successResponse($payload);
    }
}
