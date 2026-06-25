<template>
  <div class="notif-view">
    <div class="page-header">
      <div>
        <h2>Notificaciones</h2>
        <p class="subtitle">Alertas y eventos recientes del sistema</p>
      </div>
      <button class="btn-ghost" @click="marcarTodasLeidas">Marcar todas como leídas</button>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button
        v-for="tab in tabs" :key="tab.key"
        class="tab-btn"
        :class="{ active: tabActiva === tab.key }"
        @click="tabActiva = tab.key"
      >
        {{ tab.label }}
        <span v-if="tab.count > 0" class="tab-badge">{{ tab.count }}</span>
      </button>
    </div>

    <!-- Lista -->
    <div class="notif-list">
      <div
        v-for="n in notificacionesFiltradas"
        :key="n.id"
        class="notif-item"
        :class="{ unread: !n.leida }"
        @click="marcarLeida(n.id)"
      >
        <div class="notif-icon" :style="{ background: n.color + '22', color: n.color }">{{ n.icon }}</div>
        <div class="notif-body">
          <div class="notif-titulo">{{ n.titulo }}</div>
          <div class="notif-desc">{{ n.descripcion }}</div>
          <div class="notif-tiempo">{{ n.tiempo }}</div>
        </div>
        <div class="notif-dot" v-if="!n.leida"></div>
      </div>

      <div v-if="notificacionesFiltradas.length === 0" class="empty">
        No hay notificaciones en esta categoría.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const tabActiva = ref('todas')

const notificaciones = ref([
  { id: 1, tipo: 'cita',     icon: '📅', color: '#0d8a72', titulo: 'Nueva cita registrada',              descripcion: 'El paciente Pedro Ruiz agendó una cita para el 28 de junio a las 10:00.',       tiempo: 'Hace 5 min',   leida: false },
  { id: 2, tipo: 'alerta',   icon: '⚠️', color: '#f59e0b', titulo: 'Cita cancelada',                    descripcion: 'El Dr. Gómez canceló la cita de las 14:00 del día de hoy.',                   tiempo: 'Hace 18 min',  leida: false },
  { id: 3, tipo: 'sistema',  icon: '🔧', color: '#6366f1', titulo: 'Mantenimiento programado',          descripcion: 'El sistema estará en mantenimiento el domingo 30 de junio de 2:00 a 4:00 AM.', tiempo: 'Hace 1 hora',  leida: false },
  { id: 4, tipo: 'usuario',  icon: '👤', color: '#3b82f6', titulo: 'Nuevo médico registrado',           descripcion: 'El Dr. Vargas completó su registro. Pendiente de validación.',                  tiempo: 'Hace 3 horas', leida: true  },
  { id: 5, tipo: 'cita',     icon: '📅', color: '#0d8a72', titulo: 'Cita completada',                   descripcion: 'La cita de Ana Martínez con la Dra. Ramírez fue marcada como completada.',     tiempo: 'Ayer, 16:45',  leida: true  },
  { id: 6, tipo: 'alerta',   icon: '⚠️', color: '#f59e0b', titulo: 'Paciente sin asistir',             descripcion: 'Luis Hernández no se presentó a su cita de las 10:30.',                        tiempo: 'Ayer, 11:00',  leida: true  },
  { id: 7, tipo: 'sistema',  icon: '✅', color: '#22c55e', titulo: 'Reporte generado exitosamente',    descripcion: 'El reporte mensual de mayo fue generado y está disponible para descarga.',       tiempo: 'Hace 2 días',  leida: true  },
])

const tabs = computed(() => [
  { key: 'todas',   label: 'Todas',    count: notificaciones.value.filter(n => !n.leida).length },
  { key: 'cita',    label: 'Citas',    count: notificaciones.value.filter(n => n.tipo === 'cita'    && !n.leida).length },
  { key: 'alerta',  label: 'Alertas',  count: notificaciones.value.filter(n => n.tipo === 'alerta'  && !n.leida).length },
  { key: 'sistema', label: 'Sistema',  count: notificaciones.value.filter(n => n.tipo === 'sistema' && !n.leida).length },
  { key: 'usuario', label: 'Usuarios', count: notificaciones.value.filter(n => n.tipo === 'usuario' && !n.leida).length },
])

const notificacionesFiltradas = computed(() => {
  if (tabActiva.value === 'todas') return notificaciones.value
  return notificaciones.value.filter(n => n.tipo === tabActiva.value)
})

const marcarLeida = (id) => {
  const n = notificaciones.value.find(n => n.id === id)
  if (n) n.leida = true
}

const marcarTodasLeidas = () => {
  notificaciones.value.forEach(n => n.leida = true)
}
</script>

<style scoped>
.notif-view { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }

.btn-ghost { background: none; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.55rem 1rem; font-size: 0.85rem; color: #64748b; cursor: pointer; font-weight: 500; }
.btn-ghost:hover { background: #f8fafc; }

/* Tabs */
.tabs { display: flex; gap: 0.4rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0; flex-wrap: wrap; }
.tab-btn { background: none; border: none; border-bottom: 2px solid transparent; padding: 0.5rem 1rem; font-size: 0.88rem; color: #64748b; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 0.4rem; margin-bottom: -1px; transition: color 0.15s; }
.tab-btn.active { color: #0d8a72; border-bottom-color: #0d8a72; }
.tab-badge { background: #ef4444; color: white; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 99px; }

/* List */
.notif-list { display: flex; flex-direction: column; gap: 0.5rem; }
.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  background: white;
  border-radius: 12px;
  padding: 1rem 1.2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  cursor: pointer;
  transition: background 0.15s;
  position: relative;
}
.notif-item:hover { background: #f8fafc; }
.notif-item.unread { border-left: 3px solid #0d8a72; }

.notif-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.notif-body { flex: 1; }
.notif-titulo { font-size: 0.92rem; font-weight: 600; color: #1e293b; margin-bottom: 0.2rem; }
.notif-desc { font-size: 0.84rem; color: #64748b; line-height: 1.4; }
.notif-tiempo { font-size: 0.76rem; color: #94a3b8; margin-top: 0.4rem; }
.notif-dot { width: 8px; height: 8px; background: #0d8a72; border-radius: 50%; flex-shrink: 0; margin-top: 6px; }

.empty { text-align: center; color: #94a3b8; padding: 3rem; background: white; border-radius: 12px; }
</style>
