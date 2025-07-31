<template>
    <div class="chat-app">
        <UserSelector
            :currentUserId="currentUserId"
            :isConnected="isConnected"
            @user-changed="handleUserChange"
        />

        <div class="chat-container">
            <ChatRoomList
                :selectedRoomId="currentRoom?.id"
                @room-selected="selectRoom"
                @room-created="onRoomCreated"
            />

            <div class="chat-main">
                <div class="message-list-wrapper">
                    <MessageList
                        :currentRoom="currentRoom"
                        :messages="messages"
                        :typingUsers="typingUsers"
                        :currentUserId="currentUserId"
                    />
                </div>

                <MessageInput
                    :currentRoom="currentRoom"
                    @message-sent="onMessageSent"
                    @typing="onTyping"
                    :currentUserId="currentUserId"
                />
            </div>
        </div>

        <div v-if="!isConnected" class="connection-status">
            <span>⚠️ Disconnected from server</span>
        </div>

        <div v-if="onlineUsers.length > 0" class="online-users">
            <span>Online: {{ onlineUsers.map((u) => u.name).join(", ") }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { io } from "socket.io-client";
import UserSelector from "./UserSelector.vue";
import ChatRoomList from "./ChatRoomList.vue";
import MessageList from "./MessageList.vue";
import MessageInput from "./MessageInput.vue";

const currentRoom = ref(null);
const messages = ref([]);
const typingUsers = ref([]);
const onlineUsers = ref([]);
const currentUserId = ref(1);
const currentUserName = ref("John Doe");
const isConnected = ref(false);
const socket = ref(null);
let typingTimer = null;

const initializeApp = async () => {
    console.log("Chat app initialized");
};

const connectSocket = () => {
    socket.value = io("http://localhost:3000", {
        transports: ["websocket", "polling"],
    });

    socket.value.on("connect", () => {
        isConnected.value = true;
        socket.value.emit("join", {
            id: currentUserId.value,
            name: currentUserName.value,
        });
    });

    socket.value.on("disconnect", () => {
        isConnected.value = false;
        onlineUsers.value = [];
    });

    socket.value.on("connect_error", (err) => {
        console.error("Connection error:", err);
        isConnected.value = false;
    });

    socket.value.on("new-message", (message) => {
        if (
            currentRoom.value &&
            message.chat_room_id === currentRoom.value.id
        ) {
            const formattedMessage = {
                id: message.id,
                content: message.text,
                user: {
                    id: message.userId,
                    name: message.user,
                },
                user_id: message.userId,
                chat_room_id: message.chat_room_id,
                created_at: message.timestamp,
                message_type: "text",
            };

            messages.value.push(formattedMessage);
        }
    });

    socket.value.on("user-joined", (user) => {
        console.log(`${user.name} joined the chat`);
    });

    socket.value.on("user-left", (user) => {
        console.log(`${user.name} left the chat`);
        typingUsers.value = typingUsers.value.filter(
            (name) => name !== user.name
        );
    });

    socket.value.on("online-users", (users) => {
        onlineUsers.value = users;
    });

    socket.value.on("user-typing", (data) => {
        if (data.isTyping) {
            if (!typingUsers.value.includes(data.userName)) {
                typingUsers.value.push(data.userName);
            }
        } else {
            typingUsers.value = typingUsers.value.filter(
                (name) => name !== data.userName
            );
        }
    });
};

const handleUserChange = (user) => {
    if (socket.value?.connected) {
        socket.value.disconnect();
    }

    currentUserId.value = user.id;
    currentUserName.value = user.name;
    onlineUsers.value = [];
    typingUsers.value = [];

    connectSocket();
    if (currentRoom.value) loadMessages();
};

const selectRoom = async (room) => {
    if (socket.value && currentRoom.value) {
        socket.value.emit("leave-room", currentRoom.value.id);
    }

    currentRoom.value = room;
    typingUsers.value = [];

    if (socket.value) {
        socket.value.emit("join-room", room.id);
    }

    await loadMessages();
};

const loadMessages = async () => {
    if (!currentRoom.value) return;

    try {
        const response = await fetch(
            `/api/chat/messages?chat_room_id=${currentRoom.value.id}`
        );
        const data = await response.json();
        if (data.success) {
            messages.value = data.data;
        }
    } catch (err) {
        console.error("Failed to load messages", err);
    }
};

const onRoomCreated = (room) => {
    selectRoom(room);
};

const onMessageSent = async (message) => {
    if (socket.value && socket.value.connected) {
        socket.value.emit("send-message", {
            text: message.content,
            chat_room_id: message.chat_room_id,
        });
    }
};

const onTyping = (isTyping) => {
    if (!socket.value?.connected) return;

    socket.value.emit("typing", { isTyping });

    if (typingTimer) clearTimeout(typingTimer);

    if (isTyping) {
        typingTimer = setTimeout(() => {
            socket.value.emit("typing", { isTyping: false });
        }, 3000);
    }
};

onMounted(async () => {
    await initializeApp();
    connectSocket();
});

onBeforeUnmount(() => {
    if (socket.value) socket.value.disconnect();
    if (typingTimer) clearTimeout(typingTimer);
});
</script>

<style scoped>
.chat-app {
    height: 100vh;
    display: flex;
    flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
        sans-serif;
}

.chat-container {
    flex: 1;
    display: flex;
    height: 100%;
    overflow: hidden;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.message-list-wrapper {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    box-sizing: border-box;
}

/* Ensures input is always visible */
.chat-main > *:last-child {
    border-top: 1px solid #ddd;
    padding: 0.5rem 1rem;
    background: #fff;
}

.connection-status {
    background: #dc3545;
    color: white;
    padding: 0.5rem 1rem;
    text-align: center;
    font-size: 14px;
}

.online-users {
    background: #28a745;
    color: white;
    padding: 0.25rem 1rem;
    text-align: center;
    font-size: 12px;
}
</style>
