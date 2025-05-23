import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import ProductList from '../components/ProductList.vue'
import Cart from '../components/Cart.vue'
import OrderHistory from '../components/OrderHistory.vue'

const routes = [
    {
        path: '/',
        redirect: '/products'
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { guest: true }
    },
    {
        path: '/products',
        name: 'Products',
        component: ProductList,
        meta: { requiresAuth: true }
    },
    {
        path: '/cart',
        name: 'Cart',
        component: Cart,
        meta: { requiresAuth: true }
    },
    {
        path: '/orders',
        name: 'Orders',
        component: OrderHistory,
        meta: { requiresAuth: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('token')

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login')
    } else if (to.meta.guest && isAuthenticated) {
        next('/products')
    } else {
        next()
    }
})

export default router