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

// Vistas del médico
import InicioMedicoView    from '../views/Medico/InicioMedicoView.vue'
import PerfilMedicoView    from '../views/Medico/PerfilMedicoView.vue'
import AgendaMedicoView    from '../views/Medico/AgendaMedicoView.vue'
import HistorialMedicoView from '../views/Medico/HistorialMedicoView.vue'

// Vistas de la recepcionista
import InicioRecepcionistaView from '../views/Recepcionista/inicioRecepcionista.vue'
import CitasRecepcionistaView  from '../views/Recepcionista/citas.vue'
import PacientesView           from '../views/Recepcionista/pacientes.vue'
import CheckinView             from '../views/Recepcionista/tarjetas.vue'
import ReportesView            from '../views/Recepcionista/reportes.vue'

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

    // ─── PACIENTE ───────────────────────────────────────────────────────────────
    {
      path: '/dashboard',
      meta: { requiereAuth: true, rol: 'paciente' },
      component: DashboardPacienteView,
      children: [
        { path: 'perfil',         name: 'dashboard-perfil',         component: PerfilView },
        { path: 'citas',          name: 'dashboard-citas',          component: CitasView },
        { path: 'historial',      name: 'dashboard-historial',      component: HistorialView },
        { path: 'notificaciones', name: 'dashboard-notificaciones', component: NotificacionesView }
      ]
    },

    // ─── MÉDICO ─────────────────────────────────────────────────────────────────
    {
      path: '/medico/inicio',
      name: 'medico-inicio',
      meta: { requiereAuth: true, rol: 'medico' },
      component: InicioMedicoView
    },
    {
      path: '/medico/perfil',
      name: 'medico-perfil',
      meta: { requiereAuth: true, rol: 'medico' },
      component: PerfilMedicoView
    },
    {
      path: '/medico/agenda',
      name: 'medico-agenda',
      meta: { requiereAuth: true, rol: 'medico' },
      component: AgendaMedicoView
    },
    {
      path: '/medico/historiales',
      name: 'medico-historiales',
      meta: { requiereAuth: true, rol: 'medico' },
      component: HistorialMedicoView
    },

    // ─── RECEPCIONISTA ──────────────────────────────────────────────────────────
    {
      path: '/recepcionista/inicio',
      name: 'recepcionista-inicio',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: InicioRecepcionistaView
    },
    {
      path: '/recepcionista/citas',
      name: 'recepcionista-citas',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: CitasRecepcionistaView
    },
    {
      path: '/recepcionista/pacientes',
      name: 'recepcionista-pacientes',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: PacientesView
    },
    {
      path: '/recepcionista/checkin',
      name: 'recepcionista-checkin',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: CheckinView
    },
    {
      path: '/recepcionista/reportes',
      name: 'recepcionista-reportes',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: ReportesView
    }
  ]
})

// ─────────────────────────────────────────
// GUARDIA DE NAVEGACIÓN GLOBAL
// ─────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const token      = localStorage.getItem('token')
  const rolUsuario = localStorage.getItem('usuarioRol')

  // Ruta pública: dejar pasar siempre
  if (!to.meta.requiereAuth) {
    // Si ya hay sesión activa y van al login, redirigir al área correcta
    if (to.name === 'login' && token) {
      if (rolUsuario === 'medico')         return next('/medico/inicio')
      if (rolUsuario === 'recepcionista')  return next('/recepcionista/inicio')
      return next('/dashboard')
    }
    return next()
  }

  // Ruta protegida: sin token → al login
  if (!token) return next({ name: 'login' })

  // Ruta protegida: rol incorrecto → redirigir al área correcta
  if (to.meta.rol && to.meta.rol !== rolUsuario) {
    if (rolUsuario === 'medico')         return next('/medico/inicio')
    if (rolUsuario === 'recepcionista')  return next('/recepcionista/inicio')
    return next('/dashboard')
  }

  next()
})

export default router