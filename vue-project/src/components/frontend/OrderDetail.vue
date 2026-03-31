<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const order = ref(null)
const loading = ref(true)
const counts = ref({ unhandled: 0, shipping: 0 })
const productNotExistVisible = ref(false)

const reviewPopup = ref(null)
const reviewSubmitting = ref(false)

onMounted(async () => {
  await fetchOrder()
})

async function fetchOrder() {
  loading.value = true
  try {
    const res = await fetch(`/api/user/order/${route.params.id}`, {
      headers: { 'Accept': 'application/json' },
    })
    if (res.status === 401) {
      router.push('/login')
      return
    }
    const data = await res.json()
    order.value = data.order
    counts.value = data.counts
  } finally {
    loading.value = false
  }
}

function switchTab(type) {
  router.push({ path: '/orders', query: { type } })
}

function statusLabel(t) {
  const map = { unhandled: '待出貨', shipping: '運送中', completed: '已完成', cancel: '已取消' }
  return map[t] || ''
}

function statusClass(t) {
  const map = { unhandled: 'text-danger', shipping: 'text-primary', completed: 'text-success', cancel: '' }
  return map[t] || ''
}

function paymentLabel(method) {
  return method === 'COD' ? '貨到付款' : method === 'creditCard' ? '信用卡' : method
}

async function cancelOrder() {
  await fetch(`/user/order/to-cancel/${order.value.id}`, {
    headers: { 'Accept': 'application/json' },
  })
  router.push({ path: '/orders', query: { type: 'unhandled' } })
}

async function completeOrder() {
  await fetch(`/user/order/to-completed/${order.value.id}`, {
    headers: { 'Accept': 'application/json' },
  })
  router.push({ path: '/orders', query: { type: 'shipping' } })
}

async function repurchase() {
  const res = await fetch(`/user/order/${order.value.id}/repurchase`, {
    headers: { 'Accept': 'application/json' },
  })
  const data = await res.json()
  if (data.productExists) {
    router.push('/cart')
  } else {
    productNotExistVisible.value = true
    setTimeout(() => { productNotExistVisible.value = false }, 3000)
  }
}

function openReview() {
  reviewPopup.value = {
    items: order.value.order_details.map(d => ({
      title: d.title,
      rating: 0,
      hoverRating: 0,
      comment: '',
    })),
  }
}

function closeReview() {
  reviewPopup.value = null
}

const reviewAllRated = computed(() => {
  if (!reviewPopup.value) return false
  return reviewPopup.value.items.every(item => item.rating > 0)
})

async function submitReview() {
  if (!reviewAllRated.value || reviewSubmitting.value) return
  reviewSubmitting.value = true
  try {
    await fetch('/api/review', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        order_id: order.value.id,
        rate: reviewPopup.value.items.map(i => i.rating),
        review: reviewPopup.value.items.map(i => i.comment),
      }),
    })
    order.value.isReview = true
    closeReview()
  } finally {
    reviewSubmitting.value = false
  }
}
</script>

<template>
  <div v-if="loading" class="title"><p>載入中...</p></div>
  <template v-else-if="order">
    <div class="topbar">
      <div class="row">
        <a @click.prevent="switchTab('unhandled')" href="#">
          <div :class="order.status === 'unhandled' ? 'border' : 'col'">
            <p>待出貨(<span class="count">{{ counts.unhandled }}</span>)</p>
          </div>
        </a>
        <a @click.prevent="switchTab('shipping')" href="#">
          <div :class="order.status === 'shipping' ? 'border' : 'col'">
            <p>待收貨(<span class="count">{{ counts.shipping }}</span>)</p>
          </div>
        </a>
        <a @click.prevent="switchTab('completed')" href="#">
          <div :class="order.status === 'completed' ? 'border' : 'col'">
            <p>已完成</p>
          </div>
        </a>
        <a @click.prevent="switchTab('cancel')" href="#">
          <div :class="order.status === 'cancel' ? 'border' : 'col'">
            <p>已取消</p>
          </div>
        </a>
      </div>
    </div>

    <div id="hidden" class="popup-bg" v-if="reviewPopup">
      <div class="review-popup">
        <div v-for="(item, index) in reviewPopup.items" :key="index" class="review">
          <p>{{ item.title }}</p>
          <div class="rating-box">
            <span
              v-for="star in 5"
              :key="star"
              :class="['empty-stars', (item.hoverRating || item.rating) >= star ? 'full' : '']"
              @mouseover="item.hoverRating = star"
              @mouseout="item.hoverRating = 0"
              @click="item.rating = star"
            ></span>
          </div>
          <div class="review-inner">
            <textarea class="comment" v-model="item.comment"></textarea>
          </div>
        </div>
        <div class="button">
          <button
            v-show="reviewAllRated"
            class="btn right btn-accent"
            :disabled="reviewSubmitting"
            @click="submitReview"
          >送出</button>
          <button
            v-show="!reviewAllRated"
            class="btn right btn-prohibit"
            type="button"
          >送出</button>
          <button class="btn right btn-dark" type="button" @click="closeReview">取消</button>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="order-header">
        <p class="order_number">訂單編號: {{ order.order_number }}</p>
        <p class="order_status">訂單狀態: </p>
        <p class="status" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</p>
      </div>
      <table class="table table-cart">
        <thead>
          <tr>
            <th>商品</th>
            <th>單價</th>
            <th>數量</th>
            <th>金額</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="detail in order.order_details" :key="detail.id">
            <td>
              <RouterLink :to="`/product/${detail.slug}`" class="product-title">
                {{ detail.title }}
              </RouterLink>
            </td>
            <td><p class="text-center">${{ detail.price }}</p></td>
            <td><p class="text-center">{{ detail.quantity }}</p></td>
            <td><p class="text-center">${{ detail.amount }}</p></td>
          </tr>
        </tbody>
      </table>
      <div class="order-info">
        <div>
          <label>備註:</label>
          <p class="text-danger">{{ order.note }}</p>
        </div>
        <div class="amount">
          <label class="m-r-m">合計:</label>
          <p class="text-danger">${{ order.sub_total }}</p>
        </div>
        <div class="receiver-info">
          <label>收件人:</label>
          {{ order.name }}

          <label>手機:</label>
          {{ order.phone }}

          <label>收件地址:</label>
          {{ order.address }}
        </div>
        <div class="payments">
          <label>付款方式:</label>
          <p>{{ paymentLabel(order.payment_method) }}</p>
        </div>
        <div class="total-amount">
          <div>
            <label class="m-r-m">商品:</label>
            <p>${{ order.sub_total }}</p>
          </div>
          <div>
            <label class="m-r-m">運費:</label>
            <p>${{ order.shipping_fee }}</p>
          </div>
          <div>
            <label class="m-r-m">應付:</label>
            <p class="text-danger">${{ order.total_amount }}</p>
          </div>
        </div>
        <div class="button">
          <template v-if="order.status === 'unhandled'">
            <button class="btn right btn-dark" @click="cancelOrder">取消訂單</button>
          </template>
          <template v-else-if="order.status === 'shipping'">
            <button class="btn right btn-accent" @click="completeOrder">完成訂單</button>
          </template>
          <template v-else-if="order.status === 'completed'">
            <button class="btn right btn-dark" @click="repurchase">重新購買</button>
            <button v-if="!order.isReview" class="btn right m-r-s m-l-s btn-accent" @click="openReview">評價</button>
          </template>
          <template v-else-if="order.status === 'cancel'">
            <button class="btn right btn-dark" @click="repurchase">重新購買</button>
          </template>
        </div>
      </div>
    </div>

    <div class="hidden popup-content" v-show="productNotExistVisible">
      <p>商品不存在</p>
    </div>
  </template>
</template>
