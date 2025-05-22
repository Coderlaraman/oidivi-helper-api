<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Display a listing of the user's chats.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Obtener chats donde el usuario es participante
        $chats = Chat::where(function($query) use ($user) {
                $query->where('user_one', $user->id)
                      ->orWhere('user_two', $user->id);
            })
            ->with(['userOne:id,name,profile_photo_url', 'userTwo:id,name,profile_photo_url'])
            ->withCount(['messages' => function($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                      ->where('seen', false);
            }])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $chats
        ]);
    }

    /**
     * Store a newly created chat in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'initial_message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $otherUser = User::findOrFail($request->user_id);

        // Verificar si ya existe un chat entre estos usuarios
        $existingChat = Chat::where(function($query) use ($user, $otherUser) {
                $query->where(function($q) use ($user, $otherUser) {
                        $q->where('user_one', $user->id)
                          ->where('user_two', $otherUser->id);
                    })
                    ->orWhere(function($q) use ($user, $otherUser) {
                        $q->where('user_one', $otherUser->id)
                          ->where('user_two', $user->id);
                    });
            })
            ->first();

        if ($existingChat) {
            return response()->json([
                'status' => 'success',
                'message' => 'Chat already exists',
                'data' => $existingChat
            ]);
        }

        // Crear nuevo chat
        $chat = Chat::create([
            'user_one' => $user->id,
            'user_two' => $otherUser->id,
            'service_request_id' => $request->service_request_id,
            'last_message_at' => now(),
        ]);

        // Si hay un mensaje inicial, crearlo
        if ($request->has('initial_message') && !empty($request->initial_message)) {
            $message = $chat->messages()->create([
                'sender_id' => $user->id,
                'receiver_id' => $otherUser->id,
                'message' => $request->initial_message,
                'type' => 'text',
            ]);

            // Actualizar last_message_at
            $chat->update(['last_message_at' => now()]);

            // Disparar evento de mensaje enviado
            event(new \App\Events\MessageSent($message));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Chat created successfully',
            'data' => $chat->load(['userOne:id,name,profile_photo_url', 'userTwo:id,name,profile_photo_url'])
        ], 201);
    }

    /**
     * Display the specified chat.
     */
    public function show(Chat $chat)
    {
        $user = Auth::user();

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        // Marcar mensajes como leídos
        $chat->markAsRead($user);

        return response()->json([
            'status' => 'success',
            'data' => $chat->load([
                'userOne:id,name,profile_photo_url',
                'userTwo:id,name,profile_photo_url',
                'serviceRequest:id,title,description,status',
                'messages' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'messages.sender:id,name,profile_photo_url' // Incluir mensajes y el remitente de cada mensaje
            ])
        ]);
    }

    /**
     * Update the specified chat in storage.
     */
    public function update(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        // Solo se puede actualizar si es un chat grupal
        if (!$chat->is_group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update a one-on-one chat'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar chat
        $chat->update($request->only(['name', 'description']));

        return response()->json([
            'status' => 'success',
            'message' => 'Chat updated successfully',
            'data' => $chat
        ]);
    }

    /**
     * Remove the specified chat from storage.
     */
    public function destroy(Chat $chat)
    {
        $user = Auth::user();

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        // Eliminar chat (soft delete)
        $chat->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Chat deleted successfully'
        ]);
    }

    /**
     * Mark a chat as read.
     */
    public function markAsRead(Chat $chat)
    {
        $user = Auth::user();

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        // Marcar mensajes como leídos
        $chat->markAsRead($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Chat marked as read'
        ]);
    }

    /**
     * Send typing status.
     */
    public function typing(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        $isTyping = $request->input('is_typing', true);

        // Disparar evento de usuario escribiendo
        event(new UserTyping($chat, $user, $isTyping));

        return response()->json([
            'status' => 'success',
            'message' => $isTyping ? 'Typing status sent' : 'Stopped typing status sent'
        ]);
    }

    /**
     * Store a new message in the specified chat.
     */
    public function storeMessage(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'type' => 'nullable|string|in:text,image,file', // Validar tipos de mensaje
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear el mensaje
        $message = $chat->messages()->create([
            'sender_id' => $user->id,
            // Si es un chat uno a uno, establecer el receiver_id
            'receiver_id' => $chat->is_group ? null : $chat->getOtherParticipant($user)?->id,
            'message' => $request->message,
            'type' => $request->input('type', 'text'),
            'seen' => false, // Marcar como no leído inicialmente
        ]);

        // Actualizar last_message_at en el chat
        $chat->update(['last_message_at' => now()]);

        // Disparar evento de mensaje enviado
        event(new \App\Events\MessageSent($message->load('sender'))); // Cargar el remitente para el evento

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $message->load('sender') // Devolver el mensaje con el remitente
        ], 201);
    }
}
