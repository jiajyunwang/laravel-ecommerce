<script setup>
import { ref } from 'vue'

const form = ref({
  nickname: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const submitting = ref(false)
const success = ref(false)

async function submit() {
  errors.value = {}
  submitting.value = true
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify(form.value),
    })
    const data = await response.json()
    if (!response.ok) {
      errors.value = data.errors ?? {}
    } else {
      success.value = true
      setTimeout(() => { window.location.href = '/' }, 1500)
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="title">
    <p>註冊</p>
  </div>
  <div class="form">
    <div v-if="success" class="success">註冊成功，即將跳轉...</div>
    <form @submit.prevent="submit">
      <label>暱稱<span>*</span></label>
      <input v-model="form.nickname" type="text" required>
      <span v-if="errors.nickname" class="error">{{ errors.nickname[0] }}</span>

      <label>Email<span>*</span></label>
      <input v-model="form.email" type="text" required>
      <span v-if="errors.email" class="error">{{ errors.email[0] }}</span>

      <label>密碼<span>*</span></label>
      <input v-model="form.password" type="password" required>
      <span v-if="errors.password" class="error">{{ errors.password[0] }}</span>

      <label>再次輸入密碼<span>*</span></label>
      <input v-model="form.password_confirmation" type="password" required>

      <button type="submit" :disabled="submitting">註冊</button>
    </form>
  </div>
</template>
