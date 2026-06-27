<script setup>
import { ref } from 'vue'
import axios from 'axios';
import { useRouter } from 'vue-router'

const router = useRouter()

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

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = ['請輸入有效的電子郵件地址']
        return
    }
    if (form.value.password.length < 8) {
        errors.value.password = ['密碼至少需要8個字元']
        return
    }
    
    submitting.value = true
    try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/user/login', form.value)    
        const data = response.data
        router.push('/')

    } catch (error) {
        const data = error.response.data
        errors.value = data.errors ?? {}
        errorMessage.value = data.messages ?? ''
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
