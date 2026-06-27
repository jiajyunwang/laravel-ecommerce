<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios';

const route = useRoute()
const router = useRouter()

const orders = ref([])
const type = ref('unhandled')
const page = ref(1)
const loading = ref(false)
const noMore = ref(false)
const counts = ref({ unhandled: 0, shipping: 0 })
const productNotExistVisible = ref(false)

const reviewPopup = ref(null) // { orderId, items: [{title, rating, hoverRating, comment}] }
const reviewSubmitting = ref(false)

const sentinel = ref(null)
let observer = null

onMounted(async () => {
    type.value = route.query.type || 'unhandled'
    await fetchOrders()
    await nextTick()
    setupObserver()
})

onUnmounted(() => {
    if (observer) observer.disconnect()
})

watch(() => route.query.type, async (newType) => {
    type.value = newType || 'unhandled'
    orders.value = []
    page.value = 1
    noMore.value = false
    if (observer) {
        observer.disconnect()
        observer = null
    }
    await fetchOrders()
    await nextTick()
    setupObserver()
})

function setupObserver() {
    if (!sentinel.value) return
    observer = new IntersectionObserver(async (entries) => {
        if (entries[0].isIntersecting && !loading.value && !noMore.value) {
            page.value++
            await fetchOrders()
        }
    })
    observer.observe(sentinel.value)
}

async function fetchOrders() {
    loading.value = true
    try {
        const res = await axios.get(`/api/user/orders?type=${type.value}&page=${page.value}`);
        const data = res.data
        if (page.value === 1) {
            orders.value = data.orders
        } else {
            orders.value.push(...data.orders)
        }
        counts.value = data.counts
        noMore.value = !data.hasMore
    } finally {
        loading.value = false
    }
}

function switchTab(newType) {
    router.push({ path: '/orders', query: { type: newType } })
}

function statusLabel(t) {
    const map = { unhandled: '待出貨', shipping: '運送中', completed: '已完成', cancel: '已取消' }
    return map[t] || ''
}

function statusClass(t) {
    const map = { unhandled: 'text-danger', shipping: 'text-primary', completed: 'text-success', cancel: '' }
    return map[t] || ''
}

async function cancelOrder(id) {
    try {
        await axios.get(`/user/order/to-cancel/${id}`);
        // orders.value = orders.value.filter(o => o.id !== id)
        // counts.value.unhandled = Math.max(0, counts.value.unhandled - 1)
        switchTab('cancel')
    } finally {
    }
}

async function completeOrder(id) {
    await fetch(`/user/order/to-completed/${id}`, {
        headers: { 'Accept': 'application/json' },
    })
    orders.value = orders.value.filter(o => o.id !== id)
    counts.value.shipping = Math.max(0, counts.value.shipping - 1)
}

async function repurchase(orderId) {
    const res = await fetch(`/user/order/${orderId}/repurchase`, {
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

function openReview(order) {
    reviewPopup.value = {
        orderId: order.id,
        items: order.order_details.map(d => ({
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
                order_id: reviewPopup.value.orderId,
                rate: reviewPopup.value.items.map(i => i.rating),
                review: reviewPopup.value.items.map(i => i.comment),
            }),
        })
        const order = orders.value.find(o => o.id === reviewPopup.value.orderId)
        if (order) order.isReview = true
        closeReview()
    } finally {
        reviewSubmitting.value = false
    }
}
</script>

<template>
    <div class="topbar">
        <div class="row">
            <a @click.prevent="switchTab('unhandled')" href="#">
                <div :class="type === 'unhandled' ? 'border' : 'col'">
                    <p>待出貨(<span class="count">{{ counts.unhandled }}</span>)</p>
                </div>
            </a>
            <a @click.prevent="switchTab('shipping')" href="#">
                <div :class="type === 'shipping' ? 'border' : 'col'">
                    <p>待收貨(<span class="count">{{ counts.shipping }}</span>)</p>
                </div>
            </a>
            <a @click.prevent="switchTab('completed')" href="#">
                <div :class="type === 'completed' ? 'border' : 'col'">
                    <p>已完成</p>
                </div>
            </a>
            <a @click.prevent="switchTab('cancel')" href="#">
                <div :class="type === 'cancel' ? 'border' : 'col'">
                    <p>已取消</p>
                </div>
            </a>
        </div>
    </div>

    <div class="hidden popup-content" v-show="productNotExistVisible">
        <p>商品不存在</p>
    </div>

    <template v-for="order in orders" :key="order.id">
        <div id="hidden" class="popup-bg" v-if="reviewPopup && reviewPopup.orderId === order.id">
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
                <p class="status" :class="statusClass(type)">{{ statusLabel(type) }}</p>
            </div>
            <RouterLink :to="`/order/${order.id}`">
                <table class="table table-cart">
                    <thead>
                        <tr>
                            <th>商品</th>
                            <th>數量</th>
                            <th>金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="detail in order.order_details" :key="detail.id">
                            <td><p class="product-title">{{ detail.title }}</p></td>
                            <td><p class="text-center">x{{ detail.quantity }}</p></td>
                            <td><p class="text-center">${{ detail.amount }}</p></td>
                        </tr>
                    </tbody>
                </table>
            </RouterLink>
            <div class="amount">
                <label class="m-r-m">訂單合計:</label>
                <p class="text-danger">${{ order.total_amount }}</p>
            </div>
            <div class="center">
                <template v-if="type === 'unhandled'">
                    <button class="btn m-t-m m-r-s m-l-s btn-dark" @click="cancelOrder(order.id)">取消訂單</button>
                </template>
                <template v-else-if="type === 'shipping'">
                    <button class="btn m-t-m m-r-s m-l-s btn-accent" @click="completeOrder(order.id)">完成訂單</button>
                </template>
                <template v-else-if="type === 'completed'">
                    <button v-if="!order.isReview" class="btn m-t-m m-r-s m-l-s btn-accent" @click="openReview(order)">評價</button>
                    <button class="btn m-t-m m-r-s m-l-s btn-dark" @click="repurchase(order.id)">重新購買</button>
                </template>
                <template v-else-if="type === 'cancel'">
                    <button class="btn m-t-m btn-dark" @click="repurchase(order.id)">重新購買</button>
                </template>
            </div>
        </div>
    </template>

    <div ref="sentinel" class="center grid-colum-2">
        <div v-if="loading" class="loader"></div>
        <div v-else-if="noMore && orders.length > 0">無更多訂單</div>
    </div>
</template>
