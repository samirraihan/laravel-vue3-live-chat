<template>
  <div class="message-input">
    <div class="input-container">
      <textarea
        v-model="messageText"
        @keydown="handleKeydown"
        @input="handleTyping"
        placeholder="Type your message..."
        class="message-textarea"
        rows="1"
        ref="textarea"
        :disabled="!currentRoom"
      ></textarea>

      <button
        @click="sendMessage"
        :disabled="!canSend"
        class="send-button"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'

const props = defineProps({
  currentRoom: {
    type: Object,
    default: null
  },
  currentUserId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['message-sent', 'typing'])

const messageText = ref('')
const textarea = ref(null)
let typingTimer = null

const canSend = computed(() => {
  return props.currentRoom && messageText.value.trim().length > 0
})

const handleKeydown = (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    sendMessage()
  }
}

const handleTyping = () => {
  autoResize()

  emit('typing', true)

  if (typingTimer) clearTimeout(typingTimer)

  typingTimer = setTimeout(() => {
    emit('typing', false)
  }, 1000)
}

const autoResize = () => {
  const el = textarea.value
  if (el) {
    el.style.height = 'auto'
    el.style.height = Math.min(el.scrollHeight, 120) + 'px'
  }
}

const sendMessage = async () => {
  if (!canSend.value) return

  const content = messageText.value.trim()
  messageText.value = ''

  await nextTick(() => autoResize())

  emit('typing', false)
  if (typingTimer) clearTimeout(typingTimer)

  try {
    const response = await fetch('/api/chat/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        content,
        chat_room_id: props.currentRoom.id,
        message_type: 'text',
        user_id: props.currentUserId
      })
    })

    const data = await response.json()
    if (data.success) {
      emit('message-sent', data.data)
    } else {
      console.error('Error sending message:', data.message)
    }
  } catch (err) {
    console.error('Error sending message:', err)
  }
}

onMounted(() => {
  autoResize()
})
</script>

<style scoped>
.message-input {
  padding: 1rem;
  border-top: 1px solid #dee2e6;
  background: white;
}

.input-container {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  max-width: 100%;
}

.message-textarea {
  flex: 1;
  border: 1px solid #ced4da;
  border-radius: 20px;
  padding: 0.75rem 1rem;
  font-size: 14px;
  font-family: inherit;
  resize: none;
  outline: none;
  min-height: 40px;
  max-height: 120px;
  line-height: 1.4;
  transition: border-color 0.2s;
}

.message-textarea:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.message-textarea:disabled {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

.message-textarea::placeholder {
  color: #6c757d;
}

.send-button {
  background: #007bff;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, transform 0.1s;
  flex-shrink: 0;
}

.send-button:hover:not(:disabled) {
  background: #0056b3;
  transform: scale(1.05);
}

.send-button:active {
  transform: scale(0.95);
}

.send-button:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
}

.send-button svg {
  transform: rotate(0deg);
  transition: transform 0.2s;
}

.send-button:hover:not(:disabled) svg {
  transform: rotate(15deg);
}
</style>
