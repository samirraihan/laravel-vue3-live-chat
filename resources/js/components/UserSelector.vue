<template>
    <div class="user-selector">
        <div class="selector-container">
            <label for="user-select" class="selector-label">
                👤 Chat as:
            </label>
            <select
                id="user-select"
                v-model="selectedUserId"
                @change="changeUser"
                class="user-dropdown"
            >
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <div class="connection-indicator">
                <span
                    :class="['status-dot', { connected: isConnected }]"
                ></span>
                <span class="status-text">
                    {{ isConnected ? "Connected" : "Disconnected" }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    currentUserId: {
        type: Number,
        required: true,
    },
    isConnected: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["user-changed"]);

const users = [
    { id: 1, name: "John Doe" },
    { id: 2, name: "Jane Smith" },
    { id: 3, name: "Bob Wilson" },
];

const selectedUserId = ref(props.currentUserId);

watch(
    () => props.currentUserId,
    (newId) => {
        selectedUserId.value = newId;
    }
);

const changeUser = () => {
    const selectedUser = users.find((user) => user.id === selectedUserId.value);
    if (selectedUser) {
        emit("user-changed", {
            id: selectedUser.id,
            name: selectedUser.name,
        });
    }
};
</script>

<style scoped>
.user-selector {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.selector-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    max-width: 1200px;
    margin: 0 auto;
}

.selector-label {
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

.user-dropdown {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    color: white;
    padding: 0.5rem 1rem;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.user-dropdown:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
}

.user-dropdown:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.7);
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
}

.user-dropdown option {
    background: #333;
    color: white;
    padding: 0.5rem;
}

.connection-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto;
    font-size: 12px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #dc3545;
    transition: background-color 0.3s ease;
}

.status-dot.connected {
    background: #28a745;
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
}

.status-text {
    font-weight: 500;
    opacity: 0.9;
}

/* Responsive design */
@media (max-width: 768px) {
    .selector-container {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .connection-indicator {
        margin-left: 0;
        align-self: flex-end;
    }

    .user-dropdown {
        width: 100%;
        max-width: 200px;
    }
}

@media (max-width: 480px) {
    .user-selector {
        padding: 0.5rem;
    }

    .selector-container {
        gap: 0.25rem;
    }

    .selector-label {
        font-size: 12px;
    }

    .user-dropdown {
        font-size: 12px;
        padding: 0.4rem 0.8rem;
    }

    .status-text {
        font-size: 11px;
    }
}
</style>
