import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'
 
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
import NotificacionesMedicoView from '../views/Medico/NotificacionesMedicoView.vue'
 
// Vistas de la recepcionista
import InicioRecepcionistaView from '../views/Recepcionista/inicioRecepcionista.vue'
import CitasRecepcionistaView  from '../views/Recepcionista/citas.vue'
import PacientesView           from '../views/Recepcionista/pacientes.vue'
import CheckinView             from '../views/Recepcionista/tarjetas.vue'
import ReportesView            from '../views/Recepcionista/reportes.vue'
import NotificacionesRecepcionistaView from '../views/Recepcionista/notificaciones.vue'
import PerfilRecepcionistaView from '../views/Recepcionista/perfil.vue'
 
// Vistas del administrador
import AdminLayout              from '../views/Admin/AdminLayout.vue'
import InicioAdminView          from '../views/Admin/InicioAdminView.vue'
import CitasAdminView           from '../views/Admin/CitasAdminView.vue'
import PacientesAdminView       from '../views/Admin/PacientesAdminView.vue'
import ReportesAdminView        from '../views/Admin/ReportesAdminView.vue'
import RecepcionistasAdminView  from '../views/Admin/RecepcionistasAdminView.vue'
import NotificacionesAdminView  from '../views/Admin/NotificacionesAdminView.vue'
import PerfilAdminView          from '../views/Admin/PerfilAdminView.vue'
 
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
    {
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPassword
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
    {
      path: '/medico/notificaciones',
      name: 'medico-notificaciones',
      meta: { requiereAuth: true, rol: 'medico' },
      component: NotificacionesMedicoView
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
    },
    {
      path: '/recepcionista/notificaciones',
      name: 'recepcionista-notificaciones',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: NotificacionesRecepcionistaView
    },
    {
      path: '/recepcionista/perfil',
      name: 'recepcionista-perfil',
      meta: { requiereAuth: true, rol: 'recepcionista' },
      component: PerfilRecepcionistaView
    },
 
    // ─── ADMINISTRADOR ──────────────────────────────────────────────────────────
    {
      path: '/admin',
      meta: { requiereAuth: true, rol: 'administrador' },
      component: AdminLayout,
      children: [
        {
          path: '',
          name: 'admin-inicio',
          component: InicioAdminView
        },
        {
          path: 'citas',
          name: 'admin-citas',
          component: CitasAdminView
        },
        {
          path: 'pacientes',
          name: 'admin-pacientes',
          component: PacientesAdminView
        },
        {
          path: 'recepcionistas',
          name: 'admin-recepcionistas',
          component: RecepcionistasAdminView
        },
        {
          path: 'reportes',
          name: 'admin-reportes',
          component: ReportesAdminView
        },
        {
          path: 'notificaciones',
          name: 'admin-notificaciones',
          component: NotificacionesAdminView
        },
        {
          path: 'perfil',
          name: 'admin-perfil',
          component: PerfilAdminView
        }
      ]
    }
  ]
})
 
// ─────────────────────────────────────────
// GUARDIA DE NAVEGACIÓN GLOBAL
// ─────────────────────────────────────────
const BASE_URL = 'http://localhost:8000/api/v1'
let tokenVerificado = false

const cerrarSesionForzado = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('usuarioRol')
  localStorage.removeItem('usuarioNombre')
  localStorage.removeItem('usuarioCorreo')
  localStorage.removeItem('pacId')
  localStorage.removeItem('medicoId')
  localStorage.removeItem('astId')
}

router.beforeEach(async (to, from, next) => {
  const token      = localStorage.getItem('token')
  const rolUsuario = localStorage.getItem('usuarioRol')

  // Si hay un token que aún no hemos validado en esta carga de la app,
  // confirmamos con el backend que siga siendo válido (evita quedar
  // "logueado" con un token viejo tras reiniciar el servidor/BD).
  if (token && !tokenVerificado) {
    tokenVerificado = true
    try {
      const res = await fetch(`${BASE_URL}/user`, {
        headers: { 'Authorization': `Bearer ${token}` }
      })
      if (!res.ok) {
        cerrarSesionForzado()
        return next({ name: 'login' })
      }
    } catch {
      // Sin conexión al backend: dejamos pasar, se validará en la siguiente petición real
    }
  }

  // Ruta pública: dejar pasar siempre
  if (!to.meta.requiereAuth) {
    // Si ya hay sesión activa y van al login, redirigir al área correcta
    if (to.name === 'login' && localStorage.getItem('token')) {
      if (rolUsuario === 'administrador')  return next('/admin')
      if (rolUsuario === 'medico')         return next('/medico/inicio')
      if (rolUsuario === 'recepcionista')  return next('/recepcionista/inicio')
      return next('/dashboard')
    }
    return next()
  }

  // Ruta protegida: sin token → al login
  if (!localStorage.getItem('token')) return next({ name: 'login' })
 
  // Ruta protegida: rol incorrecto → redirigir al área correcta
  if (to.meta.rol && to.meta.rol !== rolUsuario) {
    if (rolUsuario === 'administrador')  return next('/admin')
    if (rolUsuario === 'medico')         return next('/medico/inicio')
    if (rolUsuario === 'recepcionista')  return next('/recepcionista/inicio')
    return next('/dashboard')
  }
 
  next()
})
 
export default router
 