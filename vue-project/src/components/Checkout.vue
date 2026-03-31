<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { loadStripe } from '@stripe/stripe-js'

const route = useRoute()
const router = useRouter()

const carts = ref([])
const subTotal = ref(0)
const homeDeliveryFee = ref(0)
const totalAmount = computed(() => subTotal.value + homeDeliveryFee.value)

const name = ref('')
const cellphone = ref('')
const address = ref('')
const note = ref('')
const errors = ref({})

const paymentMethod = ref('COD')
const showPanel = ref(false)

const cardholderName = ref('')
const cardholderCellphone = ref('')

const loading = ref(false)
const submitting = ref(false)

let stripe = null
let cardNumberElement = null
let cardExpiryElement = null
let cardCVCElement = null

const checkIds = computed(() => {
  const check = route.query.check
  if (!check) return []
  return Array.isArray(check) ? check : [check]
})

onMounted(async () => {
  if (checkIds.value.length === 0) {
    router.push('/cart')
    return
  }
  await fetchCheckout()
  await initStripe()
})

onUnmounted(() => {
  if (cardNumberElement) cardNumberElement.destroy()
  if (cardExpiryElement) cardExpiryElement.destroy()
  if (cardCVCElement) cardCVCElement.destroy()
})

async function fetchCheckout() {
  loading.value = true
  const params = checkIds.value.map(id => `check[]=${id}`).join('&')
  try {
    const res = await fetch(`/api/user/checkout?${params}`, {
      headers: { 'Accept': 'application/json' },
    })
    if (res.status === 401) {
      router.push('/login')
      return
    }
    const data = await res.json()
    carts.value = data.carts
    subTotal.value = data.subTotal
    homeDeliveryFee.value = data.homeDeliveryFee
    name.value = data.user.name || ''
    cellphone.value = data.user.cellphone || ''
    address.value = data.user.address || ''
  } finally {
    loading.value = false
  }
}

async function initStripe() {
  stripe = await loadStripe('pk_test_51RFGzDGg0Fe7TJofHBe0SBc1jRBCDmvChNw03uLffEqBJBl6BytI8aYRaDBkw40calHVRiEev3OQG68jMwcYve8g00BgEyVHrM')
  const elements = stripe.elements({
    fonts: [{ cssSrc: 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap' }],
  })
  const style = { base: { color: '#555', fontFamily: 'Montserrat, sans-serif' } }

  cardNumberElement = elements.create('cardNumber', { style })
  cardNumberElement.mount('#card-number-element')

  cardExpiryElement = elements.create('cardExpiry', { style })
  cardExpiryElement.mount('#card-expiry-element')

  cardCVCElement = elements.create('cardCvc', { style })
  cardCVCElement.mount('#card-cvc-element')
}

function selectCOD() {
  paymentMethod.value = 'COD'
  showPanel.value = false
}

function selectCreditCard() {
  paymentMethod.value = 'creditCard'
  showPanel.value = true
}

async function handleCheckout() {
  if (submitting.value) return
  errors.value = {}
  submitting.value = true

  let stripeToken = null
  try {
    if (paymentMethod.value === 'creditCard') {
      const result = await stripe.createToken(cardNumberElement, {
        cardholder_name: cardholderName.value,
        cardholder_cellphone: cardholderCellphone.value,
      })
      if (result.error) {
        alert(result.error.message)
        return
      }
      stripeToken = result.token.id
    }

    const body = {
      product_id: carts.value.map(c => c.product_id),
      quantity: carts.value.map(c => c.quantity),
      note: note.value,
      name: name.value,
      cellphone: cellphone.value,
      address: address.value,
      paymentMethod: paymentMethod.value,
      stripeToken,
      subTotal: subTotal.value,
      shippingFee: homeDeliveryFee.value,
      totalAmount: totalAmount.value,
      fromCart: 1,
      check: checkIds.value,
    }

    const res = await fetch('/api/order/store', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    })

    const data = await res.json()
    if (!res.ok) {
      if (data.errors) {
        errors.value = data.errors
      } else {
        alert(data.message || '結帳失敗')
      }
      return
    }

    window.location.href = '/user/order'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div v-if="loading" class="title"><p>載入中...</p></div>
  <template v-else>
    <div class="title">
      <p>結帳</p>
    </div>
    <div class="content">
      <form @submit.prevent="handleCheckout">
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
            <tr v-for="cart in carts" :key="cart.id">
              <td><p class="product-title">{{ cart.title }}</p></td>
              <td><p class="text-center">${{ cart.price }}</p></td>
              <td><p class="text-center">{{ cart.quantity }}</p></td>
              <td><p class="text-center product-amount">${{ cart.amount }}</p></td>
            </tr>
          </tbody>
        </table>
        <div class="grid-container">
          <div class="note">
            <label>備註:</label>
            <input type="text" v-model="note">
          </div>
          <div class="amount">
            <label class="m-r-m">合計:</label>
            <p class="text-danger">${{ subTotal }}</p>
          </div>
          <div class="receiver-info">
            <label>收件人<span>*</span></label>
            <input type="text" v-model="name" required>
            <span v-if="errors.name" class="error">{{ errors.name[0] }}</span>

            <label>手機<span>*</span></label>
            <input type="text" v-model="cellphone" required>
            <span v-if="errors.cellphone" class="error">{{ errors.cellphone[0] }}</span>

            <label>收件地址<span>*</span></label>
            <input type="text" v-model="address" required>
            <span v-if="errors.address" class="error">{{ errors.address[0] }}</span>
          </div>
          <div class="payments">
            <label>付款方式</label>
            <div class="nav-tabs">
              <button
                id="COD"
                class="btn sort-button"
                :class="{ active: paymentMethod === 'COD' }"
                type="button"
                @click="selectCOD"
              >貨到付款</button>
              <button
                id="credit-card"
                class="btn sort-button"
                :class="{ active: paymentMethod === 'creditCard' }"
                type="button"
                @click="selectCreditCard"
              >信用卡</button>
            </div>
            <div class="panel" v-show="showPanel">
              <label>付款詳情</label>
              <div class="card_box">
                <label for="cardholder-name">持卡人姓名</label>
                <input id="cardholder-name" class="card_input" type="text" v-model="cardholderName">

                <label for="cardholder-cellphone">持卡人手機</label>
                <input id="cardholder-cellphone" class="card_input" type="tel" v-model="cardholderCellphone">

                <label for="card-number-element">卡號</label>
                <div id="card-number-element" class="card_input"></div>

                <div class="card_row">
                  <div>
                    <label for="card-expiry-element">到期日</label>
                    <div id="card-expiry-element" class="card_input"></div>
                  </div>
                  <div>
                    <label for="card-cvc-element">安全碼</label>
                    <div id="card-cvc-element" class="card_input"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="total-amount">
            <div>
              <label class="m-r-m">商品:</label>
              <p>${{ subTotal }}</p>
            </div>
            <div>
              <label class="-m-r-m">運費:</label>
              <p>${{ homeDeliveryFee }}</p>
            </div>
            <div>
              <label class="m-r-m">應付:</label>
              <p class="text-danger">${{ totalAmount }}</p>
            </div>
          </div>
          <div class="button">
            <button
              id="checkout"
              class="btn right btn-dark"
              type="submit"
              :disabled="submitting"
            >結帳</button>
          </div>
        </div>
      </form>
    </div>
  </template>
</template>
