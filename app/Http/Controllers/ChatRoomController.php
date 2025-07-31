<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ChatRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $chatRooms = ChatRoom::with(['creator', 'latestMessage.user'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $chatRooms
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_private' => 'boolean'
            ]);

            $chatRoom = ChatRoom::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_private' => $validated['is_private'] ?? false,
                'created_by' => 1 // For demo purposes, using user ID 1
            ]);

            $chatRoom->load('creator');

            return response()->json([
                'success' => true,
                'data' => $chatRoom,
                'message' => 'Chat room created successfully'
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
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $chatRoom = ChatRoom::with(['creator', 'messages.user'])
            ->find($id);

        if (!$chatRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Chat room not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $chatRoom
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $chatRoom = ChatRoom::find($id);

        if (!$chatRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Chat room not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_private' => 'boolean'
            ]);

            $chatRoom->update($validated);

            return response()->json([
                'success' => true,
                'data' => $chatRoom,
                'message' => 'Chat room updated successfully'
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $chatRoom = ChatRoom::find($id);

        if (!$chatRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Chat room not found'
            ], 404);
        }

        $chatRoom->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat room deleted successfully'
        ]);
    }
}
