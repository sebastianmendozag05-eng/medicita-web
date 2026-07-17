<template>
  <div class="reportes-view">
    <div class="page-header">
      <div>
        <h2>Reportes</h2>
        <p class="subtitle">Estadísticas generales del sistema</p>
      </div>
      <div class="header-actions">
        <select v-model="periodoSeleccionado" class="select-filtro">
          <option value="semana">Esta semana</option>
          <option value="mes">Este mes</option>
          <option value="anio">Este año</option>
        </select>
        <button class="btn-primary">⬇ Exportar PDF</button>
      </div>
    </div>

    <!-- Resumen -->
    <div class="resumen-grid">
      <div class="res-card" v-for="r in resumen" :key="r.label">
        <span class="res-icon">{{ r.icon }}</span>
        <div>
          <div class="res-value">{{ r.value }}</div>
          <div class="res-label">{{ r.label }}</div>
        </div>
      </div>
    </div>

    <div class="charts-grid">
      <!-- Citas por día (barras CSS) -->
      <div class="card">
        <h3>Citas por día (semana actual)</h3>
        <div class="bar-chart">
          <div class="bar-group" v-for="d in citasPorDia" :key="d.dia">
            <div class="bar-wrap">
              <span class="bar-val">{{ d.total }}</span>
              <div class="bar" :style="{ height: (d.total / maxCitas * 100) + '%' }"></div>
            </div>
            <span class="bar-label">{{ d.dia }}</span>
          </div>
        </div>
      </div>

      <!-- Distribución por especialidad -->
      <div class="card">
        <h3>Citas por especialidad</h3>
        <div class="esp-list" v-if="porEspecialidad.length > 0">
          <div class="esp-row" v-for="e in porEspecialidad" :key="e.nombre">
            <span class="esp-nombre">{{ e.nombre }}</span>
            <div class="esp-bar-wrap">
              <div class="esp-bar" :style="{ width: (e.pct) + '%' }"></div>
            </div>
            <span class="esp-pct">{{ e.pct }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de médicos -->
    <div class="card">
      <h3>Rendimiento por médico</h3>
      <table class="tabla">
        <thead>
          <tr>
            <th>Médico</th>
            <th>Especialidad</th>
            <th>Citas totales</th>
            <th>Completadas</th>
            <th>Canceladas</th>
            <th>% Asistencia</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in medicos" :key="m.nombre">
            <td class="nombre-col">{{ m.nombre }}</td>
            <td>{{ m.especialidad }}</td>
            <td>{{ m.total }}</td>
            <td>{{ m.completadas }}</td>
            <td>{{ m.canceladas }}</td>
            <td>
              <div class="pct-wrap">
                <div class="pct-bar" :style="{ width: m.asistencia + '%', background: m.asistencia >= 80 ? '#0d8a72' : '#f59e0b' }"></div>
                <span>{{ m.asistencia }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'

const getHeaders = () => ({
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('token')}`
})

const periodoSeleccionado = ref('semana')

const resumen = ref([])
const citasPorDia = ref([])
const maxCitas = ref(1)
const porEspecialidad = ref([])
const medicos = ref([])

const nombresDias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

const cargarReportes = async () => {
  try {
    const [resResumen, resMedicos, resCitas] = await Promise.all([
      fetch(`${BASE_URL}/reportes/resumen`, { headers: getHeaders() }),
      fetch(`${BASE_URL}/medicos`, { headers: getHeaders() }),
      fetch(`${BASE_URL}/citas`, { headers: getHeaders() })
    ])

    if (resResumen.ok) {
      const r = await resResumen.json()
      resumen.value = [
        { icon: '📅', label: 'Total de citas', value: r.total_citas ?? 0 },
        { icon: '✅', label: 'Completadas', value: r.completadas ?? 0 },
        { icon: '✕', label: 'Canceladas', value: r.canceladas ?? 0 },
        { icon: '👨‍⚕️', label: 'Médicos activos', value: r.medicos_activos ?? 0 }
      ]
    }

    const listaMedicos = resMedicos.ok ? await resMedicos.json() : []
    const listaCitas = resCitas.ok ? await resCitas.json() : []
    const citas = Array.isArray(listaCitas) ? listaCitas : (listaCitas.data ?? [])

    // Citas por día (semana actual)
    const conteoPorDia = [0, 0, 0, 0, 0, 0, 0]
    citas.forEach(c => {
      if (!c.citFecha) return
      const dia = new Date(c.citFecha).getDay()
      conteoPorDia[dia]++
    })
    citasPorDia.value = nombresDias.map((dia, i) => ({ dia, total: conteoPorDia[i] }))
    maxCitas.value = Math.max(...conteoPorDia, 1)

    // Distribución por motivo/especialidad
    const conteoMotivo = {}
    citas.forEach(c => {
      const motivo = c.citMotivo || 'Otro'
      conteoMotivo[motivo] = (conteoMotivo[motivo] ?? 0) + 1
    })
    const totalCitas = citas.length || 1
    porEspecialidad.value = Object.entries(conteoMotivo).map(([nombre, total]) => ({
      nombre,
      pct: Math.round((total / totalCitas) * 100)
    }))

    // Rendimiento por médico
    const detalles = await Promise.all(
      listaMedicos.map(m => fetch(`${BASE_URL}/reportes/medico/${m.medId}`, { headers: getHeaders() }).then(r => r.json()))
    )
    medicos.value = detalles.map(d => ({
      nombre: `Dr. ${d.medico?.medNombre ?? ''} ${d.medico?.medApePat ?? ''}`,
      especialidad: '—',
      total: d.total,
      completadas: d.completadas,
      canceladas: d.canceladas,
      asistencia: d.total ? Math.round((d.completadas / d.total) * 100) : 0
    }))
  } catch (error) {
    console.error('Error al cargar reportes:', error)
  }
}

onMounted(() => {
  cargarReportes()
})
</script>

<style scoped>
.reportes-view { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }
.header-actions { display: flex; gap: 0.75rem; align-items: center; }

.select-filtro { padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; background: white; }
.btn-primary { background: #0d8a72; color: white; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }
.btn-primary:hover { background: #0a6c59; }

/* Resumen */
.resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.res-card { background: white; border-radius: 14px; padding: 1.2rem 1.3rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.res-icon { font-size: 1.8rem; }
.res-value { font-size: 1.8rem; font-weight: 700; color: #1e293b; line-height: 1; }
.res-label { font-size: 0.78rem; color: #64748b; margin-top: 0.2rem; }

/* Charts */
.charts-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem; }
.card { background: white; border-radius: 14px; padding: 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.card h3 { margin: 0 0 1.2rem; font-size: 1rem; font-weight: 600; color: #1e293b; }

/* Bar chart */
.bar-chart { display: flex; align-items: flex-end; gap: 0.6rem; height: 140px; padding-bottom: 1.5rem; position: relative; }
.bar-group { display: flex; flex-direction: column; align-items: center; gap: 0.3rem; flex: 1; height: 100%; justify-content: flex-end; }
.bar-wrap { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; width: 100%; flex: 1; justify-content: flex-end; }
.bar-val { font-size: 0.72rem; color: #64748b; font-weight: 600; }
.bar { width: 100%; background: #0d8a72; border-radius: 6px 6px 0 0; min-height: 4px; transition: height 0.3s; }
.bar-label { font-size: 0.75rem; color: #94a3b8; }

/* Especialidad */
.esp-list { display: flex; flex-direction: column; gap: 1rem; }
.esp-row { display: flex; align-items: center; gap: 0.75rem; font-size: 0.87rem; }
.esp-nombre { width: 140px; color: #334155; font-size: 0.82rem; flex-shrink: 0; }
.esp-bar-wrap { flex: 1; background: #f1f5f9; border-radius: 99px; height: 8px; overflow: hidden; }
.esp-bar { height: 100%; background: #0d8a72; border-radius: 99px; }
.esp-pct { width: 36px; text-align: right; color: #64748b; font-size: 0.8rem; font-weight: 600; }

/* Tabla */
.tabla { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
.tabla th { text-align: left; color: #94a3b8; font-weight: 600; font-size: 0.75rem; padding: 0 0.75rem 0.7rem; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
.tabla td { padding: 0.7rem 0.75rem; color: #334155; border-bottom: 1px solid #f8fafc; }
.tabla tbody tr:hover { background: #f8fafc; }
.nombre-col { font-weight: 500; }
.pct-wrap { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; font-weight: 600; color: #334155; }
.pct-bar { height: 6px; border-radius: 99px; flex-shrink: 0; width: 0; }

@media (max-width: 1100px) { .resumen-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 800px)  { .charts-grid { grid-template-columns: 1fr; } }
</style>
