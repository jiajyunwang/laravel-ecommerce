<script setup>
import { ref } from 'vue'

const form = ref({
  email: '',
  password: '',
})

const errors = ref({})
const errorMessage = ref('')
const submitting = ref(false)

async function submit() {
  errors.value = {}
  errorMessage.value = ''
  submitting.value = true
  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(form.value),
    })
    const data = await response.json()
    if (!response.ok) {
      errors.value = data.errors ?? {}
      errorMessage.value = data.message ?? ''
    } else {
      window.location.href = '/'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="title">
    <p>登入</p>
  </div>
  <div class="form">
    <form @submit.prevent="submit">
      <label>Email</label>
      <input v-model="form.email" type="text" required>
      <span v-if="errors.email" class="error">{{ errors.email[0] }}</span>
      <div v-if="errorMessage" class="error">{{ errorMessage }}</div>

      <label>密碼</label>
      <input v-model="form.password" type="password" required><br>
      <span v-if="errors.password" class="error">{{ errors.password[0] }}</span>

      <button type="submit" :disabled="submitting">登入</button>
    </form>
  </div>
</template>
