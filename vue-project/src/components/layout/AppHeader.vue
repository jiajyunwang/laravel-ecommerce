<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const user = ref(null)
const cartCount = ref(0)

onMounted(fetchUserInfo)
watch(() => route.path, fetchUserInfo)

async function fetchUserInfo() {
  const res = await fetch('/api/user/info', {
    headers: { 'Accept': 'application/json' },
  })
  if (res.ok) {
    const data = await res.json()
    user.value = data.user
    cartCount.value = data.cartCount
  }
}

async function logout() {
  await fetch('/logout', { headers: { 'Accept': 'application/json' } })
  user.value = null
  cartCount.value = 0
  router.push('/')
}
</script>

<template>
  <div class="header-inner">
    <div class="items left">
      <RouterLink to="/">首頁</RouterLink>
    </div>
    <div class="items right">
      <template v-if="user">
        <RouterLink to="/cart">
          <i class="ti-shopping-cart-full"></i>
          <p class="text-transparent">購物車(<span class="count">{{ cartCount }}</span>)</p>
        </RouterLink>&emsp;
        <ul class="dropdown">
          <li>
            <a>{{ user.email }}</a>
            <ul>
              <li><RouterLink to="/account">我的帳戶</RouterLink></li>
              <li><RouterLink to="/orders">訂單查詢</RouterLink></li>
              <li><a href="#" @click.prevent="logout">登出</a></li>
            </ul>
          </li>
        </ul>
      </template>
      <template v-else>
        <RouterLink to="/login">登入</RouterLink>
        <nobr>︱</nobr>
        <RouterLink to="/register">註冊</RouterLink>
      </template>
    </div>
  </div>
</template>
