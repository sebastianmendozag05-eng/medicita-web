<script setup>
import { ref, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const notificaciones = ref([])
const cargando = ref(false)

const cargarNotificaciones = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${BASE_URL}/notificaciones`, { headers: getHeaders() })
    if (res.ok) notificaciones.value = await res.json()
  } catch { /* silencioso */ } finally {
    cargando.value = false
  }
}

const marcarLeida = async (id) => {
  const n = notificaciones.value.find(n => n.notifId === id)
  if (!n || n.leida) return
  n.leida = true
  try {
    await fetch(`${BASE_URL}/notificaciones/${id}/leer`, { method: 'PUT', headers: getHeaders() })
  } catch { /* silencioso */ }
}

onMounted(cargarNotificaciones)
</script>

<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Notificaciones</h2>

    <div v-if="cargando" class="space-y-3">
      <p class="text-sm text-gray-500">Cargando...</p>
    </div>

    <div v-else-if="notificaciones.length === 0" class="space-y-3">
      <div class="p-4 rounded-xl border bg-white border-gray-100">
        <p class="text-sm text-gray-600">🔔 No tienes notificaciones por el momento.</p>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="n in notificaciones" :key="n.notifId"
        @click="marcarLeida(n.notifId)"
        class="p-4 rounded-xl border bg-white cursor-pointer"
        :class="n.leida ? 'border-gray-100' : 'border-teal-300'"
      >
        <p class="text-sm font-semibold text-gray-800">{{ n.titulo }}</p>
        <p class="text-sm text-gray-600">{{ n.descripcion }}</p>
      </div>
    </div>
  </div>
</template>
