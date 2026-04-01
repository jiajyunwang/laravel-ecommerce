<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const products = ref([])
const selectedIds = ref([])
const loading = ref(false)

const type = computed(() => route.query.type === 'unlisted' ? 'unlisted' : 'listed')

const allSelected = computed({
  get: () => products.value.length > 0 && selectedIds.value.length === products.value.length,
  set: (val) => {
    selectedIds.value = val ? products.value.map(p => p.id) : []
  },
})

onMounted(fetchProducts)
watch(type, fetchProducts)

async function fetchProducts() {
  loading.value = true
  try {
    const res = await fetch(`/api/admin/products?type=${type.value}`, {
      headers: { 'Accept': 'application/json' },
    })
    products.value = await res.json()
    selectedIds.value = []
  } finally {
    loading.value = false
  }
}

function toggleSelect(id) {
  const idx = selectedIds.value.indexOf(id)
  if (idx === -1) selectedIds.value.push(id)
  else selectedIds.value.splice(idx, 1)
}

function getCsrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

async function apiPost(url, body = null) {
  const opts = {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
  }
  if (body) {
    opts.headers['Content-Type'] = 'application/json'
    opts.body = JSON.stringify(body)
  }
  await fetch(url, opts)
  await fetchProducts()
}

const toInactive = (id) => apiPost(`/api/admin/product/to-inactive/${id}`)
const toActive   = (id) => apiPost(`/api/admin/product/to-active/${id}`)
const singleDelete = (id) => apiPost(`/api/admin/product/destroy/${id}`)

async function batchDelete() {
  if (!selectedIds.value.length) return
  await apiPost('/api/admin/product/destroy-products', { check: selectedIds.value })
}

function editProduct(id) {
  window.location.href = `/admin/product/${id}/edit`
}
</script>

<template>
  <div class="card">
    <div class="card-header bg-light">
      <div class="left">
        <div class="check-all">
          <input type="checkbox" v-model="allSelected">
          <label>全選</label>
        </div>&emsp;
        <div class="btn-delete">
          <button id="delete" type="button" @click="batchDelete">刪除</button>
        </div>
      </div>
      <div v-if="type === 'listed'" class="right">
        <div class="btn-product-create right">
          <a href="/admin/product/create">
            <div><p><i class="ti-plus"></i> 新增商品</p></div>
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div v-if="loading" class="text-center">載入中...</div>
      <table v-else class="table table-product" id="product-dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>商品</th>
            <th>價錢</th>
            <th>庫存</th>
            <th>圖片</th>
            <th>操作</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th>商品</th>
            <th>價錢</th>
            <th>庫存</th>
            <th>圖片</th>
            <th>操作</th>
          </tr>
        </tfoot>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div class="product-title">
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(product.id)"
                  @change="toggleSelect(product.id)"
                >
                <a :href="`/product/${product.id}`">{{ product.title }}</a>
              </div>
            </td>
            <td class="text-center"><p>{{ product.price }}</p></td>
            <td class="text-center"><p>{{ product.stock }}</p></td>
            <td class="text-center">
              <img :src="product.photo" style="max-width:80px">
            </td>
            <td>
              <button title="編輯" class="btn-edit" @click="editProduct(product.id)">
                <i class="ti-pencil-alt"></i>
              </button>
              <button v-if="type === 'listed'" title="下架" class="to-inactive" @click="toInactive(product.id)">
                <i class="ti-import"></i>
              </button>
              <button v-else-if="type === 'unlisted'" title="上架" class="to-active" @click="toActive(product.id)">
                <i class="ti-export"></i>
              </button>
              <button title="刪除" class="btn-delete" @click="singleDelete(product.id)">
                <i class="ti-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
