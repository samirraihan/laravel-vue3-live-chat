<template>
    <div class="chat-room-list">
        <div class="room-list-header">
            <h3>Chat Rooms</h3>
            <button @click="toggleCreateRoom" class="create-room-btn">
                {{ showCreateRoom ? "✕" : "+" }}
            </button>
        </div>

        <!-- Create Room Form -->
        <div v-if="showCreateRoom" class="create-room-form">
            <input
                v-model="newRoom.name"
                placeholder="Room name"
                class="room-input"
                @keyup.enter="createRoom"
            />
            <input
                v-model="newRoom.description"
                placeholder="Description (optional)"
                class="room-input"
            />
            <div class="room-options">
                <label>
                    <input type="checkbox" v-model="newRoom.is_private" />
                    Private Room
                </label>
            </div>
            <button @click="createRoom" class="create-btn">Create Room</button>
        </div>

        <!-- Room List -->
        <div class="rooms">
            <div
                v-for="room in rooms"
                :key="room.id"
                :class="['room-item', { active: selectedRoomId === room.id }]"
                @click="selectRoom(room)"
            >
                <div class="room-info">
                    <h4>{{ room.name }}</h4>
                    <p v-if="room.description">{{ room.description }}</p>
                    <span class="room-type">{{
                        room.is_private ? "🔒 Private" : "🌐 Public"
                    }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
    selectedRoomId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(["room-selected", "room-created"]);

const rooms = ref([]);
const showCreateRoom = ref(false);

const newRoom = ref({
    name: "",
    description: "",
    is_private: false,
});

const toggleCreateRoom = () => {
    showCreateRoom.value = !showCreateRoom.value;
};

const loadRooms = async () => {
    try {
        const response = await fetch("/api/chat/rooms");
        const data = await response.json();
        if (data.success) {
            rooms.value = data.data;
        }
    } catch (error) {
        console.error("Error loading rooms:", error);
    }
};

const createRoom = async () => {
    if (!newRoom.value.name.trim()) return;

    try {
        const response = await fetch("/api/chat/rooms", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify(newRoom.value),
        });

        const data = await response.json();
        if (data.success) {
            rooms.value.unshift(data.data);
            newRoom.value = { name: "", description: "", is_private: false };
            showCreateRoom.value = false;
            emit("room-created", data.data);
        }
    } catch (error) {
        console.error("Error creating room:", error);
    }
};

const selectRoom = (room) => {
    emit("room-selected", room);
};

onMounted(() => {
    loadRooms();
});
</script>

<style scoped>
.chat-room-list {
    width: 300px;
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.room-list-header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

.room-list-header h3 {
    margin: 0;
    color: #333;
}

.create-room-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.create-room-btn:hover {
    background: #0056b3;
}

.create-room-form {
    padding: 1rem;
    background: white;
    border-bottom: 1px solid #dee2e6;
}

.room-input {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.room-options {
    margin-bottom: 0.5rem;
}

.room-options label {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #666;
}

.room-options input[type="checkbox"] {
    margin-right: 0.5rem;
}

.create-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.create-btn:hover {
    background: #218838;
}

.rooms {
    flex: 1;
    overflow-y: auto;
}

.room-item {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    cursor: pointer;
    transition: background-color 0.2s;
}

.room-item:hover {
    background: #e9ecef;
}

.room-item.active {
    background: #007bff;
    color: white;
}

.room-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 16px;
}

.room-info p {
    margin: 0 0 0.5rem 0;
    font-size: 12px;
    color: #666;
    opacity: 0.8;
}

.room-item.active .room-info p {
    color: rgba(255, 255, 255, 0.8);
}

.room-type {
    font-size: 12px;
    opacity: 0.7;
}
</style>
