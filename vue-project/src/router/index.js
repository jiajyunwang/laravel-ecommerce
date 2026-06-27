import { createRouter, createWebHistory } from 'vue-router'
import ProductIndex from '@/components/frontend/ProductIndex.vue'
import ProductDetail from '@/components/frontend/ProductDetail.vue'
import Register from '@/components/frontend/Register.vue'
import Login from '@/components/frontend/Login.vue'
import Account from '@/components/frontend/Account.vue'
import Cart from '@/components/frontend/Cart.vue'
import Checkout from '@/components/frontend/Checkout.vue'
import Orders from '@/components/frontend/Orders.vue'
import OrderDetail from '@/components/frontend/OrderDetail.vue'
import AdminLogin from '@/components/backend/AdminLogin.vue'
import AdminLayout from '@/components/backend/AdminLayout.vue'
import AdminProductIndex from '@/components/backend/AdminProductIndex.vue'
import AdminProductCreate from '@/components/backend/AdminProductCreate.vue'
import AdminProductEdit from '@/components/backend/AdminProductEdit.vue'
import AdminOrderIndex from '@/components/backend/AdminOrderIndex.vue'
import AdminOrderInvoice from '@/components/backend/AdminOrderInvoice.vue'
import axios from 'axios';

const routes = [
    { 
        path: '/', 
        component: ProductIndex 
    },
    { 
        path: '/search', 
        component: ProductIndex 
    },
    { 
        path: '/product/:id', 
        component: ProductDetail 
    },
    { 
        path: '/register', 
        component: Register 
    },
    { 
        path: '/login', 
        component: Login 
    },
    { 
        path: '/account', 
        component: Account,
        meta: { requiresAuth: true }
    },
    { 
        path: '/cart', 
        component: Cart,
        meta: { requiresAuth: true }
    },
    { 
        path: '/checkout', 
        component: Checkout, 
        meta: { requiresAuth: true }
    },
    { 
        path: '/orders', 
        component: Orders,
        meta: { requiresAuth: true }
    },
    { 
        path: '/order/:id', 
        component: OrderDetail,
        meta: { requiresAuth: true } 
    },
    { 
        path: '/admin/login', 
        component: AdminLogin 
    },
    {
        path: '/admin',
        component: AdminLayout,
        children: [
        { path: 'product', component: AdminProductIndex },
        { path: 'product/create', component: AdminProductCreate },
        { path: 'product/:id/edit', component: AdminProductEdit },
        { path: 'orders', component: AdminOrderIndex },
        ],
    },
    { path: '/admin/orders/:id/invoice', component: AdminOrderInvoice },
    { path: '/request-action/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// router.beforeEach(async (to, from, next) => {
//   if (to.meta.requiresAuth) {
//     await checkAuth()

//     if (!isAuthenticated) {
//       return next('/login')
//     }
//   }

//   next()
// })


router.beforeEach(async (to) => {

    if (!to.meta.requiresAuth) {
        return true
    }

    try {

        await axios.get('/users')

        return true

    } catch {

        return '/login'

    }

})

export default router
