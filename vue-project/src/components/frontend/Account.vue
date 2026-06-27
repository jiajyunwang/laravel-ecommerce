<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios';

const form = ref({
    email: '',
    nickname: '',
    name: '',
    cellphone: '',
    address: '',
})

const errors = ref({})
const submitting = ref(false)
const showSuccess = ref(false)

async function fetchUser() {
    const response = await axios.get('/user/account')    
    // if (response.status === 401) {
    //   window.location.href = '/login'
    //   return
    // }
    const data = response.data
    form.value.email     = data.email     ?? ''
    form.value.nickname  = data.nickname  ?? ''
    form.value.name      = data.name      ?? ''
    form.value.cellphone = data.cellphone ?? ''
    form.value.address   = data.address   ?? ''
}

async function submit() {
    errors.value = {}
    submitting.value = true
    try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/user/account', form.value)    
        const data = response.data
        showSuccess.value = true
        setTimeout(() => { showSuccess.value = false }, 2000)

    } catch (error) {
        const data = error.response.data
        errors.value = data.errors ?? {}
        errorMessage.value = data.messages ?? ''
    } finally { 
        submitting.value = false
    }
}

function closeOverlay() {
    showSuccess.value = false
}

onMounted(fetchUser)
</script>

<template>
    <p class="title">我的帳戶</p>
    <form class="form" @submit.prevent="submit">
        <div class="user-email">
            <label>Email</label>&emsp;<p>{{ form.email }}</p>
        </div>

        <label>暱稱<span>*</span></label>
        <input v-model="form.nickname" type="text">
        <span v-if="errors.nickname" class="error">{{ errors.nickname[0] }}</span>

        <label>姓名</label>
        <input v-model="form.name" type="text">
        <span v-if="errors.name" class="error">{{ errors.name[0] }}</span>

        <label>手機號碼</label>
        <input v-model="form.cellphone" type="tel">
        <span v-if="errors.cellphone" class="error">{{ errors.cellphone[0] }}</span>

        <label>地址</label>
        <input v-model="form.address" type="text">
        <span v-if="errors.address" class="error">{{ errors.address[0] }}</span>

        <button type="submit" :disabled="submitting">儲存</button>
    </form>

    <div v-if="showSuccess" id="overlay" @click="closeOverlay">
        <div class="popup">
            <div class="success"><i class="ti-check"></i></div>
            <p>檔案已更新</p>
        </div>
    </div>
</template>
