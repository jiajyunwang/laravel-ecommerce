<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import Echo from 'laravel-echo'
import { io } from 'socket.io-client'

const isOpen = ref(false)
const messages = ref([])
const messageInput = ref('')
const unreadCount = ref(0)
const chatRooms = ref([])
const activeRoom = ref(null)
const chatContentHeader = ref('')
const showChatInput = ref(false)
const messagesEl = ref(null)

let userInfo = null
let echo = null
let listenedRoomId = null

function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

onMounted(async () => {
  const res = await fetch('/api/user/info', { headers: { 'Accept': 'application/json' } })
  if (!res.ok) return
  const data = await res.json()
  if (!data.user) return
  userInfo = data.user

  initEcho()

  if (userInfo.role === 'user') {
    await fetchUnreadCount()
  } else if (userInfo.role === 'admin') {
    await fetchChatList()
  }
})

onUnmounted(() => {
  if (echo) echo.disconnect()
})

function initEcho() {
  if (!userInfo?.token) return
  echo = new Echo({
    broadcaster: 'socket.io',
    client: io,
    host: 'Laravel-echo-server-env.eba-r9qrwrp8.us-east-1.elasticbeanstalk.com:6001',
    auth: {
      headers: {
        Authorization: 'Bearer ' + userInfo.token,
      },
    },
  })
}

async function openChat() {
  isOpen.value = true
  if (userInfo?.role === 'user') {
    await fetchMessages()
    startUserListen()
  }
}

function closeChat() {
  isOpen.value = false
}

async function fetchMessages() {
  const res = await fetch('/user/chat/messages', {
    headers: { 'Accept': 'application/json' },
  })
  const data = await res.json()
  messages.value = toMessageGroups(data)
  await nextTick()
  scrollToBottom()
}

function toMessageGroups(data) {
  return Object.entries(data).map(([date, items]) => ({
    date,
    items: items.map(m => ({
      text: m.message,
      sender: m.sender_id === m.user_id ? 'local' : 'remote',
      time: m.time,
    })),
  }))
}

function startUserListen() {
  if (!echo || !userInfo) return
  echo.join('user.' + userInfo.id)
    .listen('SendMessage', async (e) => {
      if (e.role === 'admin' && e.userId === userInfo.id && isOpen.value) {
        appendMessage(e.message.date, 'remote', e.message.message, e.message.time)
        scrollToBottom()
        await markAsRead(e.messageId)
      }
    })
}

async function fetchUnreadCount() {
  const res = await fetch('/user/chat/unread', {
    headers: { 'Accept': 'application/json' },
  })
  const data = await res.json()
  unreadCount.value = data
}

async function sendMessage() {
  const text = messageInput.value.trim()
  if (!text) return
  messageInput.value = ''

  const body = { message: text }
  if (userInfo.role === 'admin' && activeRoom.value) {
    body.roomId = activeRoom.value.roomId
  }

  const res = await fetch('/user/chat/send', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-XSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify(body),
  })
  const data = await res.json()
  appendMessage(data.date, 'local', data.message, data.time)
  scrollToBottom()
}

function appendMessage(date, sender, text, time) {
  const group = messages.value.find(g => g.date === date)
  if (group) {
    group.items.push({ sender, text, time })
  } else {
    messages.value.push({ date, items: [{ sender, text, time }] })
  }
}

async function markAsRead(messageId) {
  await fetch('/user/chat/mark-as-read', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-XSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify({ messageId }),
  })
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesEl.value) {
      messagesEl.value.scrollTop = messagesEl.value.scrollHeight
    }
  })
}

// Admin only
async function fetchChatList() {
  const res = await fetch('/admin/chat/room-list', {
    headers: { 'Accept': 'application/json' },
  })
  const data = await res.json()
  let total = 0
  chatRooms.value = data.map(room => {
    if (room.unreadCount) total += room.unreadCount
    return { ...room }
  })
  unreadCount.value = total
}

async function selectRoom(room) {
  if (activeRoom.value?.roomId === room.roomId) return
  activeRoom.value = room
  showChatInput.value = false
  chatContentHeader.value = room.nickname
  messages.value = []

  const res = await fetch(`/admin/chat/messages?id=${room.roomId}`, {
    headers: { 'Accept': 'application/json' },
  })
  const data = await res.json()
  messages.value = toMessageGroups(data)
  showChatInput.value = true

  unreadCount.value = Math.max(0, unreadCount.value - (room.unreadCount || 0))
  room.unreadCount = 0

  await nextTick()
  scrollToBottom()

  if (listenedRoomId !== room.roomId && echo) {
    listenedRoomId = room.roomId
    echo.join('user.' + room.userId)
      .listen('SendMessage', async (e) => {
        if (e.role === 'user' && e.userId === room.userId && isOpen.value) {
          appendMessage(e.message.date, 'remote', e.message.message, e.message.time)
          scrollToBottom()
          await markAsRead(e.messageId)
        }
      })
  }
}

function onKeydown(e) {
  if (e.key === 'Enter') sendMessage()
}
</script>

<template>
  <div id="chat-widget" v-if="userInfo">
    <div class="chat-icon" @click="openChat" v-show="!isOpen">
      <div class="unread unread-total" v-show="unreadCount > 0">
        <div class="unread-count">{{ unreadCount }}</div>
      </div>
      &#128172;
    </div>

    <!-- User chat box -->
    <div v-if="userInfo.role === 'user'" class="chat-box" v-show="isOpen">
      <div id="chat-header">
        <span>即時對話</span>
        <button id="close-chat" @click="closeChat">&times;</button>
      </div>
      <div id="chat-main">
        <div id="chat-content" class="chat-content">
          <div id="messages" ref="messagesEl">
            <template v-for="group in messages" :key="group.date">
              <div class="date-label">{{ group.date }}</div>
              <div
                v-for="(msg, i) in group.items"
                :key="i"
                :class="`message-wrapper ${msg.sender}`"
              >
                <template v-if="msg.sender === 'local'">
                  <span class="message-time">{{ msg.time }}</span>
                  <div :class="`message ${msg.sender}`">{{ msg.text }}</div>
                </template>
                <template v-else>
                  <div :class="`message ${msg.sender}`">{{ msg.text }}</div>
                  <span class="message-time">{{ msg.time }}</span>
                </template>
              </div>
            </template>
          </div>
          <div class="chat-input">
            <input
              type="text"
              id="message-input"
              v-model="messageInput"
              placeholder="輸入訊息..."
              @keydown="onKeydown"
            >
            <button id="send-message" @click="sendMessage">送出</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin chat box -->
    <div v-else-if="userInfo.role === 'admin'" id="admin-chat-box" class="chat-box" v-show="isOpen">
      <div id="chat-header">
        <span>即時對話</span>
        <button id="close-chat" @click="closeChat">&times;</button>
      </div>
      <div id="chat-main">
        <div id="chat-list" class="chat-list">
          <ul id="contacts">
            <li
              v-for="room in chatRooms"
              :key="room.roomId"
              :class="{ active: activeRoom?.roomId === room.roomId }"
              @click="selectRoom(room)"
            >
              <div class="room-name">{{ room.nickname }}</div>
              <div class="unread" v-show="room.unreadCount > 0">
                <div class="center unread-count">{{ room.unreadCount }}</div>
              </div>
            </li>
          </ul>
        </div>
        <div id="chat-content" class="admin-chat-content">
          <div class="chat-content-header" v-show="showChatInput">{{ chatContentHeader }}</div>
          <div id="messages" ref="messagesEl">
            <template v-for="group in messages" :key="group.date">
              <div class="date-label">{{ group.date }}</div>
              <div
                v-for="(msg, i) in group.items"
                :key="i"
                :class="`message-wrapper ${msg.sender}`"
              >
                <template v-if="msg.sender === 'local'">
                  <span class="message-time">{{ msg.time }}</span>
                  <div :class="`message ${msg.sender}`">{{ msg.text }}</div>
                </template>
                <template v-else>
                  <div :class="`message ${msg.sender}`">{{ msg.text }}</div>
                  <span class="message-time">{{ msg.time }}</span>
                </template>
              </div>
            </template>
          </div>
          <div class="chat-input" v-show="showChatInput">
            <input
              type="text"
              id="message-input"
              v-model="messageInput"
              placeholder="輸入訊息..."
              @keydown="onKeydown"
            >
            <button id="send-message" @click="sendMessage">送出</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
