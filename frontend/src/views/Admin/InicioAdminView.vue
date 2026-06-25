<template>
  <div class="inicio">
    <div class="page-header">
      <div>
        <h2>Bienvenido, Administrador</h2>
        <p class="subtitle">Resumen general del sistema</p>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card" v-for="kpi in kpis" :key="kpi.label">
        <div class="kpi-icon" :style="{ background: kpi.bg }">{{ kpi.icon }}</div>
        <div class="kpi-info">
          <span class="kpi-value">{{ kpi.value }}</span>
          <span class="kpi-label">{{ kpi.label }}</span>
        </div>
        <span class="kpi-trend" :class="kpi.trendUp ? 'up' : 'down'">
          {{ kpi.trendUp ? '▲' : '▼' }} {{ kpi.trend }}
        </span>
      </div>
    </div>

    <div class="bottom-grid">
      <!-- Citas de hoy -->
      <div class="card">
        <div class="card-header">
          <h3>Citas de hoy</h3>
          <router-link to="/admin/citas" class="ver-mas">Ver todas →</router-link>
        </div>
        <table class="mini-table">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Médico</th>
              <th>Hora</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cita in citasHoy" :key="cita.id">
              <td>{{ cita.paciente }}</td>
              <td>{{ cita.medico }}</td>
              <td>{{ cita.hora }}</td>
              <td><span class="chip" :class="cita.estado">{{ cita.estadoLabel }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Actividad reciente -->
      <div class="card">
        <div class="card-header">
          <h3>Actividad reciente</h3>
        </div>
        <ul class="activity-list">
          <li v-for="act in actividad" :key="act.id">
            <span class="act-dot" :style="{ background: act.color }"></span>
            <div class="act-info">
              <span class="act-msg">{{ act.mensaje }}</span>
              <span class="act-time">{{ act.tiempo }}</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const kpis = ref([
  { icon: '👥', label: 'Pacientes registrados', value: '248', trend: '12 este mes', trendUp: true,  bg: '#e6f4f1' },
  { icon: '📅', label: 'Citas hoy',             value: '34',  trend: '5 más que ayer', trendUp: true,  bg: '#eff6ff' },
  { icon: '🩺', label: 'Médicos activos',        value: '18',  trend: '1 menos',        trendUp: false, bg: '#fef9c3' },
  { icon: '🔔', label: 'Notificaciones',         value: '3',   trend: 'Sin resolver',   trendUp: false, bg: '#fee2e2' },
])

const citasHoy = ref([
  { id: 1, paciente: 'Ana Martínez',    medico: 'Dr. López',    hora: '09:00', estado: 'confirmada',  estadoLabel: 'Confirmada'  },
  { id: 2, paciente: 'Luis Hernández',  medico: 'Dra. Ramírez', hora: '10:30', estado: 'pendiente',   estadoLabel: 'Pendiente'   },
  { id: 3, paciente: 'Sofía Torres',    medico: 'Dr. Gómez',    hora: '11:00', estado: 'confirmada',  estadoLabel: 'Confirmada'  },
  { id: 4, paciente: 'Carlos Mendoza',  medico: 'Dr. López',    hora: '12:00', estado: 'cancelada',   estadoLabel: 'Cancelada'   },
  { id: 5, paciente: 'María Sánchez',   medico: 'Dra. Ramírez', hora: '13:30', estado: 'pendiente',   estadoLabel: 'Pendiente'   },
])

const actividad = ref([
  { id: 1, mensaje: 'Nuevo paciente registrado: Pedro Ruiz',      tiempo: 'hace 5 min',  color: '#0d8a72' },
  { id: 2, mensaje: 'Cita cancelada por Dr. Gómez (14:00)',       tiempo: 'hace 18 min', color: '#ef4444' },
  { id: 3, mensaje: 'Dra. Ramírez actualizó su horario',          tiempo: 'hace 45 min', color: '#f59e0b' },
  { id: 4, mensaje: 'Reporte mensual generado correctamente',     tiempo: 'hace 1 h',    color: '#3b82f6' },
  { id: 5, mensaje: 'Nuevo médico añadido: Dr. Vargas',           tiempo: 'hace 3 h',    color: '#0d8a72' },
])
</script>

<style scoped>
.inicio { display: flex; flex-direction: column; gap: 1.5rem; }

.page-header { display: flex; justify-content: space-between; align-items: flex-start; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }

/* KPIs */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.kpi-card {
  background: white;
  border-radius: 14px;
  padding: 1.2rem 1.3rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.kpi-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
.kpi-info { display: flex; flex-direction: column; flex: 1; }
.kpi-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; line-height: 1; }
.kpi-label { font-size: 0.78rem; color: #64748b; margin-top: 0.2rem; }
.kpi-trend { font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
.kpi-trend.up { color: #0d8a72; }
.kpi-trend.down { color: #ef4444; }

/* Bottom grid */
.bottom-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 1.5rem; }
.card { background: white; border-radius: 14px; padding: 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.card-header h3 { margin: 0; font-size: 1rem; font-weight: 600; color: #1e293b; }
.ver-mas { font-size: 0.82rem; color: #0d8a72; text-decoration: none; font-weight: 500; }
.ver-mas:hover { text-decoration: underline; }

/* Table */
.mini-table { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
.mini-table th { text-align: left; color: #94a3b8; font-weight: 600; font-size: 0.78rem; padding: 0 0.5rem 0.6rem; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.03em; }
.mini-table td { padding: 0.65rem 0.5rem; color: #334155; border-bottom: 1px solid #f8fafc; }
.chip { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.chip.confirmada { background: #e6f4f1; color: #0d8a72; }
.chip.pendiente  { background: #fef9c3; color: #a16207; }
.chip.cancelada  { background: #fee2e2; color: #dc2626; }

/* Activity */
.activity-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
.activity-list li { display: flex; align-items: flex-start; gap: 0.75rem; }
.act-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.act-info { display: flex; flex-direction: column; gap: 0.15rem; }
.act-msg { font-size: 0.87rem; color: #334155; }
.act-time { font-size: 0.75rem; color: #94a3b8; }

@media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 800px)  { .bottom-grid { grid-template-columns: 1fr; } }
</style>
