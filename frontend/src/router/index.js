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

// VISTA DEL MÉDICO (Corregida la carpeta 'medico' a minúscula)
import InicioMedicoView from '../views/Medico/InicioMedicoView.vue'

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
    
    // --- FLUJO DEL PACIENTE ---
    {
      path: '/dashboard', 
      component: DashboardPacienteView,
      children: [
        {
          path: 'perfil', 
          name: 'dashboard-perfil',
          component: PerfilView
        },
        {
          path: 'citas', 
          name: 'dashboard-citas',
          component: CitasView
        },
        {
          path: 'historial', 
          name: 'dashboard-historial',
          component: HistorialView
        },
        {
          path: 'notificaciones', 
          name: 'dashboard-notificaciones',
          component: NotificacionesView
        }
      ]
    },

    // --- FLUJO DEL MÉDICO ---
    {
      path: '/medico/inicio',
      name: 'medico-inicio',
      component: InicioMedicoView
    }
  ]
})

export default router