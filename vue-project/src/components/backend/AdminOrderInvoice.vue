<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const order = ref(null)
const loading = ref(true)

onMounted(async () => {
  const style = document.createElement('style')
  style.id = 'invoice-print-style'
  style.textContent = `
    @media print {
      .header-inner, #chat-widget { display: none !important; }
      body * { visibility: hidden; }
      .invoice-wrapper, .invoice-wrapper * { visibility: visible; }
      .invoice-wrapper { position: absolute; top: 0; left: 0; width: 100%; }
      .invoice-print-btn { display: none !important; }
    }
  `
  document.head.appendChild(style)

  const res = await fetch(`/api/admin/order/${route.params.id}`, {
    headers: { 'Accept': 'application/json' },
  })
  order.value = await res.json()
  loading.value = false
})

onUnmounted(() => {
  const el = document.getElementById('invoice-print-style')
  if (el) el.remove()
})

function print() {
  window.print()
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
  const day = days[d.getDay()]
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = d.getFullYear()
  return `${day}${dd}${mm}${yyyy}`
}
</script>

<template>
  <div v-if="loading" style="padding:30px">載入中...</div>
  <div v-else class="invoice-wrapper">
    <button class="invoice-print-btn" @click="print()">列印</button>

    <div class="container-fluid">
      <h1 class="mt-5">Invoice</h1>
      <table class="table mt-5">
        <tbody>
          <tr>
            <th scope="row">訂單編號</th>
            <td>{{ order.order_number }}</td>
          </tr>
          <tr>
            <th scope="row">買家</th>
            <td>{{ order.name }}</td>
          </tr>
          <tr>
            <th scope="row">訂單日期</th>
            <td>{{ formatDate(order.created_at) }}</td>
          </tr>
          <tr>
            <th scope="row">地址</th>
            <td>{{ order.address }}</td>
          </tr>
          <tr>
            <th scope="row">手機</th>
            <td>{{ order.phone }}</td>
          </tr>
        </tbody>
      </table>

      <table class="table table-striped mt-5">
        <thead>
          <tr>
            <th>商品</th>
            <th>數量</th>
            <th>單價</th>
            <th>金額</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="detail in order.order_details" :key="detail.id">
            <td>{{ detail.title }}</td>
            <td>{{ detail.quantity }}</td>
            <td>${{ detail.amount }}</td>
            <td>${{ detail.quantity * detail.amount }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">商品總金額</th>
            <td>${{ order.sub_total }}</td>
          </tr>
          <tr>
            <th colspan="3" class="text-end">運費</th>
            <td>${{ order.shipping_fee }}</td>
          </tr>
          <tr>
            <th colspan="3" class="text-end">訂單金額</th>
            <td>${{ order.total_amount }}</td>
          </tr>
        </tfoot>
      </table>

      <div class="row mt-5">
        <div class="col">
          <small>
            <strong>Notes:</strong><br>
            {{ order.note }}
          </small>
        </div>
      </div>
      <hr>
    </div>
  </div>
</template>

<style>
.invoice-wrapper {
  padding: 30px;
  background: #fff;
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  font-size: 1rem;
  color: #212529;
}
.invoice-wrapper .container-fluid {
  width: 100%;
  padding-right: 0.75rem;
  padding-left: 0.75rem;
  margin-right: auto;
  margin-left: auto;
}
.invoice-wrapper .mt-5 { margin-top: 3rem !important; }
.invoice-wrapper h1 { font-size: calc(1.375rem + 1.5vw); font-weight: 500; margin-top: 0; margin-bottom: 0.5rem; }
.invoice-wrapper table { border-collapse: collapse; width: 100%; margin-bottom: 1rem; vertical-align: top; border-color: #dee2e6; }
.invoice-wrapper table > * > * > * { padding: 0.5rem; border-bottom: 1px solid #dee2e6; }
.invoice-wrapper tr { border-bottom: 1px solid #dee2e6; }
.invoice-wrapper th { text-align: inherit; }
.invoice-wrapper .table tbody tr th { width: 50%; }
.invoice-wrapper .table-striped thead,
.invoice-wrapper .table-striped tbody { border-bottom: 1.1px solid #333 !important; }
.invoice-wrapper .table-striped tbody tr:nth-child(odd) { background-color: #f2f2f2; }
.invoice-wrapper .text-end { text-align: right !important; }
.invoice-wrapper .row { display: flex; flex-wrap: wrap; }
.invoice-wrapper .col { flex: 1 0 0%; }
.invoice-print-btn {
  margin: 16px 30px;
  padding: 8px 20px;
  cursor: pointer;
  font-size: 1rem;
}
</style>
