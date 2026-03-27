<script setup>
import { ref, computed, onMounted } from 'vue'

const searchInput = ref('')
const currentSearch = ref('')
const sortBy = ref('_score')
const sortOrder = ref('desc')
const currentPage = ref(1)
const lastPage = ref(1)
const products = ref([])

const isSearch = computed(() => !!currentSearch.value)

const pageRange = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(lastPage.value, currentPage.value + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

async function fetchProducts() {
  let url
  if (isSearch.value) {
    const params = new URLSearchParams({
      search: currentSearch.value,
      sortBy: sortBy.value,
      sortOrder: sortOrder.value,
      page: currentPage.value
    })
    url = `/api/products/search?${params}`
  } else {
    url = `/api/products?page=${currentPage.value}`
  }
  const response = await fetch(url)
  const data = await response.json()
  products.value = data.data
  currentPage.value = data.current_page
  lastPage.value = data.last_page
}

function updateURL() {
  const params = new URLSearchParams()
  if (currentSearch.value) {
    params.set('search', currentSearch.value)
    if (sortBy.value !== '_score') {
      params.set('sortBy', sortBy.value)
      params.set('sortOrder', sortOrder.value)
    }
  }
  if (currentPage.value > 1) params.set('page', currentPage.value)
  const query = params.toString()
  history.pushState({}, '', query ? `?${query}` : window.location.pathname)
}

function doSearch() {
  if (!searchInput.value.trim()) return
  currentSearch.value = searchInput.value.trim()
  sortBy.value = '_score'
  sortOrder.value = 'desc'
  currentPage.value = 1
  updateURL()
  fetchProducts()
}

function sortByRelevance() {
  sortBy.value = '_score'
  sortOrder.value = 'desc'
  currentPage.value = 1
  updateURL()
  fetchProducts()
}

function sortByPrice() {
  if (sortBy.value === 'price' && sortOrder.value === 'asc') {
    sortOrder.value = 'desc'
  } else {
    sortBy.value = 'price'
    sortOrder.value = 'asc'
  }
  currentPage.value = 1
  updateURL()
  fetchProducts()
}

function changePage(page) {
  currentPage.value = page
  updateURL()
  fetchProducts()
  window.scrollTo(0, 0)
}

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const search = params.get('search')
  if (search) {
    currentSearch.value = search
    searchInput.value = search
    sortBy.value = params.get('sortBy') || '_score'
    sortOrder.value = params.get('sortOrder') || 'desc'
  }
  currentPage.value = parseInt(params.get('page') || '1')
  fetchProducts()
})
</script>

<template>
  <div class="search-bar">
    <input v-model="searchInput" name="search" placeholder="搜尋商品" type="search" @keyup.enter="doSearch">
    <button type="submit" @click="doSearch"><i class="ti-search"></i></button>
  </div>

  <div class="product-area">
    <div v-if="isSearch" class="search-panel">
      <span>搜尋 '{{ currentSearch }}'</span>
      <div class="sort-by">
        <span class="cursor" :class="{ active: sortBy === '_score' }" @click="sortByRelevance">最相關</span>
        <span>|</span>
        <span
          class="cursor"
          :class="{ active: sortBy === 'price' }"
          @click="sortByPrice"
        >價格{{ (sortBy === 'price' && sortOrder === 'desc') ? '🔻' : '🔺' }}</span>
      </div>
    </div>

    <div class="product-list">
      <div v-for="product in products" :key="product.id" class="single-product">
        <a :href="`/product-detail/${product.id}`">
          <img class="product-img" :src="product.photo">
        </a>
        <p class="product-title">{{ product.title }}</p>
        <div class="rate-average">
          <p class="m-0">{{ product.average }}</p>
          <div class="ratings">
            <div class="empty-stars"></div>
            <div class="full-stars" :style="{ width: product.percentage + '%' }"></div>
          </div>
          <p class="text-center">({{ product.reviewCount }})</p>
        </div>
        <p class="product-price">${{ product.price }}</p>
      </div>
    </div>

    <div class="bg-light center">
      <ul v-if="lastPage > 1" class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">‹</button>
        </li>
        <li
          v-for="page in pageRange"
          :key="page"
          class="page-item"
          :class="{ active: page === currentPage }"
        >
          <button class="page-link" @click="changePage(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === lastPage }">
          <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage">›</button>
        </li>
      </ul>
    </div>
  </div>
</template>
