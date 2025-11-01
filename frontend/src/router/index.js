import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterChoiceView from '../views/RegisterChoiceView.vue'
import RegisterStudentView from '../views/RegisterStudentView.vue'
import RegisterCompanyView from '../views/RegisterCompanyView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'registerChoice', component: RegisterChoiceView },
    { path: '/register/student', name: 'registerStudent', component: RegisterStudentView },
    { path: '/register/company', name: 'registerCompany', component: RegisterCompanyView },
  ],
})

export default router