<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const errors = ref({})
const sessionError = ref('')

function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

onMounted(() => {
  const link = document.createElement('link')
  link.id = 'admin-css'
  link.rel = 'stylesheet'
  link.href = '/backend/css/sb-admin-2.min.css'
  document.head.appendChild(link)
  document.body.className = 'bg-gradient-primary'
})

onUnmounted(() => {
  const link = document.getElementById('admin-css')
  if (link) link.remove()
  document.body.className = 'bg-primary'
})

async function handleLogin() {
  errors.value = {}
  sessionError.value = ''

  const res = await fetch('/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-XSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify({ email: email.value, password: password.value }),
  })

  if (res.status === 422) {
    const data = await res.json()
    errors.value = data.errors || {}
    sessionError.value = data.message || ''
    return
  }

  window.location.href = '/admin'
}
</script>

<template>
  <div class="container">
    <div class="mt-5">
      <div class="card mt-5">
        <div class="p-5">
          <div class="text-center">
            <h1 class="h4 text-gray-900 mb-4">後台管理系統</h1>
          </div>
          <form class="user" @submit.prevent="handleLogin">
            <div class="form-group">
              <input
                type="email"
                class="form-control form-control-user"
                :class="{ 'is-invalid': errors.email }"
                v-model="email"
                placeholder="Enter Email Address..."
                required
                autocomplete="email"
                autofocus
              >
              <span v-if="errors.email" class="invalid-feedback" role="alert">
                <strong>{{ errors.email[0] }}</strong>
              </span>
              <div v-if="sessionError" class="alert alert-danger mt-2">
                {{ sessionError }}
              </div>
            </div>
            <div class="form-group">
              <input
                type="password"
                class="form-control form-control-user"
                :class="{ 'is-invalid': errors.password }"
                v-model="password"
                placeholder="Password"
                required
                autocomplete="current-password"
              >
              <span v-if="errors.password" class="invalid-feedback" role="alert">
                <strong>{{ errors.password[0] }}</strong>
              </span>
            </div>
            <button type="submit" class="btn btn-primary btn-user btn-block">
              Login
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
