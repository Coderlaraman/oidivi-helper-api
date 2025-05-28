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

        // Obtener chats donde el usuario es participante usando la tabla pivot
        $chats = Chat::whereHas('participants', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['participants:id,name,profile_photo_url'])
            ->withCount(['messages as unread_count' => function($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                      ->whereNull('seen_at');
            }])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Cargar el último mensaje para cada chat
        $chats->each(function($chat) {
            $chat->last_message = $chat->messages()->latest()->first();
        });

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
            'service_offer_id' => 'nullable|exists:service_offers,id',
            'initial_message' => 'nullable|string|max:1000',
            'name' => 'nullable|string|max:255', // Para chats grupales
            'type' => 'nullable|string|in:direct,group', // Tipo de chat
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
        $chatType = $request->input('type', 'direct');

        // Verificar si ya existe un chat directo entre estos usuarios para la misma oferta o solicitud
        if ($chatType === 'direct') {
            $existingChatQuery = Chat::whereHas('participants', function($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->whereHas('participants', function($query) use ($otherUser) {
                    $query->where('users.id', $otherUser->id);
                })
                ->where('type', 'direct');
                
            // Si se proporciona service_offer_id, buscar chat con ese ID específico
            if ($request->has('service_offer_id')) {
                $existingChatQuery->where('service_offer_id', $request->service_offer_id);
            } 
            // Si no hay service_offer_id pero hay service_request_id, buscar chat con ese ID específico
            elseif ($request->has('service_request_id')) {
                $existingChatQuery->where('service_request_id', $request->service_request_id);
            }
            
            $existingChat = $existingChatQuery->first();

            if ($existingChat) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Chat already exists',
                    'data' => $existingChat->load('participants:id,name,profile_photo_url')
                ]);
            }
        }

        // Crear nuevo chat
        $chat = Chat::create([
            'service_request_id' => $request->service_request_id,
            'service_offer_id' => $request->service_offer_id,
            'name' => $chatType === 'group' ? $request->name : null,
            'type' => $chatType,
            'last_message_at' => now(),
        ]);

        // Añadir participantes
        $chat->addParticipant($user, true); // El creador es admin
        $chat->addParticipant($otherUser);

        // Si hay un mensaje inicial, crearlo
        if ($request->has('initial_message') && !empty($request->initial_message)) {
            $message = $chat->messages()->create([
                'sender_id' => $user->id,
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
            'data' => $chat->load(['participants:id,name,profile_photo_url', 'serviceRequest:id,title,description,status', 'serviceOffer'])
        ], 201);
    }

    /**
     * Display the specified chat.
     */
    public function show(Chat $chat)
    {
        $user = Auth::user();

        // Verificar que el usuario sea participante del chat
        if (!$chat->isParticipant($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant of this chat'
            ], 403);
        }

        // Cargar relaciones necesarias
        $chat->load([
            'participants:id,name,profile_photo_url',
            'serviceRequest:id,title,description,status',
            'serviceOffer',
            'messages' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'messages.sender:id,name,profile_photo_url',
        ]);

        // Marcar mensajes como leídos
        $chat->markAsRead($user->id);

        // Obtener información de participación del usuario actual
        $userParticipation = $chat->participants()->where('users.id', $user->id)->first()->pivot;

        return response()->json([
            'status' => 'success',
            'data' => array_merge($chat->toArray(), [
                'user_participation' => [
                    'is_admin' => (bool) $userParticipation->is_admin,
                    'last_read_at' => $userParticipation->last_read_at,
                ]
            ])
        ]);
    }

    /**
     * Remove the specified chat from storage.
     */
    public function destroy(Chat $chat)
    {
        $user = Auth::user();

        // Verificar que el usuario sea participante del chat
        if (!$chat->isParticipant($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant of this chat'
            ], 403);
        }

        // Para chats grupales, si el usuario es admin, puede eliminar el chat
        // Si no es admin, solo se elimina su participación
        if ($chat->type === 'group') {
            $isAdmin = $chat->participants()->where('users.id', $user->id)->first()->pivot->is_admin;
            
            if ($isAdmin) {
                // Soft delete del chat completo
                $chat->delete();
                $message = 'Chat deleted successfully';
            } else {
                // Solo eliminar al usuario del chat
                $chat->removeParticipant($user->id);
                $message = 'You have left the chat';
            }
        } else {
            // Para chats directos, soft delete
            $chat->delete();
            $message = 'Chat deleted successfully';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Mark all messages in the chat as read.
     */
    public function markAsRead(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Verificar que el usuario sea participante del chat
        if (!$chat->isParticipant($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant of this chat'
            ], 403);
        }

        // Marcar mensajes como leídos
        $messagesMarked = $chat->markAsRead($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Messages marked as read',
            'data' => [
                'messages_marked' => $messagesMarked
            ]
        ]);
    }

    /**
     * Broadcast typing status.
     */
    public function typing(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }

        // Disparar evento de escritura
        event(new UserTyping(
            $chat->id,
            $user->id,
            $user->name,
            $request->input('typing')
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Typing status updated'
        ]);
    }

    /**
     * Store a new message in the chat.
     */
    public function storeMessage(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Verificar que el usuario sea participante del chat
        if (!$chat->isParticipant($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant of this chat'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required_without:media_url|string|max:1000',
            'type' => 'required|string|in:text,image,file,audio,video',
            'media_url' => 'nullable|string|max:255',
            'media_type' => 'required_with:media_url|string|max:50',
            'metadata' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear mensaje
        $message = $chat->messages()->create([
            'sender_id' => $user->id,
            'message' => $request->message ?? '',
            'type' => $request->type,
            'media_url' => $request->media_url,
            'media_type' => $request->media_type,
            'metadata' => $request->metadata,
        ]);

        // Actualizar last_message_at y last_read_at para el remitente
        $chat->update(['last_message_at' => now()]);
        $chat->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);

        // Disparar evento de mensaje enviado
        event(new \App\Events\MessageSent($message));

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $message->load('sender:id,name,profile_photo_url')
        ]);
    }
}
