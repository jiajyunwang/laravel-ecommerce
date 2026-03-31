<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const product = ref(null)
const average = ref(0)
const percentage = ref(0)
const reviewCount = ref(0)
const homeDeliveryFee = ref(0)
const alreadyInCart = ref(0)

const quantity = ref(1)
const showUnderstock = ref(false)
const showSoldOut = ref(false)
const showUpperLimit = ref(false)
const showCartSuccess = ref(false)
const cartSubmitting = ref(false)

const reviews = ref([])
const reviewPage = ref(0)
const reviewSortBy = ref('created_at')
const reviewSortOrder = ref('desc')
const reviewLoading = ref(false)
const reviewAllLoaded = ref(false)
const loadingIndicatorRef = ref(null)
const activeSort = ref('newest')

const searchInput = ref('')

const stock = computed(() => product.value?.stock ?? 0)
const maxQty = computed(() => stock.value - alreadyInCart.value)

async function fetchProduct() {
  const response = await fetch(`/api/products/${route.params.id}`, {
    headers: { 'Accept': 'application/json' },
  })
  if (!response.ok) return
  const data = await response.json()
  product.value = data.product
  average.value = data.average
  percentage.value = data.percentage
  reviewCount.value = data.reviewCount
  homeDeliveryFee.value = data.homeDeliveryFee
  alreadyInCart.value = data.alreadyInCart ?? 0
}

function increment() {
  showUnderstock.value = false
  showSoldOut.value = false
  if (quantity.value < maxQty.value) {
    quantity.value++
    showUpperLimit.value = quantity.value >= maxQty.value
    if (quantity.value >= maxQty.value) quantity.value = maxQty.value
  } else {
    quantity.value = maxQty.value
    showUpperLimit.value = true
  }
}

function decrement() {
  showUnderstock.value = false
  showSoldOut.value = false
  showUpperLimit.value = false
  quantity.value = Math.max(1, quantity.value - 1)
}

function onQuantityInput() {
  showUnderstock.value = false
  showSoldOut.value = false
  const val = parseInt(quantity.value)
  if (isNaN(val) || val < 1) {
    quantity.value = 1
    showUpperLimit.value = false
  } else if (val > maxQty.value) {
    quantity.value = maxQty.value
    showUpperLimit.value = true
  } else {
    showUpperLimit.value = false
  }
}

function onQuantityBlur() {
  if (isNaN(parseInt(quantity.value))) {
    quantity.value = 1
    showUpperLimit.value = false
  }
}

async function addToCart() {
  if (cartSubmitting.value) return
  cartSubmitting.value = true
  showUnderstock.value = false
  showSoldOut.value = false
  showUpperLimit.value = false
  try {
    const response = await fetch(
      `/request-action/${route.params.id}?requestAction=cart&quantity=${quantity.value}`,
      { headers: { 'Accept': 'application/json' } }
    )
    const data = await response.json()
    if (data.notEnough) {
      showUnderstock.value = true
    } else if (data.finish) {
      showSoldOut.value = true
    } else if (data.success) {
      showCartSuccess.value = true
      setTimeout(() => { showCartSuccess.value = false }, 2000)
    }
  } finally {
    cartSubmitting.value = false
  }
}

function directBuy() {
  window.location.href = `/request-action/${route.params.id}?requestAction=checkout&quantity=${quantity.value}`
}

function doSearch() {
  if (searchInput.value.trim()) {
    router.push({ path: '/', query: { search: searchInput.value.trim() } })
  }
}

async function fetchReviews() {
  if (reviewLoading.value || reviewAllLoaded.value) return
  reviewLoading.value = true
  reviewPage.value++
  try {
    const response = await fetch(
      `/reviews/fetch?page=${reviewPage.value}&sort_by=${reviewSortBy.value}&sort_order=${reviewSortOrder.value}&product_id=${route.params.id}`
    )
    const data = await response.json()
    if (data.data.length === 0) {
      reviewAllLoaded.value = true
    } else {
      reviews.value.push(...data.data.map(r => ({
        ...r,
        percentage: (r.rate / 5) * 100,
        formattedDate: new Date(r.created_at).toISOString().split('T')[0],
      })))
    }
  } finally {
    reviewLoading.value = false
  }
}

function setSort(key, sortBy, sortOrder) {
  activeSort.value = key
  reviewSortBy.value = sortBy
  reviewSortOrder.value = sortOrder
  reviewPage.value = 0
  reviews.value = []
  reviewAllLoaded.value = false
  fetchReviews()
}

let observer = null

onMounted(async () => {
  await fetchProduct()
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) fetchReviews()
  })
  if (loadingIndicatorRef.value) observer.observe(loadingIndicatorRef.value)
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})
</script>

<template>
  <div v-if="product">
    <div class="search-bar">
      <input v-model="searchInput" name="search" placeholder="搜尋商品" type="search" @keyup.enter="doSearch">
      <button type="submit" @click="doSearch"><i class="ti-search"></i></button>
    </div>

    <div class="product-briefing">
      <div class="product-img">
        <img :src="product.photo">
      </div>
      <div class="product-info">
        <div class="product-title">
          <p>{{ product.title }}</p>
          <div class="rate-average">
            <p class="m-0">{{ average }}</p>
            <div class="ratings">
              <div class="empty-stars"></div>
              <div class="full-stars" :style="{ width: percentage + '%' }"></div>
            </div>
            <p class="text-center">({{ reviewCount }})</p>
          </div>
        </div>
        <div class="price">
          <p>\${{ product.price }}</p>
        </div>
        <div class="delivery">
          <i class="ti-truck"></i><p>宅配\${{ homeDeliveryFee }}</p>
        </div>
        <div class="stock">
          <label>購買數量 </label>
          <div class="form-group">
            <div class="minus">
              <i class="ti-minus"></i>
              <input type="button" class="qtyminus" @click="decrement">
            </div>
            <div>
              <input
                v-model="quantity"
                type="text"
                name="quantity"
                class="qty"
                @input="onQuantityInput"
                @blur="onQuantityBlur"
              >
            </div>
            <div class="plus">
              <i class="ti-plus"></i>
              <input type="button" class="qtyplus" @click="increment">
            </div>
            <p>(庫存{{ stock }}件)</p>
            <span v-show="showUnderstock" id="understock">庫存不足，請重新選取數量。</span>
            <span v-show="showSoldOut" id="sold-out">已售完</span>
            <span v-show="showUpperLimit" id="upper-limit">已達購買上限</span>
          </div>
        </div>
        <div class="checkout">
          <button type="button" @click="directBuy">直接購買</button>
        </div>
        <div class="add-to-cart">
          <button id="cart" type="button" :disabled="cartSubmitting" @click="addToCart">
            <i class="ti-shopping-cart"></i>&thinsp;加入購物車
          </button>
        </div>
      </div>
    </div>

    <div class="product-description">
      <div class="card-header">
        <label>商品詳情</label>
      </div>
      <div class="description-body" v-html="product.description"></div>
    </div>

    <div class="product-review">
      <div class="card-header">
        <label>評價</label>
      </div>
      <div class="description-body">
        <div class="nav m-b-m">
          <div class="rate-average">
            <p class="fs-l fw m-0 text-center">{{ average }}</p>
            <div class="ratings">
              <div class="empty-stars"></div>
              <div class="full-stars" :style="{ width: percentage + '%' }"></div>
            </div>
            <p class="text-center">({{ reviewCount }})</p>
          </div>
          <div class="nav-tabs">
            <button
              :class="['btn', 'sort-button', { active: activeSort === 'newest' }]"
              type="button"
              @click="setSort('newest', 'created_at', 'desc')"
            >最新</button>
            <button
              :class="['btn', 'sort-button', { active: activeSort === 'high' }]"
              type="button"
              @click="setSort('high', 'rate', 'desc')"
            >最高評分</button>
            <button
              :class="['btn', 'sort-button', { active: activeSort === 'low' }]"
              type="button"
              @click="setSort('low', 'rate', 'asc')"
            >最低評分</button>
          </div>
        </div>

        <div id="review-container">
          <div v-for="review in reviews" :key="review.id" class="review-inner m-b-m">
            <p class="m-0">{{ review.users.nickname }}</p>
            <div class="ratings">
              <div class="empty-stars"></div>
              <div class="full-stars" :style="{ width: review.percentage + '%' }"></div>
            </div>
            <p class="m-b-l">{{ review.review }}</p>
            <p class="date">{{ review.formattedDate }}</p>
          </div>
        </div>

        <div ref="loadingIndicatorRef" id="loading-indicator">
          <span v-if="reviewAllLoaded">無更多評價</span>
        </div>
      </div>
    </div>

    <div v-if="showCartSuccess" id="hidden" class="popup-content">
      <p>已成功加入購物車</p>
    </div>
  </div>
</template>
