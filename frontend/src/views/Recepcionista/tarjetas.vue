<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const fechaActual = ref('')
const busqueda = ref('')
const citasDelDia = ref([])

onMounted(async () => {
  fechaActual.value = new Date().toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
  const hoy = new Date().toISOString().split('T')[0]
  const res = await fetch(`${BASE_URL}/agenda/todos?fecha=${hoy}`, { headers: getHeaders() })
  if (res.ok) {
    const medicos = await res.json()
    citasDelDia.value = medicos.flatMap(m =>
      (m.citas ?? []).map(c => ({
        id: c.citId,
        hora: c.citHora?.substring(0, 5),
        paciente: `${c.paciente?.pacNombre ?? ''} ${c.paciente?.pacApePat ?? ''}`,
        medico: `Dr. ${m.medNombre} ${m.medApePat}`,
        motivo: c.citMotivo,
        checkin: c.citEstatus === 'confirmada' || c.citEstatus === 'completada',
        horaCheckin: null
      }))
    )
  }
})

const totalCheckin = computed(() => citasDelDia.value.filter(c => c.checkin).length)
const totalPendiente = computed(() => citasDelDia.value.filter(c => !c.checkin).length)
const porcentaje = computed(() => citasDelDia.value.length ? Math.round((totalCheckin.value / citasDelDia.value.length) * 100) : 0)

const citasFiltradas = computed(() => {
  const q = busqueda.value.toLowerCase()
  if (!q) return citasDelDia.value
  return citasDelDia.value.filter(c => c.paciente.toLowerCase().includes(q) || c.medico.toLowerCase().includes(q))
})

const registrarCheckin = async (cita) => {
  const res = await fetch(`${BASE_URL}/citas/${cita.id}`, {
    method: 'PUT', headers: getHeaders(),
    body: JSON.stringify({ citEstatus: 'confirmada' })
  })
  if (res.ok) {
    cita.checkin = true
    const ahora = new Date()
    cita.horaCheckin = `${String(ahora.getHours()).padStart(2,'0')}:${String(ahora.getMinutes()).padStart(2,'0')}`
  }
}

const deshacerCheckin = async (cita) => {
  const res = await fetch(`${BASE_URL}/citas/${cita.id}`, {
    method: 'PUT', headers: getHeaders(),
    body: JSON.stringify({ citEstatus: 'agendada' })
  })
  if (res.ok) { cita.checkin = false; cita.horaCheckin = null }
}

const cerrarSesion = () => { localStorage.clear(); router.push('/login') }
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand"><span class="logo-icon">+</span><h1>MediCita</h1></div>
      <nav class="menu-navegacion">
        <router-link to="/recepcionista/inicio"    class="enlace-menu"><span>🏠</span> Inicio</router-link>
        <router-link to="/recepcionista/citas"     class="enlace-menu"><span>📋</span> Citas</router-link>
        <router-link to="/recepcionista/pacientes" class="enlace-menu"><span>👥</span> Pacientes</router-link>
        <router-link to="/recepcionista/checkin"   class="enlace-menu activo"><span>✅</span> Check-in</router-link>
        <router-link to="/recepcionista/reportes"  class="enlace-menu"><span>📊</span> Reportes</router-link>
        <router-link to="/recepcionista/notificaciones"  class="enlace-menu"><span>🔔</span> Notificaciones</router-link>
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera">
        <div>
          <h1 class="saludo-principal">✅ Check-in del día</h1>
          <p class="subtitulo-fecha">{{ fechaActual }}</p>
        </div>
      </div>

      <!-- Progreso del día -->
      <div class="tarjeta-progreso">
        <div class="stats-checkin">
          <div class="stat-item stat-verde">
            <span class="num-stat">{{ totalCheckin }}</span>
            <span class="label-stat">Llegaron</span>
          </div>
          <div class="stat-item stat-amarillo">
            <span class="num-stat">{{ totalPendiente }}</span>
            <span class="label-stat">Pendientes</span>
          </div>
          <div class="stat-item stat-gris">
            <span class="num-stat">{{ citasDelDia.length }}</span>
            <span class="label-stat">Total del día</span>
          </div>
        </div>
        <div class="barra-progreso-wrap">
          <div class="barra-progreso-fondo">
            <div class="barra-progreso-fill" :style="{ width: porcentaje + '%' }"></div>
          </div>
          <span class="porcentaje-label">{{ porcentaje }}% llegaron</span>
        </div>
      </div>

      <!-- Búsqueda -->
      <div class="busqueda-wrap">
        <input v-model="busqueda" type="text" class="input-busqueda" placeholder="🔍 Buscar paciente o médico..." />
      </div>

      <!-- Lista de citas -->
      <div class="lista-checkin">
        <div
          v-for="cita in citasFiltradas"
          :key="cita.id"
          class="fila-checkin"
          :class="{ 'fila-llegada': cita.checkin }"
        >
          <div class="indicador-checkin" :class="cita.checkin ? 'ind-ok' : 'ind-pendiente'">
            {{ cita.checkin ? '✓' : '·' }}
          </div>

          <div class="hora-col">
            <span class="hora-cita">{{ cita.hora }}</span>
          </div>

          <div class="info-col">
            <p class="nombre-cita">{{ cita.paciente }}</p>
            <p class="detalle-cita">{{ cita.medico }} · {{ cita.motivo }}</p>
          </div>

          <div class="estado-col">
            <span v-if="cita.checkin" class="llegada-tag">
              ✅ Llegó a las {{ cita.horaCheckin }}
            </span>
            <span v-else class="esperando-tag">⏳ Sin llegar</span>
          </div>

          <div class="accion-col">
            <button
              v-if="!cita.checkin"
              @click="registrarCheckin(cita)"
              class="btn-checkin"
            >
              ✓ Registrar llegada
            </button>
            <button
              v-else
              @click="deshacerCheckin(cita)"
              class="btn-deshacer"
            >
              Deshacer
            </button>
          </div>
        </div>

        <div v-if="citasFiltradas.length === 0" class="sin-resultados">
          No se encontraron citas
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background: #f8fafc; }
.sidebar-izquierdo { width: 240px; background: #fff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0; }
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon { background: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; }
.menu-navegacion { display: flex; flex-direction: column; gap: 0.4rem; flex-grow: 1; }
.enlace-menu { display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: #64748b; text-decoration: none; font-weight: 600; border-radius: 10px; transition: all 0.2s; font-size: 0.92rem; }
.enlace-menu:hover { background: #f1f5f9; color: #1e293b; }
.enlace-menu.activo { background: #e6f4f1; color: #0d8a72; }
.sidebar-pie { margin-top: auto; }
.btn-cerrar-sesion { width: 100%; background: none; border: none; color: #b45309; padding: 0.85rem 1rem; font-weight: 600; font-size: 0.92rem; cursor: pointer; text-align: left; border-radius: 10px; transition: background-color 0.2s; }
.btn-cerrar-sesion:hover { background: #fef3c7; }
.contenedor-dashboard { flex-grow: 1; padding: 24px; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
.cabecera { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; }
.saludo-principal { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0 0 4px; }
.subtitulo-fecha { font-size: 14px; color: #64748b; margin: 0; text-transform: capitalize; }

.tarjeta-progreso { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 16px; display: flex; align-items: center; gap: 28px; }
.stats-checkin { display: flex; gap: 24px; flex-shrink: 0; }
.stat-item { display: flex; flex-direction: column; align-items: center; min-width: 60px; }
.num-stat { font-size: 28px; font-weight: 800; line-height: 1; }
.label-stat { font-size: 12px; font-weight: 600; margin-top: 4px; }
.stat-verde .num-stat { color: #10b981; } .stat-verde .label-stat { color: #065f46; }
.stat-amarillo .num-stat { color: #f59e0b; } .stat-amarillo .label-stat { color: #854d0e; }
.stat-gris .num-stat { color: #64748b; } .stat-gris .label-stat { color: #94a3b8; }
.barra-progreso-wrap { flex-grow: 1; }
.barra-progreso-fondo { height: 10px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-bottom: 6px; }
.barra-progreso-fill { height: 100%; background: linear-gradient(90deg, #0d8a72, #10b981); border-radius: 10px; transition: width 0.5s ease; }
.porcentaje-label { font-size: 13px; font-weight: 600; color: #0d8a72; }

.busqueda-wrap { margin-bottom: 12px; }
.input-busqueda { width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; font-family: inherit; background: white; }
.input-busqueda:focus { border-color: #0d8a72; }

.lista-checkin { display: flex; flex-direction: column; gap: 8px; }
.fila-checkin {
  display: flex; align-items: center; gap: 14px; padding: 14px 18px;
  background: white; border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s;
}
.fila-llegada { background: #f0fdfa; border-color: #a7f3d0; }

.indicador-checkin {
  width: 28px; height: 28px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0;
}
.ind-ok { background: #10b981; color: white; }
.ind-pendiente { background: #f1f5f9; color: #94a3b8; font-size: 20px; }

.hora-col { flex-shrink: 0; min-width: 52px; }
.hora-cita { font-size: 15px; font-weight: 700; color: #0d8a72; }

.info-col { flex-grow: 1; }
.nombre-cita { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 2px; }
.detalle-cita { font-size: 12px; color: #64748b; margin: 0; }

.estado-col { flex-shrink: 0; min-width: 160px; }
.llegada-tag { font-size: 13px; font-weight: 600; color: #065f46; }
.esperando-tag { font-size: 13px; font-weight: 600; color: #94a3b8; }

.accion-col { flex-shrink: 0; }
.btn-checkin {
  background: #0d8a72; color: white; border: none; padding: 8px 14px;
  border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: background 0.2s; white-space: nowrap;
}
.btn-checkin:hover { background: #0a7060; }
.btn-deshacer {
  background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 7px 14px;
  border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;
}
.btn-deshacer:hover { background: #e2e8f0; }
.sin-resultados { text-align: center; padding: 40px; font-size: 14px; color: #94a3b8; background: white; border-radius: 10px; border: 1px solid #e2e8f0; }

@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
  .tarjeta-progreso { flex-direction: column; align-items: flex-start; }
  .fila-checkin { flex-wrap: wrap; }
}
</style>