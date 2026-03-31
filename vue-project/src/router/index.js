import { createRouter, createWebHistory } from 'vue-router'
import ProductIndex from '@/components/ProductIndex.vue'
import ProductDetail from '@/components/ProductDetail.vue'
import Register from '@/components/Register.vue'
import Login from '@/components/Login.vue'
import Account from '@/components/Account.vue'
import Cart from '@/components/Cart.vue'

const routes = [
  { path: '/', component: ProductIndex },
  { path: '/product/:id', component: ProductDetail },
  { path: '/register', component: Register },
  { path: '/login', component: Login },
  { path: '/account', component: Account },
  { path: '/cart', component: Cart },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
