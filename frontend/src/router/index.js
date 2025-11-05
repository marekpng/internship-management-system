import StudentDashboardView from '@/views/StudentDashboardView.vue'
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterChoiceView from '../views/RegisterChoiceView.vue'
import RegisterStudentView from '../views/RegisterStudentView.vue'
import RegisterCompanyView from '../views/RegisterCompanyView.vue'
import LandingPage from '../views/LandingPage.vue'
import ChangePasswordView from '../views/ChangePasswordView.vue'
import ProfileView from '@/views/ProfileView.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/home', name: 'home', component: HomeView },
    { path: '/', name: 'landing', component: LandingPage },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'registerChoice', component: RegisterChoiceView },
    { path: '/register/student', name: 'registerStudent', component: RegisterStudentView },
    { path: '/register/company', name: 'registerCompany', component: RegisterCompanyView },
    { 
      path: '/student/dashboard', 
      name: 'studentDashboard', 
      component: StudentDashboardView, 
      meta: { requiresAuth: true } 
    },
    { 
      path: '/student/practice', 
      name: 'studentPracticeForm', 
      component: () => import('@/views/StudentPracticeForm.vue'),
      meta: { requiresAuth: true }
    },
    { 
      path: '/student/my-practice', 
      name: 'studentMyPractice', 
      component: () => import('@/views/StudentMyPracticeView.vue'),
      meta: { requiresAuth: true }
    },
    { path: '/change-password', name: 'changePassword', component: ChangePasswordView },
    { path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },


  ],
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('access_token')
  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' })
  } else {
    next()
  }
})

export default router