
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
 
// Vistas del paciente
import DashboardPacienteView from '../views/paciente/DashboardPacienteView.vue'
import PerfilView from '../views/paciente/PerfilView.vue'
import CitasView from '../views/paciente/CitasView.vue'
import HistorialView from '../views/paciente/HistorialView.vue'
import NotificacionesView from '../views/paciente/NotificacionesView.vue'
 
// Vista del médico
import InicioMedicoView from '../views/Medico/InicioMedicoView.vue'
 
// Rutas públicas (no necesitan token)
const rutasPublicas = ['login', 'registro', 'recuperar']
 
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
 
    // --- FLUJO DEL PACIENTE (requiere token + rol paciente) ---
    {
      path: '/dashboard',
      meta: { requiereAuth: true, rol: 'paciente' },
      component: DashboardPacienteView,
      children: [
        { path: 'perfil',          name: 'dashboard-perfil',          component: PerfilView },
        { path: 'citas',           name: 'dashboard-citas',           component: CitasView },
        { path: 'historial',       name: 'dashboard-historial',       component: HistorialView },
        { path: 'notificaciones',  name: 'dashboard-notificaciones',  component: NotificacionesView }
      ]
    },
 
    // --- FLUJO DEL MÉDICO (requiere token + rol medico) ---
    {
      path: '/medico/inicio',
      name: 'medico-inicio',
      meta: { requiereAuth: true, rol: 'medico' },
      component: InicioMedicoView
    }
  ]
})
 
// ─────────────────────────────────────────
// GUARDIA DE NAVEGACIÓN GLOBAL
// ─────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const rolUsuario = localStorage.getItem('usuarioRol')
 
  // Si la ruta es pública, dejar pasar siempre
  if (!to.meta.requiereAuth) {
    // Si ya hay sesión y van al login, redirigir al área correcta
    if (to.name === 'login' && token) {
      return next(rolUsuario === 'medico' ? '/medico/inicio' : '/dashboard')
    }
    return next()
  }
 
  // Ruta protegida: sin token → al login
  if (!token) {
    return next({ name: 'login' })
  }
 
  // Ruta protegida: rol incorrecto → redirigir al área correcta
  if (to.meta.rol && to.meta.rol !== rolUsuario) {
    return next(rolUsuario === 'medico' ? '/medico/inicio' : '/dashboard')
  }
 
  next()
})
 
export default router