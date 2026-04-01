<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const orders = ref([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const searchNumber = ref('')
const searchError = ref('')
const expanded = ref({})

const type = computed(() => {
  const t = route.query.type
  return ['unhandled', 'shipping', 'completed', 'cancel'].includes(t) ? t : 'unhandled'
})

const statusMap = {
  unhandled: { label: '待出貨', cls: 'text-danger' },
  shipping:  { label: '待收貨', cls: 'text-primary' },
  completed: { label: '完成',   cls: 'text-success' },
  cancel:    { label: '取消',   cls: '' },
}

const paymentMap = { COD: '貨到付款', creditCard: '信用卡' }

onMounted(fetchOrders)
watch(type, () => {
  currentPage.value = 1
  searchNumber.value = ''
  searchError.value = ''
  expanded.value = {}
  fetchOrders()
})

async function fetchOrders() {
  loading.value = true
  try {
    const res = await fetch(`/api/admin/orders?type=${type.value}&page=${currentPage.value}`, {
      headers: { 'Accept': 'application/json' },
    })
    const data = await res.json()
    orders.value = data.orders
    lastPage.value = data.lastPage
  } finally {
    loading.value = false
  }
}

function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

async function apiPost(url) {
  await fetch(url, {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
  })
  await fetchOrders()
}

const toShipping = (id) => apiPost(`/api/admin/order/to-shipping/${id}`)
const toCancel   = (id) => apiPost(`/api/admin/order/to-cancel/${id}`)

function downloadInvoice(id) {
  window.open(`/admin/orders/${id}/invoice`, '_blank')
}

async function search() {
  searchError.value = ''
  if (!searchNumber.value.trim()) {
    currentPage.value = 1
    await fetchOrders()
    return
  }
  loading.value = true
  try {
    const res = await fetch(`/api/admin/order/search?orderNumber=${encodeURIComponent(searchNumber.value.trim())}`, {
      headers: { 'Accept': 'application/json' },
    })
    const data = await res.json()
    if (!res.ok) {
      searchError.value = data.message || '訂單不存在'
      orders.value = []
      return
    }
    orders.value = data.orders
    lastPage.value = data.lastPage
    currentPage.value = 1
  } finally {
    loading.value = false
  }
}

function toggleExpand(id) {
  expanded.value[id] = !expanded.value[id]
}

async function goToPage(page) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  await fetchOrders()
}
</script>

<template>
  <div class="card">
    <form class="form-product" @submit.prevent="search">
      <div class="card-header bg-light">
        <div class="order-search">
          <input v-model="searchNumber" placeholder="訂單編號">
          <button type="submit">訂單查詢</button>
          <div v-if="searchError" class="popup-content">
            <p>{{ searchError }}</p>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center">載入中...</div>
        <table v-else class="table table-product" id="product-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>訂單編號</th>
              <th>收件人</th>
              <th>付款方式</th>
              <th>金額</th>
              <th>狀態</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="order in orders" :key="order.id">
              <tr>
                <td class="text-center nowrap">{{ order.order_number }}</td>
                <td class="text-center nowrap">{{ order.name }}</td>
                <td class="text-center nowrap">{{ paymentMap[order.payment_method] ?? order.payment_method }}</td>
                <td class="text-center nowrap">${{ order.total_amount }}</td>
                <td class="text-center nowrap" :class="statusMap[order.status]?.cls">
                  {{ statusMap[order.status]?.label }}
                </td>
                <td class="text-center">
                  <a class="operation nowrap" href="#" @click.prevent="downloadInvoice(order.id)">下載</a>
                  <template v-if="type === 'unhandled'">
                    <a class="operation nowrap" href="#" @click.prevent="toShipping(order.id)">出貨</a>
                    <a class="operation nowrap" href="#" @click.prevent="toCancel(order.id)">取消</a>
                  </template>
                </td>
              </tr>
              <tr>
                <td colspan="6">
                  <div class="text-center flip m-b-m" style="cursor:pointer" @click="toggleExpand(order.id)">
                    <p class="m-0">訂單明細{{ expanded[order.id] ? '🔺' : '🔻' }}</p>
                  </div>
                  <template v-if="expanded[order.id]">
                    <div class="panel">
                      <div v-for="detail in order.order_details" :key="detail.id">
                        <p class="order-detail">{{ detail.title }}</p>
                        <p class="order-detail">x{{ detail.quantity }}</p>
                        <p class="order-detail">${{ detail.amount }}</p>
                      </div>
                    </div>
                    <div class="panel">
                      備註:<p class="text-danger">{{ order.note }}</p>
                    </div>
                  </template>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
        <div v-if="lastPage > 1" class="center">
          <button type="button" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">«</button>
          <button
            v-for="p in lastPage" :key="p"
            type="button"
            :class="{ active: p === currentPage }"
            @click="goToPage(p)"
          >{{ p }}</button>
          <button type="button" :disabled="currentPage === lastPage" @click="goToPage(currentPage + 1)">»</button>
        </div>
      </div>
    </form>
  </div>
</template>
