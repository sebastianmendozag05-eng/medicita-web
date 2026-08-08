<script setup>
import { ref, computed, onMounted } from 'vue'

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

onMounted(cargarNotificaciones)
</script>

<template>
  <div class="contenedor-notif">
    <div class="encabezado-notif">
      <div>
        <h2 class="titulo-principal">Notificaciones</h2>
        <p class="subtitulo">Avisos sobre tus citas y tu cuenta</p>
      </div>
      <button v-if="notificaciones.some(n => !n.leida)" @click="marcarTodasLeidas" class="btn-marcar-todas">
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
      <p class="texto-vacio-principal">Cargando...</p>
    </div>

    <div v-else-if="notificacionesFiltradas.length === 0" class="tarjeta-vacia">
      <p class="texto-vacio-principal">🔔 No tienes notificaciones en esta categoría.</p>
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
</template>

<style scoped>
.contenedor-notif { padding: 24px; max-width: 850px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
.encabezado-notif { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.titulo-principal { font-size: 24px; font-weight: 600; color: #1e293b; margin: 0; }
.subtitulo { font-size: 13px; color: #64748b; margin: 4px 0 0 0; }
.btn-marcar-todas {
  background: none; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 14px;
  font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; white-space: nowrap;
}
.btn-marcar-todas:hover { background-color: #f8fafc; }

.tabs-notif { display: flex; gap: 4px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px; flex-wrap: wrap; }
.tab-btn-notif {
  background: none; border: none; border-bottom: 2px solid transparent; padding: 8px 14px;
  font-size: 13px; color: #64748b; cursor: pointer; font-weight: 500;
  display: flex; align-items: center; gap: 6px; margin-bottom: -1px;
}
.tab-btn-notif.activo { color: #115e59; border-bottom-color: #115e59; font-weight: 600; }
.tab-badge-notif { background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 6px; border-radius: 9999px; }

.tarjeta-vacia { background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px; text-align: center; }
.texto-vacio-principal { color: #64748b; font-weight: 500; font-size: 15px; margin: 0; }

.lista-notif { display: flex; flex-direction: column; gap: 12px; }
.fila-notif {
  background-color: #ffffff; padding: 16px; border-radius: 16px; border: 1px solid #e2e8f0;
  display: flex; align-items: flex-start; gap: 14px; cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease;
}
.fila-notif:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.fila-no-leida { border-color: #99f6e4; background-color: #f0fdfa; }
.icono-notif {
  width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.cuerpo-notif { flex: 1; min-width: 0; }
.titulo-notif { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; }
.desc-notif { font-size: 13px; color: #64748b; margin: 0; }
.tiempo-notif { font-size: 11px; color: #94a3b8; margin: 6px 0 0 0; }
.punto-no-leida { width: 8px; height: 8px; border-radius: 50%; background-color: #14b8a6; flex-shrink: 0; margin-top: 6px; }

@media (max-width: 640px) {
  .encabezado-notif { flex-direction: column; gap: 10px; }
}
</style>
