<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Events\MessageSent;
use App\Events\NewChatMessageNotification;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserMessageResource; // Importante para la consistencia
use App\Models\Chat;
use App\Models\Message;
use App\Models\ServiceOffer;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, int $offerId): JsonResponse
    {
        $user = Auth::user();

        // 1. Validar la oferta y la participación del usuario
        $offer = ServiceOffer::find($offerId);
        if (!$offer) {
            return $this->notFoundResponse(__('messages.offer_not_found'));
        }
        if (!$offer->isParticipant($user)) {
            return $this->unauthorizedResponse(__('messages.unauthorized'));
        }

        // 2. Validar la entrada
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:attachment|string|max:2000',
            'attachment' => 'nullable|file|max:10240', // Usar 'nullable' es más flexible
        ]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        // 3. Obtener o crear el chat
        $chat = Chat::firstOrCreate(['service_offer_id' => $offer->id]);

        // 4. Preparar los datos del mensaje
        $data = ['chat_id' => $chat->id, 'sender_id' => $user->id];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getClientMimeType();
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('chat_media/' . $chat->id, $filename, 'public');

            $data['type'] = Str::startsWith($mime, 'image/') ? 'image' : (Str::startsWith($mime, 'video/') ? 'video' : 'file');
            $data['media_url'] = Storage::url($path);
            $data['media_type'] = $mime;
            $data['media_name'] = $originalName;
            $data['message'] = $request->input('message'); // Permite texto con archivo

            if ($data['type'] === 'image' && function_exists('getimagesize')) {
                try {
                    [$width, $height] = getimagesize($file->getRealPath());
                    $data['metadata'] = ['width' => $width, 'height' => $height];
                } catch (\Exception $e) {}
            }
        } else {
            $data['type'] = 'text';
            $data['message'] = $request->input('message');
        }

        // 5. Crear el mensaje y cargar relaciones
        $message = Message::create($data);
        $message->load('sender'); // Solo 'sender' es necesario para los eventos

        // 6. Despachar los eventos de broadcasting
        // Evento para la sala de chat (actualiza la UI de la conversación)
        broadcast(new MessageSent($message))->toOthers();

        // Evento para la notificación push (toast y contador)
        if ($recipient = $chat->getOtherParticipant($user)) {
            event(new NewChatMessageNotification($message, $recipient->id));
        }

        // 7. Devolver una respuesta exitosa y consistente
        return $this->successResponse(
            (new UserMessageResource($message))->resolve(),
            __('messages.message_sent'),
            201
        );
    }
}
