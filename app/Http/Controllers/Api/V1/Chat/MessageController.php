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

        // Eliminar mensaje (soft delete) - Assuming soft delete is handled by model traits if applicable
        $message->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully'
        ]);
    }
}
