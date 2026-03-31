<script setup>
import { ref, computed, onMounted } from 'vue'

const carts = ref([])
const selectedIds = ref([])
const loading = ref(false)

const allSelected = computed({
  get: () => carts.value.length > 0 && selectedIds.value.length === carts.value.length,
  set: (val) => {
    selectedIds.value = val ? carts.value.map(c => c.id) : []
  }
})

const hasSelected = computed(() => selectedIds.value.length > 0)
const hasItems = computed(() => carts.value.length > 0)

async function fetchCart() {
  loading.value = true
  try {
    const response = await fetch('/api/user/cart', {
      headers: { 'Accept': 'application/json' },
    })
    if (response.status === 401) {
      window.location.href = '/login'
      return
    }
    const data = await response.json()
    carts.value = data
    selectedIds.value = data.map(c => c.id)
  } finally {
    loading.value = false
  }
}

function toggleSelect(id) {
  const idx = selectedIds.value.indexOf(id)
  if (idx === -1) selectedIds.value.push(id)
  else selectedIds.value.splice(idx, 1)
}

function isSelected(id) {
  return selectedIds.value.includes(id)
}

async function updateQuantity(cart) {
  const qty = parseInt(cart.quantity)
  if (isNaN(qty) || qty < 1) cart.quantity = 1
  else if (qty > cart.product.stock) cart.quantity = cart.product.stock
  else cart.quantity = qty
  cart.amount = cart.quantity * cart.price

  await fetch('/api/cart-update', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      product_id: cart.product_id,
      quantity: cart.quantity,
      amount: cart.amount,
    }),
  })
}

function increment(cart) {
  if (cart.quantity < cart.product.stock) {
    cart.quantity++
  } else {
    cart.quantity = cart.product.stock
  }
  cart.amount = cart.quantity * cart.price
  updateQuantity(cart)
}

function decrement(cart) {
  cart.quantity = Math.max(1, cart.quantity - 1)
  cart.amount = cart.quantity * cart.price
  updateQuantity(cart)
}

function onBlur(cart) {
  updateQuantity(cart)
}

async function deleteItem(cartId) {
  await fetch(`/user/cart-destroy/${cartId}`, {
    headers: { 'Accept': 'application/json' },
  })
  carts.value = carts.value.filter(c => c.id !== cartId)
  selectedIds.value = selectedIds.value.filter(id => id !== cartId)
}

async function deleteSelected() {
  if (!hasSelected.value) return
  const params = selectedIds.value.map(id => `check[]=${id}`).join('&')
  await fetch(`/user/destroy-carts?${params}`, {
    headers: { 'Accept': 'application/json' },
  })
  const removed = new Set(selectedIds.value)
  carts.value = carts.value.filter(c => !removed.has(c.id))
  selectedIds.value = []
}

function checkout() {
  window.location.href = '/user/order/create'
}

onMounted(fetchCart)
</script>

<template>
  <div class="title">
    <p>購物車</p>
  </div>
  <div class="content">
    <form name="form" @submit.prevent>
      <div class="top check-all">
        <input type="checkbox" class="checkAll" :checked="allSelected" @change="allSelected = $event.target.checked">
        <label>全選</label>
      </div>
      <table class="table table-cart">
        <thead>
          <tr>
            <th>商品</th>
            <th>單價</th>
            <th>數量</th>
            <th>金額</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cart in carts" :key="cart.id">
            <td>
              <input
                type="checkbox"
                :checked="isSelected(cart.id)"
                @change="toggleSelect(cart.id)"
              >
              <a class="product-title" :href="`/product/${cart.product.id}`">{{ cart.product.title }}</a>
            </td>
            <td><p class="text-center">\${{ cart.product.price }}</p></td>
            <td>
              <div class="text-center">
                <div class="stock" id="click">
                  <div class="minus">
                    <i class="ti-minus"></i>
                    <input type="button" class="qtyminus" @click="decrement(cart)">
                  </div>
                  <div id="product">
                    <input
                      v-model="cart.quantity"
                      type="text"
                      class="qty"
                      @blur="onBlur(cart)"
                      @input="cart.quantity = cart.quantity.toString().replace(/[^\d]/g, '')"
                    >
                  </div>
                  <div class="plus">
                    <i class="ti-plus"></i>
                    <input type="button" class="qtyplus" @click="increment(cart)">
                  </div>
                </div>
              </div>
            </td>
            <td><p class="text-center product-amount">\${{ cart.amount }}</p></td>
            <td>
              <p class="text-center">
                <a class="product-delete" href="#" @click.prevent="deleteItem(cart.id)">刪除</a>
              </p>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="foot">
        <div class="check-all">
          <input type="checkbox" class="checkAll2" :checked="allSelected" @change="allSelected = $event.target.checked">
          <label>全選</label>
        </div>&emsp;
        <div class="btn-delete">
          <button id="to-delete" type="button" @click="deleteSelected">刪除</button>
        </div>
      </div>

      <div v-if="hasItems && hasSelected">
        <button id="to-checkout" type="button" class="btn right btn-dark" @click="checkout">去結帳</button>
      </div>
      <div v-else>
        <button class="btn right btn-prohibit" type="button">去結帳</button>
      </div>
    </form>
  </div>
</template>
