<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { watch } from 'vue'
import axios from 'axios';


const route = useRoute()
const router = useRouter()

const searchInput = ref('')
const currentSearch = ref('')
const sortBy = ref('_score')
const sortOrder = ref('desc')
const products = ref([])

const isSearch = computed(() => !!currentSearch.value)

async function fetchProducts() {
    let url
    if (isSearch.value) {
        const params = new URLSearchParams({
        search: currentSearch.value,
        sortBy: sortBy.value,
        sortOrder: sortOrder.value,
        })
        url = `/api/products/search?${params}`
    } else {
        url = `/api/products`
    }
    // const response = await fetch(url)
    // const data = await response.json()
    // products.value = data.data
    const response = await axios.get(url)
    products.value = response.data.data
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
    const query = params.toString()
    if (query) {
        router.push(`search?${query}`)
    } else {
        router.push(window.location.pathname)
    }
}

function doSearch() {
    if (!searchInput.value.trim()) return
    currentSearch.value = searchInput.value.trim()
    sortBy.value = '_score'
    sortOrder.value = 'desc'
    updateURL()
    fetchProducts()
}

function sortByRelevance() {
    sortBy.value = '_score'
    sortOrder.value = 'desc'
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
    updateURL()
    fetchProducts()
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
    fetchProducts()
})

watch(
  () => route.path,
  (path) => {
    if (path === '/') {
        currentSearch.value = false
        fetchProducts()
    }
  }
)
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
            <a :href="`/product/${product.id}`">
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
    </div>
</template>
