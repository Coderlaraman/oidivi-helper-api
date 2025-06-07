<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Events\NewChatMessageNotification;
use App\Models\Chat;
use App\Models\Message;
use App\Models\ServiceOffer;
use App\Models\User;
use App\Models\Notification as CustomNotification; // tu modelo custom
use App\Constants\NotificationType;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, int $offerId): JsonResponse
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

        $validator = \Validator::make($request->all(), [
            'message'    => 'required_without:attachment|string|max:2000',
            'attachment' => 'required_without:message|file|max:10240',
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $chat = Chat::firstOrCreate(
            ['service_offer_id' => $offer->id],
            ['service_offer_id' => $offer->id]
        );

        $data = ['chat_id' => $chat->id, 'sender_id' => $user->id];
        if ($request->filled('message')) {
            $data['type']    = 'text';
            $data['message'] = $request->input('message');
        } elseif ($request->hasFile('attachment')) {
            $file     = $request->file('attachment');
            $mime     = $file->getClientMimeType();
            $original = $file->getClientOriginalName();
            $filename = Str::uuid()->toString() . '_' . $original;
            $path     = $file->storeAs('chat_media/' . $offer->id, $filename, config('filesystems.default'));
            $url      = Storage::url($path);

            if (Str::startsWith($mime, 'image/')) {
                $data['type'] = 'image';
            } elseif (Str::startsWith($mime, 'video/')) {
                $data['type'] = 'video';
            } else {
                $data['type'] = 'file';
            }

            $data['media_url']  = $url;
            $data['media_type'] = $mime;
            $data['media_name'] = $original;

            $metadata = [];
            if ($data['type'] === 'image') {
                try {
                    [$width, $height] = getimagesize($file->getRealPath());
                    $metadata['width']  = $width;
                    $metadata['height'] = $height;
                } catch (\Exception $e) {
                    // Omite si falla
                }
            }
            if (! empty($metadata)) {
                $data['metadata'] = $metadata;
            }
        }

        $message = Message::create($data);
        $message->load('sender','chat');

        // 1) Broadcast del chat en sí (para quienes estén viendo la sala)
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        // 2) Crear tu notificación custom en base de datos
        $recipient     = $chat->getOtherParticipant($user);
        if ($recipient) {
            $customNotif = CustomNotification::create([
                'user_id'  => $recipient->id,
                'type'     => NotificationType::NEW_CHAT_MESSAGE,
                'title'    => 'Nuevo mensaje de chat',
                'message'  => substr($message->message ?? '[archivo]', 0, 100),
                'read_at'  => null,
            ]);

            // (opcional) vincular polimórficamente:
            // $customNotif->serviceOffers()->attach($offer->id);

            // 3) Emitir evento para que el frontend reciba la alerta
            //    Y si quieres, incluyes el ID de tu custom notification:
            event(new NewChatMessageNotification($message, $recipient->id));
        }

        $response = [
            'id'          => $message->id,
            'chat_id'     => $message->chat_id,
            'sender_id'   => $message->sender_id,
            'sender_name' => $message->sender->name,
            'message'     => $message->message,
            'type'        => $message->type,
            'media_url'   => $message->media_url,
            'media_type'  => $message->media_type,
            'media_name'  => $message->media_name,
            'metadata'    => $message->metadata,
            'seen_at'     => $message->seen_at?->toDateTimeString(),
            'created_at'  => $message->created_at->toDateTimeString(),
        ];

        return $this->successResponse($response, 'Mensaje enviado', 201);
    }
}
