<template>
  <div class="citas-view">
    <div class="page-header">
      <div>
        <h2>Gestión de Citas</h2>
        <p class="subtitle">Administra todas las citas del sistema</p>
      </div>
      <button class="btn-primary" @click="abrirModal()">+ Nueva cita</button>
    </div>

    <div class="filtros-bar">
      <input v-model="busqueda" type="text" placeholder="Buscar paciente o médico..." class="input-search" />
      <select v-model="filtroEstado" class="select-filtro">
        <option value="">Todos los estados</option>
        <option value="agendada">Agendada</option>
        <option value="confirmada">Confirmada</option>
        <option value="cancelada">Cancelada</option>
        <option value="completada">Completada</option>
      </select>
      <input v-model="filtroFecha" type="date" class="select-filtro" />
    </div>

    <div class="card">
      <table class="tabla">
        <thead>
          <tr>
            <th>#</th><th>Paciente</th><th>Médico</th><th>Motivo</th>
            <th>Fecha</th><th>Hora</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cita in citasFiltradas" :key="cita.citId">
            <td class="id-col">{{ cita.citId }}</td>
            <td>{{ cita.paciente?.pacNombre }} {{ cita.paciente?.pacApePat }}</td>
            <td>Dr. {{ cita.medico?.medNombre }}</td>
            <td>{{ cita.citMotivo }}</td>
            <td>{{ cita.citFecha?.split('T')[0] }}</td>
            <td>{{ cita.citHora?.substring(0,5) }}</td>
            <td><span class="chip" :class="cita.citEstatus">{{ cita.citEstatus }}</span></td>
            <td class="acciones">
              <button class="icon-btn edit" @click="abrirModal(cita)">✏️</button>
              <button class="icon-btn delete" @click="eliminarCita(cita.citId)">🗑️</button>
            </td>
          </tr>
          <tr v-if="citasFiltradas.length === 0">
            <td colspan="8" class="empty">No se encontraron citas.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="modalAbierto" class="modal-overlay" @click.self="modalAbierto = false">
      <div class="modal">
        <h3>{{ citaEditando ? 'Editar cita' : 'Nueva cita' }}</h3>
        <div class="form-group">
          <select v-model="form.pacId">
            <option value="">Seleccionar paciente</option>
            <option v-for="p in pacientes" :key="p.pacId" :value="p.pacId">{{ p.pacNombre }} {{ p.pacApePat }}</option>
          </select>
        </div>
        <div class="form-group">
          <select v-model="form.medId">
            <option value="">Seleccionar médico</option>
            <option v-for="m in medicos" :key="m.medId" :value="m.medId">Dr. {{ m.medNombre }} {{ m.medApePat }}</option>
          </select>
        </div>
        <div class="form-group">
          <input type="text" v-model="form.citMotivo" placeholder="Motivo de consulta" />
        </div>
        <div class="form-row">
          <div class="form-group"><input type="date" v-model="form.citFecha" /></div>
          <div class="form-group"><input type="time" v-model="form.citHora" /></div>
        </div>
        <div class="form-group">
          <select v-model="form.citEstatus">
            <option value="agendada">Agendada</option>
            <option value="confirmada">Confirmada</option>
            <option value="cancelada">Cancelada</option>
            <option value="completada">Completada</option>
          </select>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" @click="modalAbierto = false">Cancelar</button>
          <button class="btn-primary" @click="guardarCita">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const busqueda = ref('')
const filtroEstado = ref('')
const filtroFecha = ref('')
const modalAbierto = ref(false)
const citaEditando = ref(null)
const citas = ref([])
const medicos = ref([])
const pacientes = ref([])

const form = ref({ pacId: '', medId: '', citFecha: '', citHora: '', citMotivo: '', citEstatus: 'agendada' })

onMounted(async () => {
  const [resCitas, resMedicos, resPacientes] = await Promise.all([
    fetch(`${BASE_URL}/citas`, { headers: getHeaders() }),
    fetch(`${BASE_URL}/medicos`, { headers: getHeaders() }),
    fetch(`${BASE_URL}/pacientes`, { headers: getHeaders() })
  ])
  if (resCitas.ok) citas.value = await resCitas.json()
  if (resMedicos.ok) medicos.value = await resMedicos.json()
  if (resPacientes.ok) pacientes.value = await resPacientes.json()
})

const citasFiltradas = computed(() => citas.value.filter(c => {
  const q = busqueda.value.toLowerCase()
  const paciente = `${c.paciente?.pacNombre ?? ''} ${c.paciente?.pacApePat ?? ''}`.toLowerCase()
  const medico = `${c.medico?.medNombre ?? ''}`.toLowerCase()
  const matchQ = !q || paciente.includes(q) || medico.includes(q)
  const matchE = !filtroEstado.value || c.citEstatus === filtroEstado.value
  const matchF = !filtroFecha.value || c.citFecha?.startsWith(filtroFecha.value)
  return matchQ && matchE && matchF
}))

const abrirModal = (cita = null) => {
  citaEditando.value = cita
  form.value = cita
    ? { pacId: cita.pacId, medId: cita.medId, citFecha: cita.citFecha?.split('T')[0], citHora: cita.citHora?.substring(0,5), citMotivo: cita.citMotivo, citEstatus: cita.citEstatus }
    : { pacId: '', medId: '', citFecha: '', citHora: '', citMotivo: '', citEstatus: 'agendada' }
  modalAbierto.value = true
}

const guardarCita = async () => {
  const body = { medId: parseInt(form.value.medId), pacId: parseInt(form.value.pacId), citFecha: form.value.citFecha, citHora: form.value.citHora + ':00', citMotivo: form.value.citMotivo, citEstatus: form.value.citEstatus }
  if (citaEditando.value) {
    const res = await fetch(`${BASE_URL}/citas/${citaEditando.value.citId}`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify(body) })
    if (res.ok) { const idx = citas.value.findIndex(c => c.citId === citaEditando.value.citId); citas.value[idx] = await res.json() }
  } else {
    const res = await fetch(`${BASE_URL}/citas`, { method: 'POST', headers: getHeaders(), body: JSON.stringify(body) })
    if (res.ok) citas.value.push(await res.json())
  }
  modalAbierto.value = false
}

const eliminarCita = async (id) => {
  if (!confirm('¿Cancelar esta cita?')) return
  const res = await fetch(`${BASE_URL}/citas/${id}`, { method: 'DELETE', headers: getHeaders() })
  if (res.ok) { const idx = citas.value.findIndex(c => c.citId === id); citas.value[idx].citEstatus = 'cancelada' }
}
</script>

<style scoped>
.citas-view { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }

.filtros-bar { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.input-search { flex: 1; min-width: 200px; padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; }
.input-search:focus { border-color: #0d8a72; outline: none; }
.select-filtro { padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; background: white; }
.select-filtro:focus { border-color: #0d8a72; outline: none; }

.card { background: white; border-radius: 14px; padding: 1.2rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
.tabla th { text-align: left; color: #94a3b8; font-weight: 600; font-size: 0.75rem; padding: 0 0.75rem 0.7rem; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
.tabla td { padding: 0.7rem 0.75rem; color: #334155; border-bottom: 1px solid #f8fafc; }
.tabla tbody tr:hover { background: #f8fafc; }
.id-col { color: #94a3b8; font-size: 0.8rem; }
.empty { text-align: center; color: #94a3b8; padding: 2rem; }

.chip { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.chip.confirmada { background: #e6f4f1; color: #0d8a72; }
.chip.pendiente  { background: #fef9c3; color: #a16207; }
.chip.cancelada  { background: #fee2e2; color: #dc2626; }
.chip.completada { background: #eff6ff; color: #2563eb; }

.acciones { display: flex; gap: 0.4rem; }
.icon-btn { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.2rem 0.4rem; border-radius: 6px; transition: background 0.15s; }
.icon-btn:hover { background: #f1f5f9; }

.btn-primary { background: #0d8a72; color: white; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-primary:hover { background: #0a6c59; }
.btn-secondary { background: #f1f5f9; color: #334155; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; border-radius: 16px; padding: 2rem; width: 100%; max-width: 480px; display: flex; flex-direction: column; gap: 1rem; }
.modal h3 { margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700; }
.form-group input, .form-group select { width: 100%; padding: 0.65rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; box-sizing: border-box; }
.form-group input:focus, .form-group select:focus { border-color: #0d8a72; outline: none; }
.form-row { display: flex; gap: 0.75rem; }
.form-row .form-group { flex: 1; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }
</style>
