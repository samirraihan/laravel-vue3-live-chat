<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * Display a listing of messages for a chat room.
     */
    public function index(Request $request): JsonResponse
    {
        $chatRoomId = $request->query('chat_room_id');
        
        if (!$chatRoomId) {
            return response()->json([
                'success' => false,
                'message' => 'Chat room ID is required'
            ], 400);
        }

        $chatRoom = ChatRoom::find($chatRoomId);
        if (!$chatRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Chat room not found'
            ], 404);
        }

        $messages = Message::with('user')
            ->where('chat_room_id', $chatRoomId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:2000',
                'chat_room_id' => 'required|exists:chat_rooms,id',
                'message_type' => 'sometimes|string|in:text,image,file',
                'user_id' => 'required|exists:users,id'
            ]);

            $message = Message::create([
                'content' => $validated['content'],
                'chat_room_id' => $validated['chat_room_id'],
                'user_id' => $validated['user_id'],
                'message_type' => $validated['message_type'] ?? 'text'
            ]);

            $message->load('user', 'chatRoom');

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message sent successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified message.
     */
    public function show(string $id): JsonResponse
    {
        $message = Message::with(['user', 'chatRoom'])->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Update the specified message.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'content' => 'required|string|max:2000'
            ]);

            $message->update($validated);

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified message.
     */
    public function destroy(string $id): JsonResponse
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }
}
