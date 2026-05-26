import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue' 
import ForgotPassword from '../views/ForgotPassword.vue' 
// 1. Importamos el nuevo Dashboard
import DashboardPacienteView from '../views/DashboardPacienteView.vue' 

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'registro',
      component: HomeView
    },
    {
      path: '/login', 
      name: 'login',
      component: LoginView 
    },
    {
      path: '/recuperar', 
      name: 'recuperar',
      component: ForgotPassword 
    },
    // 2. Agregamos la ruta del Dashboard
    {
      path: '/dashboard', 
      name: 'dashboard-paciente',
      component: DashboardPacienteView 
    }
  ]
})

export default router