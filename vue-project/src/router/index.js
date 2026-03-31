import { createRouter, createWebHistory } from 'vue-router'
import ProductIndex from '@/components/ProductIndex.vue'
import Register from '@/components/Register.vue'
import Login from '@/components/Login.vue'
import Account from '@/components/Account.vue'
import ProductDetail from '@/components/ProductDetail.vue'

const routes = [
  { path: '/', component: ProductIndex },
  { path: '/product/:id', component: ProductDetail },
  { path: '/register', component: Register },
  { path: '/login', component: Login },
  { path: '/account', component: Account },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
