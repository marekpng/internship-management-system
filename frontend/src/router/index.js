import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'RegisterChoice', component: () => import('../views/RegisterChoiceView.vue') },
    { path: '/register/student', name: 'RegisterStudent', component: () => import('../views/RegisterStudentView.vue') },
    { path: '/register/company', name: 'RegisterCompany', component: () => import('../views/RegisterCompanyView.vue') },
  ],
})

export default router
