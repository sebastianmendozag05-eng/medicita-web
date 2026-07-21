<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const busqueda = ref('')
const filtroEstado = ref('todos')
const filtroMedico = ref('todos')
const mostrarModalNueva = ref(false)
const medicos = ref([])
const pacientes = ref([])
const citas = ref([])

const nuevaCita = ref({ pacId: '', medId: '', fecha: '', hora: '', motivo: '' })
const especialidades = ref([])
const especialidadNuevaCita = ref('')

onMounted(async () => {
  const [resCitas, resMedicos, resPacientes, resEspecialidades] = await Promise.all([
    fetch(`${BASE_URL}/citas`, { headers: getHeaders() }),
    fetch(`${BASE_URL}/medicos`, { headers: getHeaders() }),
    fetch(`${BASE_URL}/pacientes`, { headers: getHeaders() }),
    fetch(`${BASE_URL}/especialidades`)
  ])
  if (resCitas.ok) citas.value = await resCitas.json()
  if (resMedicos.ok) medicos.value = (await resMedicos.json()).filter(m => m.medEstatus === 1)
  if (resPacientes.ok) pacientes.value = await resPacientes.json()
  if (resEspecialidades.ok) especialidades.value = await resEspecialidades.json()
})

const medicosParaNuevaCita = computed(() => {
  if (!especialidadNuevaCita.value) return medicos.value
  return medicos.value.filter(m =>
    (m.especialidades ?? []).some(e => e.espeId === parseInt(especialidadNuevaCita.value))
  )
})

const citasFiltradas = computed(() => {
  return citas.value.filter(c => {
    const q = busqueda.value.toLowerCase()
    const paciente = `${c.paciente?.pacNombre ?? ''} ${c.paciente?.pacApePat ?? ''}`.toLowerCase()
    const medico = `${c.medico?.medNombre ?? ''} ${c.medico?.medApePat ?? ''}`.toLowerCase()
    const coincideQ = !q || paciente.includes(q) || medico.includes(q) || (c.citMotivo ?? '').toLowerCase().includes(q)
    const coincideEstado = filtroEstado.value === 'todos' || c.citEstatus === filtroEstado.value
    const coincideMedico = filtroMedico.value === 'todos' || c.medId == filtroMedico.value
    return coincideQ && coincideEstado && coincideMedico
  })
})

const cambiarEstado = async (cita, estado) => {
  const res = await fetch(`${BASE_URL}/citas/${cita.citId}`, {
    method: 'PUT', headers: getHeaders(),
    body: JSON.stringify({ citEstatus: estado })
  })
  if (res.ok) cita.citEstatus = estado
}

const guardarNuevaCita = async () => {
  if (!nuevaCita.value.pacId || !nuevaCita.value.medId || !nuevaCita.value.fecha || !nuevaCita.value.hora) return
  const res = await fetch(`${BASE_URL}/citas`, {
    method: 'POST', headers: getHeaders(),
    body: JSON.stringify({
      medId: parseInt(nuevaCita.value.medId),
      pacId: parseInt(nuevaCita.value.pacId),
      citFecha: nuevaCita.value.fecha,
      citHora: nuevaCita.value.hora + ':00',
      citMotivo: nuevaCita.value.motivo || 'Consulta general'
    })
  })
  if (res.ok) {
    const nueva = await res.json()
    citas.value.push(nueva)
    mostrarModalNueva.value = false
    nuevaCita.value = { pacId: '', medId: '', fecha: '', hora: '', motivo: '' }
  }
}

const cancelarModal = () => {
  nuevaCita.value = { pacId: '', medId: '', fecha: '', hora: '', motivo: '' }
  mostrarModalNueva.value = false
}

const formatearFecha = (f) => {
  if (!f) return ''
  return new Date(f).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })
}

const colorEstado = (e) => ({ agendada: 'badge-pendiente', confirmada: 'badge-espera', completada: 'badge-completada', cancelada: 'badge-cancelada' })[e] || 'badge-pendiente'
const labelEstado = (e) => ({ agendada: '· Agendada', confirmada: '⏳ Confirmada', completada: '✔ Completada', cancelada: '✗ Cancelada' })[e] || e

const cerrarSesion = () => { localStorage.clear(); router.push('/login') }
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand"><span class="logo-icon">+</span><h1>MediCita</h1></div>
      <nav class="menu-navegacion">
        <router-link to="/recepcionista/inicio"    class="enlace-menu"><span>🏠</span> Inicio</router-link>
        <router-link to="/recepcionista/citas"     class="enlace-menu activo"><span>📋</span> Citas</router-link>
        <router-link to="/recepcionista/pacientes" class="enlace-menu"><span>👥</span> Pacientes</router-link>
        <router-link to="/recepcionista/checkin"   class="enlace-menu"><span>✅</span> Check-in</router-link>
        <router-link to="/recepcionista/reportes"  class="enlace-menu"><span>📊</span> Reportes</router-link>
        <router-link to="/recepcionista/notificaciones"  class="enlace-menu"><span>🔔</span> Notificaciones</router-link>
        <router-link to="/recepcionista/perfil"  class="enlace-menu"><span>👤</span> Mi Perfil</router-link>
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera">
        <h1 class="saludo-principal">📋 Gestión de Citas</h1>
        <button @click="mostrarModalNueva = true" class="btn-nueva-cita">+ Nueva cita</button>
      </div>

      <!-- Filtros -->
      <div class="barra-filtros">
        <input v-model="busqueda" type="text" class="input-busqueda" placeholder="🔍 Buscar paciente, médico o motivo..." />
        <select v-model="filtroEstado" class="select-filtro">
          <option value="todos">Todos los estados</option>
          <option value="agendada">Agendada</option>
          <option value="confirmada">Confirmada</option>
          <option value="completada">Completada</option>
          <option value="cancelada">Cancelada</option>
        </select>
        <select v-model="filtroMedico" class="select-filtro">
          <option value="todos">Todos los médicos</option>
          <option v-for="m in medicos" :key="m.medId" :value="m.medId">{{ m.medNombre }} {{ m.medApePat }}</option>
        </select>
      </div>

      <!-- Contador -->
      <p class="conteo-resultados">{{ citasFiltradas.length }} cita(s) encontrada(s)</p>

      <!-- Tabla -->
      <div class="tarjeta-tabla">
        <div class="fila-encabezado">
          <span>Fecha</span><span>Hora</span><span>Paciente</span>
          <span>Médico</span><span>Motivo</span><span>Estado</span><span>Acciones</span>
        </div>

        <div v-for="cita in citasFiltradas" :key="cita.citId" class="fila-cita">
          <span class="celda-fecha">{{ formatearFecha(cita.citFecha) }}</span>
          <span class="celda-hora">{{ cita.citHora?.substring(0,5) }}</span>
          <span class="celda-paciente">{{ cita.paciente?.pacNombre }} {{ cita.paciente?.pacApePat }}</span>
          <span class="celda-medico">Dr. {{ cita.medico?.medNombre }} {{ cita.medico?.medApePat }}</span>
          <span class="celda-motivo">{{ cita.citMotivo }}</span>
          <span :class="['badge-estado', colorEstado(cita.citEstatus)]">{{ labelEstado(cita.citEstatus) }}</span>
          <div class="celda-acciones">
            <button v-if="cita.citEstatus === 'agendada'" @click="cambiarEstado(cita, 'confirmada')" class="btn-mini btn-espera">⏳</button>
            <button v-if="cita.citEstatus !== 'completada' && cita.citEstatus !== 'cancelada'" @click="cambiarEstado(cita, 'completada')" class="btn-mini btn-ok">✓</button>
            <button v-if="cita.citEstatus !== 'cancelada'" @click="cambiarEstado(cita, 'cancelada')" class="btn-mini btn-cancel">✕</button>
            <button v-if="cita.citEstatus === 'cancelada'" @click="cambiarEstado(cita, 'agendada')" class="btn-mini btn-reactivar">↩</button>
          </div>
        </div>

        <div v-if="citasFiltradas.length === 0" class="estado-vacio">
          <p>📋 No se encontraron citas con los filtros aplicados</p>
        </div>
      </div>
    </div>

    <!-- Modal nueva cita -->
    <div v-if="mostrarModalNueva" class="overlay-modal" @click.self="cancelarModal">
      <div class="modal">
        <h2 class="titulo-modal">Nueva Cita</h2>
        <div class="grilla-modal">
          <div class="campo-modal">
            <label>Paciente *</label>
            <select v-model="nuevaCita.pacId" class="input-modal">
              <option value="">Seleccionar paciente</option>
              <option v-for="p in pacientes" :key="p.pacId" :value="p.pacId">{{ p.pacNombre }} {{ p.pacApePat }}</option>
            </select>
          </div>
          <div class="campo-modal">
            <label>Especialidad</label>
            <select v-model="especialidadNuevaCita" class="input-modal">
              <option value="">Todas las especialidades</option>
              <option v-for="e in especialidades" :key="e.espeId" :value="e.espeId">{{ e.espeNombre }}</option>
            </select>
          </div>
          <div class="campo-modal">
            <label>Médico *</label>
            <select v-model="nuevaCita.medId" class="input-modal">
              <option value="">Seleccionar médico</option>
              <option v-for="m in medicosParaNuevaCita" :key="m.medId" :value="m.medId">{{ m.medNombre }} {{ m.medApePat }}</option>
            </select>
          </div>
          <div class="campo-modal">
            <label>Fecha *</label>
            <input v-model="nuevaCita.fecha" type="date" class="input-modal" />
          </div>
          <div class="campo-modal">
            <label>Hora *</label>
            <input v-model="nuevaCita.hora" type="time" class="input-modal" />
          </div>
          <div class="campo-modal campo-ancho">
            <label>Motivo de consulta</label>
            <input v-model="nuevaCita.motivo" type="text" class="input-modal" placeholder="Ej. Consulta general" />
          </div>
        </div>
        <div class="botones-modal">
          <button @click="cancelarModal" class="btn-modal-cancelar">Cancelar</button>
          <button @click="guardarNuevaCita" class="btn-modal-guardar">Guardar cita</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background: #f8fafc; }
.sidebar-izquierdo {
  width: 240px; background: #fff; border-right: 1px solid #e2e8f0;
  display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; }
.menu-navegacion { display: flex; flex-direction: column; gap: 0.4rem; flex-grow: 1; }
.enlace-menu {
  display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem;
  color: #64748b; text-decoration: none; font-weight: 600; border-radius: 10px; transition: all 0.2s; font-size: 0.92rem;
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
  margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;
}
.saludo-principal { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0; }
.btn-nueva-cita {
  background: #0d8a72; color: white; border: none; padding: 9px 18px;
  border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: background 0.2s;
}
.btn-nueva-cita:hover { background: #0a7060; }
.barra-filtros { display: flex; gap: 10px; margin-bottom: 12px; }
.input-busqueda {
  flex-grow: 1; padding: 9px 14px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; outline: none; font-family: inherit; background: #f8fafc;
}
.input-busqueda:focus { border-color: #0d8a72; background: white; }
.select-filtro {
  padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 13px; outline: none; background: #f8fafc; cursor: pointer; font-family: inherit;
}
.conteo-resultados { font-size: 13px; color: #64748b; margin: 0 0 12px; }
.tarjeta-tabla {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.fila-encabezado {
  display: grid; grid-template-columns: 70px 60px 1fr 1fr 1fr 120px 100px;
  padding: 10px 16px; font-size: 11px; font-weight: 700; color: #94a3b8;
  text-transform: uppercase; letter-spacing: 0.5px; background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.fila-cita {
  display: grid; grid-template-columns: 70px 60px 1fr 1fr 1fr 120px 100px;
  padding: 13px 16px; border-bottom: 1px solid #f8fafc;
  align-items: center; transition: background 0.15s; font-size: 13px;
}
.fila-cita:hover { background: #f8fafc; }
.celda-fecha { color: #64748b; font-weight: 600; }
.celda-hora { color: #0d8a72; font-weight: 700; }
.celda-paciente { font-weight: 600; color: #1e293b; }
.celda-medico { color: #475569; }
.celda-motivo { color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.badge-estado {
  display: inline-flex; align-items: center; padding: 3px 8px;
  border-radius: 20px; font-size: 11px; font-weight: 600; width: fit-content;
}
.badge-completada { background: #d1fae5; color: #065f46; }
.badge-espera     { background: #fef9c3; color: #854d0e; }
.badge-pendiente  { background: #f1f5f9; color: #475569; }
.badge-cancelada  { background: #fee2e2; color: #991b1b; }
.celda-acciones { display: flex; gap: 4px; }
.btn-mini {
  width: 28px; height: 28px; border-radius: 6px; border: none;
  font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;
}
.btn-espera    { background: #fef9c3; color: #854d0e; }
.btn-espera:hover { background: #fde68a; }
.btn-ok        { background: #d1fae5; color: #065f46; }
.btn-ok:hover  { background: #a7f3d0; }
.btn-cancel    { background: #fee2e2; color: #991b1b; }
.btn-cancel:hover { background: #fecaca; }
.btn-reactivar { background: #f1f5f9; color: #475569; }
.btn-reactivar:hover { background: #e2e8f0; }
.estado-vacio { text-align: center; padding: 48px; color: #94a3b8; font-size: 15px; }

/* Modal */
.overlay-modal {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: white; border-radius: 16px; padding: 28px; width: 500px;
  max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.titulo-modal { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 20px; }
.grilla-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
.campo-modal { display: flex; flex-direction: column; gap: 5px; }
.campo-ancho { grid-column: 1 / -1; }
.campo-modal label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
.input-modal {
  padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; outline: none; font-family: inherit; background: #f8fafc;
}
.input-modal:focus { border-color: #0d8a72; background: white; }
.botones-modal { display: flex; justify-content: flex-end; gap: 10px; }
.btn-modal-cancelar {
  padding: 9px 18px; border: 1px solid #e2e8f0; border-radius: 8px;
  background: #f1f5f9; color: #64748b; font-weight: 600; font-size: 14px; cursor: pointer;
}
.btn-modal-guardar {
  padding: 9px 18px; border: none; border-radius: 8px;
  background: #0d8a72; color: white; font-weight: 700; font-size: 14px; cursor: pointer;
}
.btn-modal-guardar:hover { background: #0a7060; }

@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
  .barra-filtros { flex-wrap: wrap; }
  .fila-encabezado, .fila-cita { grid-template-columns: 1fr; }
}
</style>