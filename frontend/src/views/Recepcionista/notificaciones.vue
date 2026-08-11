<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const notificaciones = ref([])
const cargando = ref(false)
const tabActiva = ref('todas')

const iconos = { cita: '📅', alerta: '⚠️', sistema: '🔧', usuario: '👤' }
const colores = { cita: '#0d8a72', alerta: '#f59e0b', sistema: '#6366f1', usuario: '#3b82f6' }

const formatearTiempo = (fecha) => {
  const diffMs = Date.now() - new Date(fecha).getTime()
  const min = Math.floor(diffMs / 60000)
  if (min < 1) return 'Ahora mismo'
  if (min < 60) return `Hace ${min} min`
  const horas = Math.floor(min / 60)
  if (horas < 24) return `Hace ${horas} hora${horas > 1 ? 's' : ''}`
  const dias = Math.floor(horas / 24)
  return `Hace ${dias} día${dias > 1 ? 's' : ''}`
}

const cargarNotificaciones = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${BASE_URL}/notificaciones`, { headers: getHeaders() })
    if (res.ok) notificaciones.value = await res.json()
  } catch { /* silencioso */ } finally {
    cargando.value = false
  }
}

const tabs = computed(() => [
  { key: 'todas',   label: 'Todas',    count: notificaciones.value.filter(n => !n.leida).length },
  { key: 'cita',    label: 'Citas',    count: notificaciones.value.filter(n => n.tipo === 'cita'    && !n.leida).length },
  { key: 'alerta',  label: 'Alertas',  count: notificaciones.value.filter(n => n.tipo === 'alerta'  && !n.leida).length },
  { key: 'sistema', label: 'Sistema',  count: notificaciones.value.filter(n => n.tipo === 'sistema' && !n.leida).length },
])

const notificacionesFiltradas = computed(() => {
  if (tabActiva.value === 'todas') return notificaciones.value
  return notificaciones.value.filter(n => n.tipo === tabActiva.value)
})

const marcarLeida = async (id) => {
  const n = notificaciones.value.find(n => n.notifId === id)
  if (!n || n.leida) return
  n.leida = true
  try {
    await fetch(`${BASE_URL}/notificaciones/${id}/leer`, { method: 'PUT', headers: getHeaders() })
  } catch { /* silencioso */ }
}

const marcarTodasLeidas = async () => {
  notificaciones.value.forEach(n => n.leida = true)
  try {
    await fetch(`${BASE_URL}/notificaciones/leer-todas`, { method: 'PUT', headers: getHeaders() })
  } catch { /* silencioso */ }
}

const cerrarSesion = () => { localStorage.clear(); router.push('/login') }

onMounted(cargarNotificaciones)
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      <nav class="menu-navegacion">
        <router-link to="/recepcionista/inicio" class="enlace-menu">
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
        <router-link to="/recepcionista/notificaciones" class="enlace-menu activo">
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
      <div class="cabecera cabecera-notif">
        <h1 class="saludo-principal">🔔 Notificaciones</h1>
        <button
          v-if="notificaciones.some(n => !n.leida)"
          @click="marcarTodasLeidas"
          class="btn-marcar-todas"
        >
          Marcar todas como leídas
        </button>
      </div>

      <div class="tabs-notif">
        <button
          v-for="tab in tabs" :key="tab.key"
          class="tab-btn-notif"
          :class="{ activo: tabActiva === tab.key }"
          @click="tabActiva = tab.key"
        >
          {{ tab.label }}
          <span v-if="tab.count > 0" class="tab-badge-notif">{{ tab.count }}</span>
        </button>
      </div>

      <div v-if="cargando" class="tarjeta-vacia">
        <p class="texto-vacio-simple">Cargando...</p>
      </div>
      <div v-else-if="notificacionesFiltradas.length === 0" class="tarjeta-vacia">
        <p class="texto-vacio-simple">No tienes notificaciones en esta categoría.</p>
      </div>
      <div v-else class="lista-notif">
        <div
          v-for="n in notificacionesFiltradas" :key="n.notifId"
          @click="marcarLeida(n.notifId)"
          class="fila-notif"
          :class="{ 'fila-no-leida': !n.leida }"
        >
          <div class="icono-notif" :style="{ background: (colores[n.tipo] || '#64748b') + '22', color: colores[n.tipo] || '#64748b' }">
            {{ iconos[n.tipo] || '🔔' }}
          </div>
          <div class="cuerpo-notif">
            <p class="titulo-notif">{{ n.titulo }}</p>
            <p class="desc-notif">{{ n.descripcion }}</p>
            <p class="tiempo-notif">{{ formatearTiempo(n.created_at) }}</p>
          </div>
          <div v-if="!n.leida" class="punto-no-leida"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background-color: #f8fafc; }
.sidebar-izquierdo {
  width: 260px; background-color: #ffffff; border-right: 1px solid #e2e8f0;
  display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; }
.menu-navegacion { display: flex; flex-direction: column; gap: 0.5rem; flex-grow: 1; }
.enlace-menu {
  display: flex; align-items: center; gap: 0.8rem; padding: 0.85rem 1rem;
  color: #64748b; text-decoration: none; font-weight: 600; border-radius: 10px;
  transition: all 0.2s; font-size: 0.95rem;
}
.enlace-menu:hover { background-color: #f1f5f9; color: #1e293b; }
.enlace-menu.activo { background-color: #e6f4f1; color: #0d8a72; }
.sidebar-pie { margin-top: auto; }
.btn-cerrar-sesion {
  width: 100%; background: none; border: none; color: #b45309;
  padding: 0.85rem 1rem; font-weight: 600; font-size: 0.95rem;
  cursor: pointer; text-align: left; border-radius: 10px; transition: background-color 0.2s;
}
.btn-cerrar-sesion:hover { background-color: #fef3c7; }
.contenedor-dashboard {
  flex-grow: 1; padding: 24px; box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.cabecera { margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; }
.cabecera-notif { display: flex; align-items: center; justify-content: space-between; }
.saludo-principal { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
.btn-marcar-todas {
  background: none; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.5rem 0.9rem;
  font-size: 0.82rem; font-weight: 600; color: #64748b; cursor: pointer;
}
.btn-marcar-todas:hover { background-color: #f8fafc; }

.tabs-notif { display: flex; gap: 0.4rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 16px; flex-wrap: wrap; }
.tab-btn-notif {
  background: none; border: none; border-bottom: 2px solid transparent; padding: 0.5rem 0.9rem;
  font-size: 0.86rem; color: #64748b; cursor: pointer; font-weight: 600;
  display: flex; align-items: center; gap: 0.4rem; margin-bottom: -1px;
}
.tab-btn-notif.activo { color: #0d8a72; border-bottom-color: #0d8a72; }
.tab-badge-notif { background: #ef4444; color: white; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 99px; }

.tarjeta-vacia { background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 40px; text-align: center; }
.texto-vacio-simple { color: #94a3b8; font-size: 14px; margin: 0; }
.lista-notif { display: flex; flex-direction: column; gap: 10px; }
.fila-notif {
  display: flex; align-items: flex-start; gap: 0.85rem;
  background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px;
  cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease;
}
.fila-notif:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.fila-no-leida { border-color: #99f6e4; background-color: #f0fdfa; }
.icono-notif {
  width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.cuerpo-notif { flex: 1; min-width: 0; }
.titulo-notif { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; }
.desc-notif { font-size: 13px; color: #64748b; margin: 0; }
.tiempo-notif { font-size: 11px; color: #94a3b8; margin: 6px 0 0 0; }
.punto-no-leida { width: 8px; height: 8px; border-radius: 50%; background: #0d8a72; flex-shrink: 0; margin-top: 6px; }

@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
}
</style>
