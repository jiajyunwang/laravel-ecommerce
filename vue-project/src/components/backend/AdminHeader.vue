<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const counts = ref({ activeProducts: 0, inactiveProducts: 0, unhandledOrders: 0, shippingOrders: 0 })

onMounted(fetchStats)
watch(() => route.path, fetchStats)

async function fetchStats() {
  const res = await fetch('/api/admin/stats', {
    headers: { 'Accept': 'application/json' },
  })
  if (res.ok) {
    counts.value = await res.json()
  }
}

function logout() {
  window.location.href = '/logout'
}

const activeTab = computed(() => {
  const path = route.path
  const type = route.query.type
  if (path.startsWith('/admin/product')) {
    return type === 'unlisted' ? 'unlisted' : 'product'
  }
  if (path.startsWith('/admin/order')) {
    return type || 'unhandled'
  }
  return ''
})
</script>

<template>
  <div class="logout">
    <input type="button" class="btn-logout right" value="登出" @click="logout">
  </div>
  <div class="topbar">
    <div class="row">
      <a href="/admin/product">
        <div :class="activeTab === 'product' ? 'border' : 'col'">
          <p>商品(<p>{{ counts.activeProducts }}</p>)</p>
        </div>
      </a>
      <a href="/admin/product?type=unlisted">
        <div :class="activeTab === 'unlisted' ? 'border' : 'col'">
          <p>已下架(<span>{{ counts.inactiveProducts }}</span>)</p>
        </div>
      </a>
      <a href="/admin/order?type=unhandled">
        <div :class="activeTab === 'unhandled' ? 'border' : 'col'">
          <p>待出貨(<span class="count">{{ counts.unhandledOrders }}</span>)</p>
        </div>
      </a>
      <a href="/admin/order?type=shipping">
        <div :class="activeTab === 'shipping' ? 'border' : 'col'">
          <p>待收貨(<span class="count">{{ counts.shippingOrders }}</span>)</p>
        </div>
      </a>
      <a href="/admin/order?type=completed">
        <div :class="activeTab === 'completed' ? 'border' : 'col'">
          <p>已完成</p>
        </div>
      </a>
      <a href="/admin/order?type=cancel">
        <div :class="activeTab === 'cancel' ? 'border' : 'col'">
          <p>已取消</p>
        </div>
      </a>
    </div>
  </div>
</template>
