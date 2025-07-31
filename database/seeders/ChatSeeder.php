<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
        ]);

        $user3 = User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create default chat room
        $generalRoom = ChatRoom::create([
            'name' => 'General Chat',
            'description' => 'Welcome to the general chat room! Feel free to discuss anything here.',
            'is_private' => false,
            'created_by' => $user1->id,
        ]);

        // Create a private chat room
        $privateRoom = ChatRoom::create([
            'name' => 'Private Discussion',
            'description' => 'A private room for team discussions.',
            'is_private' => true,
            'created_by' => $user2->id,
        ]);

        // Create some demo messages
        Message::create([
            'content' => 'Welcome to the chat! This is the first message.',
            'user_id' => $user1->id,
            'chat_room_id' => $generalRoom->id,
            'message_type' => 'text',
        ]);

        Message::create([
            'content' => 'Hello everyone! Great to be here.',
            'user_id' => $user2->id,
            'chat_room_id' => $generalRoom->id,
            'message_type' => 'text',
        ]);

        Message::create([
            'content' => 'This chat app looks amazing! 🚀',
            'user_id' => $user3->id,
            'chat_room_id' => $generalRoom->id,
            'message_type' => 'text',
        ]);

        Message::create([
            'content' => 'Let\'s discuss the project details here.',
            'user_id' => $user2->id,
            'chat_room_id' => $privateRoom->id,
            'message_type' => 'text',
        ]);
    }
}
