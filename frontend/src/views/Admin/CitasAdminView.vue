<template>
  <div class="citas-view">
    <div class="page-header">
      <div>
        <h2>Gestión de Citas</h2>
        <p class="subtitle">Administra todas las citas del sistema</p>
      </div>
      <button class="btn-primary" @click="abrirModal()">+ Nueva cita</button>
    </div>

    <!-- Filtros -->
    <div class="filtros-bar">
      <input v-model="busqueda" type="text" placeholder="Buscar paciente o médico..." class="input-search" />
      <select v-model="filtroEstado" class="select-filtro">
        <option value="">Todos los estados</option>
        <option value="confirmada">Confirmada</option>
        <option value="pendiente">Pendiente</option>
        <option value="cancelada">Cancelada</option>
        <option value="completada">Completada</option>
      </select>
      <input v-model="filtroFecha" type="date" class="select-filtro" />
    </div>

    <!-- Tabla -->
    <div class="card">
      <table class="tabla">
        <thead>
          <tr>
            <th>#</th>
            <th>Paciente</th>
            <th>Médico</th>
            <th>Especialidad</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cita in citasFiltradas" :key="cita.id">
            <td class="id-col">{{ cita.id }}</td>
            <td>{{ cita.paciente }}</td>
            <td>{{ cita.medico }}</td>
            <td>{{ cita.especialidad }}</td>
            <td>{{ cita.fecha }}</td>
            <td>{{ cita.hora }}</td>
            <td><span class="chip" :class="cita.estado">{{ cita.estadoLabel }}</span></td>
            <td class="acciones">
              <button class="icon-btn edit" @click="abrirModal(cita)" title="Editar">✏️</button>
              <button class="icon-btn delete" @click="eliminarCita(cita.id)" title="Cancelar">🗑️</button>
            </td>
          </tr>
          <tr v-if="citasFiltradas.length === 0">
            <td colspan="8" class="empty">No se encontraron citas con ese filtro.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal nueva/editar cita -->
    <div v-if="modalAbierto" class="modal-overlay" @click.self="modalAbierto = false">
      <div class="modal">
        <h3>{{ citaEditando ? 'Editar cita' : 'Nueva cita' }}</h3>
        <div class="form-group">
          <input type="text" v-model="form.paciente" placeholder="Nombre del paciente" />
        </div>
        <div class="form-group">
          <input type="text" v-model="form.medico" placeholder="Médico" />
        </div>
        <div class="form-group">
          <input type="text" v-model="form.especialidad" placeholder="Especialidad" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <input type="date" v-model="form.fecha" />
          </div>
          <div class="form-group">
            <input type="time" v-model="form.hora" />
          </div>
        </div>
        <div class="form-group">
          <select v-model="form.estado">
            <option value="confirmada">Confirmada</option>
            <option value="pendiente">Pendiente</option>
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
import { ref, computed } from 'vue'

const busqueda    = ref('')
const filtroEstado = ref('')
const filtroFecha  = ref('')
const modalAbierto = ref(false)
const citaEditando = ref(null)

const form = ref({ paciente: '', medico: '', especialidad: '', fecha: '', hora: '', estado: 'pendiente' })

const labelEstado = { confirmada: 'Confirmada', pendiente: 'Pendiente', cancelada: 'Cancelada', completada: 'Completada' }

const citas = ref([
  { id: 1, paciente: 'Ana Martínez',   medico: 'Dr. López',    especialidad: 'Cardiología',      fecha: '2025-06-25', hora: '09:00', estado: 'confirmada',  estadoLabel: 'Confirmada'  },
  { id: 2, paciente: 'Luis Hernández', medico: 'Dra. Ramírez', especialidad: 'Pediatría',         fecha: '2025-06-25', hora: '10:30', estado: 'pendiente',   estadoLabel: 'Pendiente'   },
  { id: 3, paciente: 'Sofía Torres',   medico: 'Dr. Gómez',    especialidad: 'Medicina General', fecha: '2025-06-25', hora: '11:00', estado: 'confirmada',  estadoLabel: 'Confirmada'  },
  { id: 4, paciente: 'Carlos Mendoza', medico: 'Dr. López',    especialidad: 'Cardiología',      fecha: '2025-06-24', hora: '12:00', estado: 'cancelada',   estadoLabel: 'Cancelada'   },
  { id: 5, paciente: 'María Sánchez',  medico: 'Dra. Ramírez', especialidad: 'Pediatría',         fecha: '2025-06-26', hora: '13:30', estado: 'completada',  estadoLabel: 'Completada'  },
])

const citasFiltradas = computed(() => {
  return citas.value.filter(c => {
    const q = busqueda.value.toLowerCase()
    const matchQ = !q || c.paciente.toLowerCase().includes(q) || c.medico.toLowerCase().includes(q)
    const matchE = !filtroEstado.value || c.estado === filtroEstado.value
    const matchF = !filtroFecha.value || c.fecha === filtroFecha.value
    return matchQ && matchE && matchF
  })
})

const abrirModal = (cita = null) => {
  citaEditando.value = cita
  form.value = cita
    ? { ...cita }
    : { paciente: '', medico: '', especialidad: '', fecha: '', hora: '', estado: 'pendiente' }
  modalAbierto.value = true
}

const guardarCita = () => {
  if (citaEditando.value) {
    const idx = citas.value.findIndex(c => c.id === citaEditando.value.id)
    citas.value[idx] = { ...form.value, estadoLabel: labelEstado[form.value.estado] }
  } else {
    const newId = Math.max(...citas.value.map(c => c.id)) + 1
    citas.value.push({ ...form.value, id: newId, estadoLabel: labelEstado[form.value.estado] })
  }
  modalAbierto.value = false
}

const eliminarCita = (id) => {
  if (confirm('¿Cancelar esta cita?')) {
    const idx = citas.value.findIndex(c => c.id === id)
    citas.value[idx].estado = 'cancelada'
    citas.value[idx].estadoLabel = 'Cancelada'
  }
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
