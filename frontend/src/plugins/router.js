import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const router = createRouter({
    history: createWebHistory('/TemplateApp/'),
    routes: [
        { path: '/', name: 'dashboard', component: () => import('@/views/Dashboard.vue'), meta: { view: 'Dashboard', img: 'dashboard.png', auth: true } },
        { path: '/login/:reset_password_uid?/:reset_password_token?', name: 'login', component: () => import('@/views/Login.vue'), meta: { view: 'Login' } },
        { path: '/profile/:user_id?', name: 'profile', component: () => import('@/views/Profile.vue'), meta: { view: 'Perfil', img: 'profile.png', auth: true } },
        { path: '/products', name: 'products', component: () => import('@/views/Products.vue'), meta: { view: 'Produtos', img: 'products.png', auth: true, level: 1 } },
        { path: '/groups', name: 'groups', component: () => import('@/views/Groups.vue'), meta: { view: 'Grupos', img: 'team.png', auth: true, level: 1 } },
        { path: '/users', name: 'users', component: () => import('@/views/Users.vue'), meta: { view: 'Usuários', img: 'users.png', auth: true, level: 2 } },
        { path: '/about', name: 'about', component: () => import('@/views/About.vue'), meta: { view: 'Sobre', icon: 'mdi-information', auth: true } },
        { path: '/401', name: '401', component: () => import('@/views/401.vue'), meta: { auth: true }, props: true },
        { path: '/:pathMatch(.*)*', name: '404', component: () => import('@/views/404.vue') }
    ],
})

router.beforeEach(async (to, from, next) => {
    if (to.name == '401' && !to.params.page) return next({ path: '/404' })
    const auth = useAuthStore()
    if (to.meta?.auth) {
        if (auth.is_auth && auth.hasData()) {
            if (to.meta?.level) {
                auth.user.level >= to.meta.level ? next() : next({ name: '401', params: { page: to.meta.view } })
            } else {
                next()
            }
        } else {
            next({ name: 'login' })
        }
    } else {
        next()
    }
})

export default router