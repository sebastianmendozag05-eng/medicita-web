<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const fechaActual = ref('')
const nombreRecepcionista = ref('Recepcionista')
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('token')}`
})

const stats = ref({ citasHoy: 0, pacientesEnEspera: 0, citasPendientes: 0, citasCompletadas: 0 })
const citasHoy = ref([])

const colorEstado = (estado) => ({
  'completada': 'badge-completada',
  'en-espera': 'badge-espera',
  'pendiente': 'badge-pendiente',
  'cancelada': 'badge-cancelada'
})[estado] || 'badge-pendiente'

const labelEstado = (estado) => ({
  'completada': '✔ Completada',
  'en-espera': '⏳ En espera',
  'pendiente': '· Pendiente',
  'cancelada': '✗ Cancelada'
})[estado] || estado

onMounted(async () => {
  const nombre = localStorage.getItem('usuarioNombre')
  if (nombre) nombreRecepcionista.value = nombre
  fechaActual.value = new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })

  try {
    const hoy = new Date().toISOString().split('T')[0]

    const [resCitas, resResumen] = await Promise.all([
      fetch(`${BASE_URL}/agenda/todos?fecha=${hoy}`, { headers: getHeaders() }),
      fetch(`${BASE_URL}/reportes/resumen`, { headers: getHeaders() })
    ])

    if (resResumen.ok) {
      const resumen = await resResumen.json()
      stats.value.citasHoy = resumen.total_citas
      stats.value.citasPendientes = resumen.pendientes
      stats.value.citasCompletadas = resumen.completadas
      stats.value.pacientesEnEspera = resumen.pendientes
    }

    if (resCitas.ok) {
      const medicos = await resCitas.json()
      const todasLasCitas = medicos.flatMap(m => 
        (m.citas ?? []).map(c => ({
          id: c.citId,
          hora: c.citHora?.substring(0, 5),
          paciente: `${c.paciente?.pacNombre ?? ''} ${c.paciente?.pacApePat ?? ''}`,
          medico: `Dr. ${m.medNombre} ${m.medApePat}`,
          estado: c.citEstatus === 'agendada' ? 'pendiente' : c.citEstatus
        }))
      )
      citasHoy.value = todasLasCitas.slice(0, 6)
    }
  } catch (e) {
    console.error('Error cargando datos:', e)
  }
})

const cerrarSesion = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      <nav class="menu-navegacion">
        <router-link to="/recepcionista/inicio" class="enlace-menu activo">
          <span class="icono">🏠</span> Inicio
        </router-link>
        <router-link to="/recepcionista/citas" class="enlace-menu">
          <span class="icono">📋</span> Citas
        </router-link>
        <router-link to="/recepcionista/pacientes" class="enlace-menu">
          <span class="icono">👥</span> Pacientes
        </router-link>
        <router-link to="/recepcionista/checkin" class="enlace-menu">
          <span class="icono">✅</span> Check-in
        </router-link>
        <router-link to="/recepcionista/reportes" class="enlace-menu">
          <span class="icono">📊</span> Reportes
        </router-link>
        <router-link to="/recepcionista/notificaciones" class="enlace-menu">
          <span class="icono">🔔</span> Notificaciones
        </router-link>
        <router-link to="/recepcionista/perfil" class="enlace-menu">
          <span class="icono">👤</span> Mi Perfil
        </router-link>
      </nav>
      <div class="sidebar-pie">
        <button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button>
      </div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera">
        <h1 class="saludo-principal">Bienvenida, {{ nombreRecepcionista }} 👋</h1>
        <span class="fecha-cabecera">{{ fechaActual }}</span>
      </div>

      <!-- Stats -->
      <div class="grilla-stats">
        <div class="tarjeta-stat color-azul">
          <div class="stat-icono">📅</div>
          <div class="stat-info">
            <span class="stat-numero">{{ stats.citasHoy }}</span>
            <span class="stat-label">Citas hoy</span>
          </div>
        </div>
        <div class="tarjeta-stat color-amarillo">
          <div class="stat-icono">⏳</div>
          <div class="stat-info">
            <span class="stat-numero">{{ stats.pacientesEnEspera }}</span>
            <span class="stat-label">En espera</span>
          </div>
        </div>
        <div class="tarjeta-stat color-rojo">
          <div class="stat-icono">🕐</div>
          <div class="stat-info">
            <span class="stat-numero">{{ stats.citasPendientes }}</span>
            <span class="stat-label">Citas pendientes</span>
          </div>
        </div>
        <div class="tarjeta-stat color-verde">
          <div class="stat-icono">✅</div>
          <div class="stat-info">
            <span class="stat-numero">{{ stats.citasCompletadas }}</span>
            <span class="stat-label">Completadas</span>
          </div>
        </div>
      </div>

      <!-- Citas del día -->
      <div class="tarjeta-citas">
        <div class="encabezado-seccion">
          <h2 class="subtitulo">Citas para Hoy</h2>
          <router-link to="/recepcionista/citas" class="enlace-ver-todas">Ver todas →</router-link>
        </div>

        <div class="tabla-citas">
          <div class="fila-encabezado">
            <span>Hora</span>
            <span>Paciente</span>
            <span>Médico</span>
            <span>Estado</span>
          </div>
          <div v-for="cita in citasHoy" :key="cita.id" class="fila-cita">
            <span class="celda-hora">{{ cita.hora }}</span>
            <span class="celda-paciente">{{ cita.paciente }}</span>
            <span class="celda-medico">{{ cita.medico }}</span>
            <span :class="['badge-estado', colorEstado(cita.estado)]">{{ labelEstado(cita.estado) }}</span>
          </div>
        </div>

        <router-link to="/recepcionista/citas" class="btn-ver-registro">
          Ver Registro Completo
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background-color: #f8fafc; }
.sidebar-izquierdo {
  width: 240px; background: #fff; border-right: 1px solid #e2e8f0;
  display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; }
.menu-navegacion { display: flex; flex-direction: column; gap: 0.4rem; flex-grow: 1; }
.enlace-menu {
  display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem;
  color: #64748b; text-decoration: none; font-weight: 600; border-radius: 10px;
  transition: all 0.2s; font-size: 0.92rem;
}
.enlace-menu:hover { background: #f1f5f9; color: #1e293b; }
.enlace-menu.activo { background: #e6f4f1; color: #0d8a72; }
.sidebar-pie { margin-top: auto; }
.btn-cerrar-sesion {
  width: 100%; background: none; border: none; color: #b45309;
  padding: 0.85rem 1rem; font-weight: 600; font-size: 0.92rem;
  cursor: pointer; text-align: left; border-radius: 10px; transition: background-color 0.2s;
}
.btn-cerrar-sesion:hover { background: #fef3c7; }
.contenedor-dashboard {
  flex-grow: 1; padding: 24px; box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.cabecera {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;
}
.saludo-principal { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0; }
.fecha-cabecera {
  font-size: 14px; color: #64748b; background: white;
  padding: 6px 12px; border-radius: 20px; border: 1px solid #e2e8f0;
}
.grilla-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.tarjeta-stat {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 18px; display: flex; align-items: center; gap: 14px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.stat-icono { font-size: 28px; }
.stat-info { display: flex; flex-direction: column; }
.stat-numero { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 4px; font-weight: 500; }
.color-azul { border-left: 4px solid #3b82f6; }
.color-amarillo { border-left: 4px solid #f59e0b; }
.color-rojo { border-left: 4px solid #ef4444; }
.color-verde { border-left: 4px solid #10b981; }

.tarjeta-citas {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.encabezado-seccion { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.subtitulo { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; }
.enlace-ver-todas { font-size: 14px; color: #0d8a72; font-weight: 600; text-decoration: none; }
.enlace-ver-todas:hover { text-decoration: underline; }

.tabla-citas { display: flex; flex-direction: column; gap: 0; }
.fila-encabezado {
  display: grid; grid-template-columns: 80px 1fr 1fr 130px;
  padding: 8px 12px; font-size: 12px; font-weight: 700;
  color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;
  border-bottom: 1px solid #f1f5f9;
}
.fila-cita {
  display: grid; grid-template-columns: 80px 1fr 1fr 130px;
  padding: 13px 12px; border-bottom: 1px solid #f8fafc;
  align-items: center; transition: background 0.15s;
}
.fila-cita:hover { background: #f8fafc; }
.celda-hora { font-size: 14px; font-weight: 700; color: #0d8a72; }
.celda-paciente { font-size: 14px; font-weight: 600; color: #1e293b; }
.celda-medico { font-size: 13px; color: #64748b; }
.badge-estado {
  display: inline-flex; align-items: center; padding: 4px 10px;
  border-radius: 20px; font-size: 12px; font-weight: 600; width: fit-content;
}
.badge-completada { background: #d1fae5; color: #065f46; }
.badge-espera     { background: #fef9c3; color: #854d0e; }
.badge-pendiente  { background: #f1f5f9; color: #475569; }
.badge-cancelada  { background: #fee2e2; color: #991b1b; }

.btn-ver-registro {
  display: block; text-align: center; margin-top: 16px; padding: 10px;
  background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px;
  color: #0d8a72; font-weight: 600; font-size: 14px; text-decoration: none;
  transition: background 0.2s;
}
.btn-ver-registro:hover { background: #ccfbf1; }

@media (max-width: 1024px) { .grilla-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
  .grilla-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>