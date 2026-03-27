import { createRouter, createWebHistory } from 'vue-router'
import ProductIndex from '@/components/ProductIndex.vue'
import Register from '@/components/Register.vue'

const routes = [
  { path: '/', component: ProductIndex },
  { path: '/register', component: Register },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
