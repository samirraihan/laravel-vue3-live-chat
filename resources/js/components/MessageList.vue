<template>
  <div class="message-list">
    <div class="messages-header" v-if="currentRoom">
      <h3>{{ currentRoom.name }}</h3>
      <p v-if="currentRoom.description">{{ currentRoom.description }}</p>
    </div>

    <div class="messages-container" ref="messagesContainer">
      <div v-if="!currentRoom" class="no-room-selected">
        <h3>Welcome to Live Chat!</h3>
        <p>Select a chat room from the sidebar to start chatting.</p>
      </div>

      <div v-else-if="messages.length === 0" class="no-messages">
        <p>No messages yet. Be the first to say something!</p>
      </div>

      <div v-else class="messages">
        <div
          v-for="message in messages"
          :key="message.id"
          :class="['message', { 'own-message': message.user_id === currentUserId }]"
        >
          <div class="message-header">
            <span class="username">{{ message?.user?.name }}</span>
            <span class="timestamp">{{ formatTime(message.created_at) }}</span>
          </div>
          <div class="message-content">{{ message.content }}</div>
        </div>
      </div>

      <div v-if="typingUsers.length > 0" class="typing-indicator">
        <span>{{ typingText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue'

const props = defineProps({
  currentRoom: {
    type: Object,
    default: null
  },
  messages: {
    type: Array,
    default: () => []
  },
  typingUsers: {
    type: Array,
    default: () => []
  },
  currentUserId: {
    type: Number,
    default: 1
  }
})

const messagesContainer = ref(null)

const typingText = computed(() => {
  const users = props.typingUsers
  if (users.length === 0) return ''
  if (users.length === 1) return `${users[0]} is typing...`
  if (users.length === 2) return `${users[0]} and ${users[1]} are typing...`
  return `${users.length} people are typing...`
})

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const scrollToBottom = () => {
  const container = messagesContainer.value
  if (container) {
    container.scrollTop = container.scrollHeight
  }
}

// Watch messages and scroll down when updated
watch(
  () => props.messages,
  async () => {
    await nextTick()
    scrollToBottom()
  },
  { deep: true }
)
</script>

<style scoped>
.message-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  background: white;
}

.messages-header {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
}

.messages-header h3 {
  margin: 0 0 0.25rem 0;
  color: #333;
}

.messages-header p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.no-room-selected,
.no-messages {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  color: #666;
}

.no-room-selected h3 {
  color: #333;
  margin-bottom: 0.5rem;
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  max-width: 70%;
  align-self: flex-start;
}

.message.own-message {
  align-self: flex-end;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
  font-size: 12px;
}

.username {
  font-weight: bold;
  color: #007bff;
}

.own-message .username {
  color: #28a745;
}

.timestamp {
  color: #666;
}

.message-content {
  background: #f8f9fa;
  padding: 0.75rem;
  border-radius: 12px;
  border-top-left-radius: 4px;
  word-wrap: break-word;
  line-height: 1.4;
}

.own-message .message-content {
  background: #007bff;
  color: white;
  border-top-left-radius: 12px;
  border-top-right-radius: 4px;
}

.typing-indicator {
  margin-top: 1rem;
  padding: 0.5rem;
  font-style: italic;
  color: #666;
  font-size: 14px;
}

.typing-indicator span {
  background: #f8f9fa;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  display: inline-block;
}

.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.messages-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
