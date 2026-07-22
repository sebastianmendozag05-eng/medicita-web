<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const hoy = new Date()
const mesActual = ref(hoy.getMonth())
const anioActual = ref(hoy.getFullYear())
const fechaSeleccionada = ref(hoy.toISOString().split('T')[0])
const filtroBusqueda = ref('')
const filtroEstado = ref('todos')
const todasLasCitas = ref([])

const nombresMeses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
const diasSemana = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb']

onMounted(async () => {
  const medId = localStorage.getItem('medicoId')
  if (!medId) return
  const inicio = `${anioActual.value}-${String(mesActual.value+1).padStart(2,'0')}-01`
  const fin = `${anioActual.value}-${String(mesActual.value+1).padStart(2,'0')}-31`
  const res = await fetch(`${BASE_URL}/agenda/medico/${medId}/semana?inicio=${inicio}&fin=${fin}`, { headers: getHeaders() })
  if (res.ok) {
    const data = await res.json()
    todasLasCitas.value = (data.citas ?? []).map(c => ({
      id: c.citId,
      pacId: c.paciente?.pacId,
      paciente: `${c.paciente?.pacNombre ?? ''} ${c.paciente?.pacApePat ?? ''}`,
      motivo: c.citMotivo,
      fecha: c.citFecha?.split('T')[0] ?? c.citFecha,
      hora: c.citHora?.substring(0,5),
      estado: c.citEstatus,
      tieneNota: false
    }))
  }
})

const diasDelMes = computed(() => {
  const primerDia = new Date(anioActual.value, mesActual.value, 1).getDay()
  const totalDias = new Date(anioActual.value, mesActual.value + 1, 0).getDate()
  const dias = []
  for (let i = 0; i < primerDia; i++) dias.push(null)
  for (let d = 1; d <= totalDias; d++) dias.push(d)
  return dias
})

const fechaFormato = (dia) => {
  const m = String(mesActual.value + 1).padStart(2, '0')
  const d = String(dia).padStart(2, '0')
  return `${anioActual.value}-${m}-${d}`
}

const tieneCitas = (dia) => dia && todasLasCitas.value.some(c => c.fecha === fechaFormato(dia))
const seleccionarDia = (dia) => { if (dia) fechaSeleccionada.value = fechaFormato(dia) }
const mesPrevio = () => { if (mesActual.value === 0) { mesActual.value = 11; anioActual.value-- } else mesActual.value-- }
const mesSiguiente = () => { if (mesActual.value === 11) { mesActual.value = 0; anioActual.value++ } else mesActual.value++ }
const esHoy = (dia) => dia && fechaFormato(dia) === hoy.toISOString().split('T')[0]
const esDiaSeleccionado = (dia) => dia && fechaFormato(dia) === fechaSeleccionada.value

const citasFiltradas = computed(() => todasLasCitas.value.filter(c => {
  const coincideFecha = c.fecha === fechaSeleccionada.value
  const coincideBusqueda = c.paciente.toLowerCase().includes(filtroBusqueda.value.toLowerCase()) || c.motivo.toLowerCase().includes(filtroBusqueda.value.toLowerCase())
  const coincideEstado = filtroEstado.value === 'todos' || c.estado === filtroEstado.value
  return coincideFecha && coincideBusqueda && coincideEstado
}))

const citasProximas = computed(() => {
  const hoyStr = hoy.toISOString().split('T')[0]
  return todasLasCitas.value.filter(c => c.fecha >= hoyStr && c.fecha !== fechaSeleccionada.value).sort((a,b) => (a.fecha+a.hora).localeCompare(b.fecha+b.hora)).slice(0,5)
})

const formatearFechaLegible = (f) => {
  if (!f) return ''
  return new Date(f).toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })
}

const esPendiente = (estado) => estado === 'agendada' || estado === 'confirmada'
const colorEstado = (e) => ({ agendada: 'estado-pendiente', confirmada: 'estado-pendiente', completada: 'estado-completada', cancelada: 'estado-cancelada' })[e] || 'estado-pendiente'

const cambiarEstado = async (cita, nuevoEstado) => {
  const res = await fetch(`${BASE_URL}/citas/${cita.id}`, {
    method: 'PUT', headers: getHeaders(),
    body: JSON.stringify({ citEstatus: nuevoEstado })
  })
  if (res.ok) cita.estado = nuevoEstado
}

// ── Modal de nota de consulta al completar una cita ──
const modalNotaAbierto = ref(false)
const citaEnNota = ref(null)
const notaDiagnostico = ref('')
const notaReceta = ref('')
const guardandoNota = ref(false)
const errorNota = ref('')

const abrirModalNota = (cita) => {
  citaEnNota.value = cita
  notaDiagnostico.value = ''
  notaReceta.value = ''
  errorNota.value = ''
  modalNotaAbierto.value = true
}
const cerrarModalNota = () => { modalNotaAbierto.value = false; citaEnNota.value = null }

const guardarNotaYCompletar = async () => {
  if (!notaDiagnostico.value.trim()) { errorNota.value = 'El diagnóstico es obligatorio.'; return }
  const cita = citaEnNota.value
  const medId = localStorage.getItem('medicoId')
  guardandoNota.value = true
  errorNota.value = ''
  try {
    const resNota = await fetch(`${BASE_URL}/notas`, {
      method: 'POST', headers: getHeaders(),
      body: JSON.stringify({
        citId: cita.id,
        medId: parseInt(medId),
        pacId: cita.pacId,
        notaDiagnostico: notaDiagnostico.value,
        notaReceta: notaReceta.value || null
      })
    })
    if (!resNota.ok) {
      const err = await resNota.json()
      errorNota.value = err.message ?? 'No se pudo guardar la nota.'
      return
    }
    await cambiarEstado(cita, 'completada')
    cita.tieneNota = true
    cerrarModalNota()
  } catch {
    errorNota.value = 'No se pudo conectar con el servidor.'
  } finally {
    guardandoNota.value = false
  }
}

const cerrarSesion = () => { localStorage.clear(); router.push('/login') }
</script>

<template>
  <div class="pantalla-layout">

    <aside class="sidebar-izquierdo">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      <nav class="menu-navegacion">
        <router-link to="/medico/inicio" class="enlace-menu">
          <span class="icono">🏠</span> Inicio
        </router-link>
        <router-link to="/medico/perfil" class="enlace-menu">
          <span class="icono">👤</span> Mi Perfil
        </router-link>
        <router-link to="/medico/agenda" class="enlace-menu activo">
          <span class="icono">📅</span> Mis Citas
        </router-link>
        <router-link to="/medico/historiales" class="enlace-menu">
          <span class="icono">📂</span> Historial Médico
        </router-link>
        <router-link to="/medico/notificaciones" class="enlace-menu">
          <span class="icono">🔔</span> Notificaciones
        </router-link>
      </nav>
      <div class="sidebar-pie">
        <button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button>
      </div>
    </aside>

    <div class="contenedor-dashboard">

      <div class="cabecera-medico">
        <h1 class="saludo-principal">📅 Mis Citas</h1>
        <span class="fecha-cabecera">Agenda del médico</span>
      </div>

      <div class="layout-agenda">

        <!-- Columna izquierda: calendario + próximas -->
        <div class="columna-izquierda">

          <!-- Calendario -->
          <div class="tarjeta-calendario">
            <div class="nav-mes">
              <button @click="mesPrevio" class="btn-nav-mes">‹</button>
              <span class="titulo-mes">{{ nombresMeses[mesActual] }} {{ anioActual }}</span>
              <button @click="mesSiguiente" class="btn-nav-mes">›</button>
            </div>

            <div class="grilla-semana">
              <div v-for="d in diasSemana" :key="d" class="encabezado-dia">{{ d }}</div>
            </div>
            <div class="grilla-dias">
              <div
                v-for="(dia, idx) in diasDelMes"
                :key="idx"
                class="celda-dia"
                :class="{
                  'celda-vacia': !dia,
                  'celda-hoy': esHoy(dia),
                  'celda-seleccionada': esDiaSeleccionado(dia),
                  'celda-con-citas': dia && tieneCitas(dia)
                }"
                @click="seleccionarDia(dia)"
              >
                <span v-if="dia">{{ dia }}</span>
                <span v-if="dia && tieneCitas(dia)" class="punto-cita"></span>
              </div>
            </div>
          </div>

          <!-- Próximas citas -->
          <div class="tarjeta-proximas">
            <h3 class="subtitulo-mini">Próximas citas</h3>
            <div v-if="citasProximas.length === 0" class="sin-proximas">
              No hay más citas programadas
            </div>
            <div v-for="cita in citasProximas" :key="cita.id" class="fila-proxima">
              <div class="punto-proxima"></div>
              <div>
                <p class="proxima-paciente">{{ cita.paciente }}</p>
                <p class="proxima-fecha">{{ formatearFechaLegible(cita.fecha) }} · {{ cita.hora }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Columna derecha: citas del día seleccionado -->
        <div class="columna-derecha">
          <div class="tarjeta-citas-dia">
            <div class="encabezado-citas">
              <div>
                <h2 class="titulo-dia">{{ formatearFechaLegible(fechaSeleccionada) }}</h2>
                <p class="conteo-citas">{{ citasFiltradas.length }} cita(s) encontrada(s)</p>
              </div>
            </div>

            <!-- Búsqueda y filtro -->
            <div class="barra-filtros">
              <input
                v-model="filtroBusqueda"
                type="text"
                class="input-busqueda"
                placeholder="🔍 Buscar paciente o motivo..."
              />
              <select v-model="filtroEstado" class="select-estado">
                <option value="todos">Todos los estados</option>
                <option value="agendada">Agendada</option>
                <option value="confirmada">Confirmada</option>
                <option value="completada">Completada</option>
                <option value="cancelada">Cancelada</option>
              </select>
            </div>

            <!-- Lista de citas -->
            <div v-if="citasFiltradas.length > 0" class="lista-citas">
              <div v-for="cita in citasFiltradas" :key="cita.id" class="tarjeta-cita">
                <div class="cita-hora-col">
                  <span class="hora-grande">{{ cita.hora }}</span>
                </div>
                <div class="cita-info-col">
                  <p class="nombre-paciente-cita">{{ cita.paciente }}</p>
                  <p class="motivo-cita">{{ cita.motivo }}</p>
                  <span :class="['badge-estado', colorEstado(cita.estado)]">
                    {{ cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1) }}
                  </span>
                </div>
                <div class="cita-acciones-col">
                  <button
                    v-if="esPendiente(cita.estado)"
                    @click="abrirModalNota(cita)"
                    class="btn-accion btn-completar"
                    title="Marcar como completada"
                  >✓ Completar</button>
                  <button
                    v-if="esPendiente(cita.estado)"
                    @click="cambiarEstado(cita, 'cancelada')"
                    class="btn-accion btn-cancelar-cita"
                    title="Cancelar cita"
                  >✕ Cancelar</button>
                  <button
                    v-if="!esPendiente(cita.estado)"
                    @click="cambiarEstado(cita, 'agendada')"
                    class="btn-accion btn-reactivar"
                    title="Reactivar cita"
                  >↩ Reactivar</button>
                </div>
              </div>
            </div>

            <div v-else class="contenedor-sin-citas">
              <div class="icono-vacio">📅</div>
              <p class="texto-vacio">No hay citas para este día</p>
              <p class="subtexto-vacio">Selecciona otro día en el calendario</p>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- Modal: nota de consulta al completar cita -->
    <div v-if="modalNotaAbierto" class="overlay-modal" @click.self="cerrarModalNota">
      <div class="tarjeta-modal">
        <h3 class="titulo-modal">Nota de consulta — {{ citaEnNota?.paciente }}</h3>
        <p class="subtitulo-modal">Registra el diagnóstico antes de marcar la cita como completada.</p>

        <div class="form-group-modal">
          <label>Diagnóstico *</label>
          <textarea v-model="notaDiagnostico" rows="3" placeholder="Diagnóstico del paciente..."></textarea>
        </div>
        <div class="form-group-modal">
          <label>Receta (opcional)</label>
          <textarea v-model="notaReceta" rows="2" placeholder="Medicamentos, indicaciones..."></textarea>
        </div>

        <p v-if="errorNota" class="error-msg-modal">{{ errorNota }}</p>

        <div class="acciones-modal">
          <button class="btn-secundario-modal" @click="cerrarModalNota">Cancelar</button>
          <button class="btn-primario-modal" :disabled="guardandoNota" @click="guardarNotaYCompletar">
            {{ guardandoNota ? 'Guardando...' : 'Guardar y completar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}
.sidebar-izquierdo {
  width: 260px;
  background-color: #ffffff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  box-sizing: border-box;
  flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center;
  justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; letter-spacing: -0.5px; }
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
  flex-grow: 1; padding: 24px; background-color: #f8fafc; box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.cabecera-medico {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;
}
.saludo-principal { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
.fecha-cabecera {
  font-size: 14px; color: #64748b; background: white;
  padding: 6px 12px; border-radius: 20px; border: 1px solid #e2e8f0;
}

.layout-agenda { display: grid; grid-template-columns: 300px 1fr; gap: 20px; align-items: start; }

/* --- Calendario --- */
.tarjeta-calendario, .tarjeta-proximas, .tarjeta-citas-dia {
  background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.tarjeta-proximas { margin-top: 16px; }
.columna-izquierda { position: sticky; top: 24px; }

.nav-mes {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;
}
.titulo-mes { font-size: 15px; font-weight: 700; color: #1e293b; }
.btn-nav-mes {
  background: none; border: 1px solid #e2e8f0; border-radius: 6px;
  width: 28px; height: 28px; cursor: pointer; font-size: 16px;
  color: #64748b; display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-nav-mes:hover { background: #f1f5f9; }

.grilla-semana { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 6px; }
.encabezado-dia { text-align: center; font-size: 11px; font-weight: 600; color: #94a3b8; padding: 4px 0; }
.grilla-dias { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.celda-dia {
  position: relative; aspect-ratio: 1; display: flex; flex-direction: column;
  align-items: center; justify-content: center; border-radius: 8px; cursor: pointer;
  font-size: 13px; color: #1e293b; font-weight: 500; transition: all 0.15s;
}
.celda-dia:hover:not(.celda-vacia) { background: #f1f5f9; }
.celda-vacia { cursor: default; }
.celda-hoy { background: #f0fdfa; color: #0d8a72; font-weight: 700; }
.celda-seleccionada { background: #0d8a72 !important; color: white !important; }
.celda-seleccionada .punto-cita { background: white !important; }
.punto-cita {
  width: 5px; height: 5px; border-radius: 50%;
  background: #0d8a72; position: absolute; bottom: 4px;
}
.celda-hoy .punto-cita { background: #0d8a72; }

/* Próximas */
.subtitulo-mini { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 12px 0; }
.sin-proximas { font-size: 13px; color: #94a3b8; text-align: center; padding: 12px 0; }
.fila-proxima { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px; }
.punto-proxima {
  width: 8px; height: 8px; background: #0d8a72; border-radius: 50; flex-shrink: 0; margin-top: 5px;
}
.proxima-paciente { font-size: 13px; font-weight: 600; color: #1e293b; margin: 0; }
.proxima-fecha { font-size: 12px; color: #64748b; margin: 2px 0 0 0; }

/* Citas del día */
.encabezado-citas { margin-bottom: 16px; }
.titulo-dia { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0; text-transform: capitalize; }
.conteo-citas { font-size: 13px; color: #64748b; margin: 0; }

.barra-filtros { display: flex; gap: 10px; margin-bottom: 16px; }
.input-busqueda {
  flex-grow: 1; padding: 9px 14px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; color: #1e293b; outline: none; font-family: inherit; background: #f8fafc;
}
.input-busqueda:focus { border-color: #0d8a72; background: white; }
.select-estado {
  padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 13px; color: #1e293b; outline: none; background: #f8fafc;
  cursor: pointer; font-family: inherit;
}

.lista-citas { display: flex; flex-direction: column; gap: 12px; }
.tarjeta-cita {
  display: flex; gap: 16px; align-items: flex-start; padding: 16px;
  border: 1px solid #f1f5f9; border-radius: 10px; transition: border-color 0.2s;
}
.tarjeta-cita:hover { border-color: #cbd5e1; }
.cita-hora-col { flex-shrink: 0; text-align: center; min-width: 56px; }
.hora-grande { font-size: 16px; font-weight: 700; color: #0d8a72; }
.cita-info-col { flex-grow: 1; }
.nombre-paciente-cita { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0; }
.motivo-cita { font-size: 13px; color: #64748b; margin: 0 0 8px 0; }
.badge-estado {
  display: inline-block; padding: 3px 10px; border-radius: 20px;
  font-size: 12px; font-weight: 600;
}
.estado-pendiente { background: #fef9c3; color: #854d0e; }
.estado-completada { background: #d1fae5; color: #065f46; }
.estado-cancelada { background: #fee2e2; color: #991b1b; }

.cita-acciones-col { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
.btn-accion {
  padding: 6px 12px; border-radius: 6px; font-size: 12px;
  font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; white-space: nowrap;
}
.btn-completar { background: #d1fae5; color: #065f46; }
.btn-completar:hover { background: #a7f3d0; }
.btn-cancelar-cita { background: #fee2e2; color: #991b1b; }
.btn-cancelar-cita:hover { background: #fecaca; }
.btn-reactivar { background: #f1f5f9; color: #475569; }
.btn-reactivar:hover { background: #e2e8f0; }

.contenedor-sin-citas {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 60px 20px; text-align: center;
}
.icono-vacio { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }
.texto-vacio { font-size: 16px; font-weight: 600; color: #475569; margin: 0 0 4px 0; }
.subtexto-vacio { font-size: 13px; color: #94a3b8; margin: 0; }

@media (max-width: 900px) {
  .layout-agenda { grid-template-columns: 1fr; }
  .columna-izquierda { position: static; }
}
@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
  .barra-filtros { flex-direction: column; }
}

.overlay-modal {
  position: fixed; inset: 0; background: rgba(15,23,42,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem;
}
.tarjeta-modal {
  background: white; border-radius: 12px; padding: 24px; width: 100%; max-width: 440px;
}
.titulo-modal { font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0; }
.subtitulo-modal { font-size: 13px; color: #64748b; margin: 0 0 16px 0; }
.form-group-modal { margin-bottom: 14px; }
.form-group-modal label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-group-modal textarea {
  width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; font-family: inherit; resize: vertical; box-sizing: border-box;
}
.error-msg-modal { color: #b91c1c; font-size: 13px; margin: 0 0 10px 0; }
.acciones-modal { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
.btn-secundario-modal {
  padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: white;
  color: #475569; font-weight: 600; font-size: 13px; cursor: pointer;
}
.btn-primario-modal {
  padding: 8px 16px; border-radius: 8px; border: none; background: #0d8a72;
  color: white; font-weight: 600; font-size: 13px; cursor: pointer;
}
.btn-primario-modal:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
