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
import GarantDashboardView from '@/views/Garant/GarantDashboardView.vue'
import GarantPracticesView from '@/views/Garant/GarantPracticesView.vue'
import GarantPracticeDetailView from '@/views/Garant/GarantPracticeDetailView.vue'
import GarantSettingsView from '@/views/Garant/GarantSettings.vue'

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
    {
      path: '/student/settings',
      name: 'studentSettings',
      component: () => import('@/views/StudentSettings.vue'),
      meta: { requiresAuth: true }
    },
    { path: '/change-password', name: 'changePassword', component: ChangePasswordView },
    { path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },
    {
      path: '/company/dashboard',
      name: 'companyDashboard',
      component: () => import('../views/Company/CompanyDashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/company/practices',
      name: 'companyPractices',
      component: () => import('../views/Company/CompanyPracticesView.vue'),
      meta: { requiresAuth: true }
    }, 
    {
      path: '/company/practices/:id',
      name: 'companyPracticeDetail',
      component: () => import('../views/Company/CompanyPracticeDetailView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/company/settings',
      name: 'companySettings',
      component: () => import('../views/Company/CompanySettings.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/garant/dashboard',
      name: 'garantDashboard',
      component: GarantDashboardView,
      meta: { requiresAuth: true }
    },
    {
      path: '/garant/practices',
      name: 'garantPractices',
      component: GarantPracticesView,
      meta: { requiresAuth: true }
    },
    {
      path: '/garant/practices/:id',
      name: 'garantPracticeDetail',
      component: GarantPracticeDetailView,
      meta: { requiresAuth: true }
    },
    {
      path: '/garant/settings',
      name: 'garantSettings',
      component: GarantSettingsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/login',
      name: 'adminLogin',
      component: () => import('@/views/Admin/AdminLoginView.vue')
    },
    {
      path: '/admin/dashboard',
      name: 'adminDashboard',
      component: () => import('@/views/Admin/AdminDashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/role-settings',
      name: 'adminRoleSettings',
      component: () => import('@/views/Admin/AdminRoleSettingsView.vue'),
      meta: { requiresAuth: true }
    },
  ],
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('access_token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const rawRole = user?.roles?.[0]
  const role = typeof rawRole === 'string' ? rawRole : rawRole?.name

  // ak stránka vyžaduje login a user nemá token → redirect
  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login' })
  }

  // ochrana routov pre študenta
  if (role === 'student') {
    if (to.path.startsWith('/company') || to.path.startsWith('/garant')) {
      return next('/student/dashboard')
    }
  }

  // ochrana routov pre firmu
  if (role === 'company') {
    if (to.path.startsWith('/student') || to.path.startsWith('/garant') || to.path.startsWith('/admin')) {
      return next('/company/dashboard')
    }
  }

    if (role === 'garant') {
    if (to.path.startsWith('/student') || to.path.startsWith('/company')) {
      return next('/garant/dashboard')
    }
  }

  return next()
})

export default router