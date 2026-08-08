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
  <div class="p-6">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Notificaciones</h2>
        <p class="text-sm text-gray-500">Avisos sobre tus citas y tu cuenta</p>
      </div>
      <button
        v-if="notificaciones.some(n => !n.leida)"
        @click="marcarTodasLeidas"
        class="text-sm font-medium text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5 hover:bg-gray-50"
      >
        Marcar todas como leídas
      </button>
    </div>

    <div class="flex gap-1 border-b border-gray-200 mb-4 flex-wrap">
      <button
        v-for="tab in tabs" :key="tab.key"
        @click="tabActiva = tab.key"
        class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium border-b-2 -mb-px"
        :class="tabActiva === tab.key ? 'border-teal-600 text-teal-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        {{ tab.label }}
        <span v-if="tab.count > 0" class="bg-red-500 text-white text-[11px] font-bold px-1.5 py-0.5 rounded-full">{{ tab.count }}</span>
      </button>
    </div>

    <div v-if="cargando" class="space-y-3">
      <p class="text-sm text-gray-500">Cargando...</p>
    </div>

    <div v-else-if="notificacionesFiltradas.length === 0" class="space-y-3">
      <div class="p-8 rounded-xl border bg-white border-gray-100 text-center">
        <p class="text-sm text-gray-500">🔔 No tienes notificaciones en esta categoría.</p>
      </div>
    </div>

    <div v-else class="space-y-2">
      <div
        v-for="n in notificacionesFiltradas" :key="n.notifId"
        @click="marcarLeida(n.notifId)"
        class="flex items-start gap-3 p-4 rounded-xl border bg-white cursor-pointer hover:bg-gray-50"
        :class="n.leida ? 'border-gray-100' : 'border-teal-200'"
      >
        <div
          class="w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0"
          :style="{ background: (colores[n.tipo] || '#64748b') + '22', color: colores[n.tipo] || '#64748b' }"
        >
          {{ iconos[n.tipo] || '🔔' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-800">{{ n.titulo }}</p>
          <p class="text-sm text-gray-600">{{ n.descripcion }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ formatearTiempo(n.created_at) }}</p>
        </div>
        <div v-if="!n.leida" class="w-2 h-2 rounded-full bg-teal-600 mt-2 shrink-0"></div>
      </div>
    </div>
  </div>
</template>
