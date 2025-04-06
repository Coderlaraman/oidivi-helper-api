<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Events\MessageSent;
use App\Events\MessageSeen;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /**
     * Display a listing of messages for a specific chat.
     */
    public function index(Request $request, Chat $chat)
    {
        $user = Auth::user();
        
        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }
        
        // Obtener mensajes del chat
        $messages = $chat->messages()
            ->with(['sender:id,name,profile_photo_url'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request, Chat $chat)
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
            'message' => 'required_without:media|string|max:5000',
            'type' => 'required|string|in:text,image,video,audio,file,location',
            'media' => 'required_if:type,image,video,audio,file|file|max:10240', // 10MB max
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Determinar el receptor
        $receiverId = null;
        if (!$chat->is_group) {
            $receiverId = $chat->getOtherParticipant($user)->id;
        }
        
        // Procesar archivo multimedia si se proporciona
        $mediaUrl = null;
        $mediaType = null;
        
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $path = $file->store('chat-media', 'public');
            $mediaUrl = Storage::url($path);
            $mediaType = $file->getClientMimeType();
        }
        
        // Crear mensaje
        $message = $chat->messages()->create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->input('message', ''),
            'type' => $request->input('type', 'text'),
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'metadata' => $request->input('metadata'),
        ]);
        
        // Actualizar last_message_at del chat
        $chat->update(['last_message_at' => now()]);
        
        // Disparar evento de mensaje enviado
        event(new MessageSent($message));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender:id,name,profile_photo_url'])
        ], 201);
    }

    /**
     * Display the specified message.
     */
    public function show(Chat $chat, Message $message)
    {
        $user = Auth::user();
        
        // Verificar si el mensaje pertenece al chat
        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message does not belong to this chat'
            ], 404);
        }
        
        // Verificar si el usuario es participante del chat
        if (!$chat->isParticipant($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this chat'
            ], 403);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $message->load(['sender:id,name,profile_photo_url'])
        ]);
    }

    /**
     * Update the specified message in storage.
     */
    public function update(Request $request, Chat $chat, Message $message)
    {
        $user = Auth::user();
        
        // Verificar si el mensaje pertenece al chat
        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message does not belong to this chat'
            ], 404);
        }
        
        // Verificar si el usuario es el remitente del mensaje
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only edit your own messages'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Actualizar mensaje
        $message->update([
            'message' => $request->input('message'),
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Message updated successfully',
            'data' => $message->load(['sender:id,name,profile_photo_url'])
        ]);
    }

    /**
     * Remove the specified message from storage.
     */
    public function destroy(Chat $chat, Message $message)
    {
        $user = Auth::user();
        
        // Verificar si el mensaje pertenece al chat
        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message does not belong to this chat'
            ], 404);
        }
        
        // Verificar si el usuario es el remitente del mensaje
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only delete your own messages'
            ], 403);
        }
        
        // Eliminar mensaje (soft delete)
        $message->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully'
        ]);
    }
    
    /**
     * Mark a message as seen.
     */
    public function markAsSeen(Chat $chat, Message $message)
    {
        $user = Auth::user();
        
        // Verificar si el mensaje pertenece al chat
        if ($message->chat_id !== $chat->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message does not belong to this chat'
            ], 404);
        }
        
        // Verificar si el usuario es el receptor del mensaje
        if ($message->receiver_id !== $user->id && !$chat->is_group) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only mark messages sent to you as seen'
            ], 403);
        }
        
        // Marcar mensaje como leído
        $message->markAsSeen();
        
        // Disparar evento de mensaje visto
        event(new MessageSeen($message, $user));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Message marked as seen'
        ]);
    }
} 