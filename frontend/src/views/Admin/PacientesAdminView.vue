<template>
  <div class="pacientes-view">
    <div class="page-header">
      <div>
        <h2>Pacientes / Expedientes</h2>
        <p class="subtitle">Consulta y administra los expedientes del sistema</p>
      </div>
    </div>

    <!-- Buscador -->
    <div class="filtros-bar">
      <input v-model="busqueda" type="text" placeholder="Buscar por nombre, NSS o correo..." class="input-search" />
      <select v-model="filtroSexo" class="select-filtro">
        <option value="">Todos</option>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
        <option value="Otro">Otro</option>
      </select>
    </div>

    <!-- Tabla -->
    <div class="card" v-if="!pacienteSeleccionado">
      <table class="tabla">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre completo</th>
            <th>NSS</th>
            <th>Correo</th>
            <th>Sexo</th>
            <th>Fecha Nac.</th>
            <th>Teléfono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in pacientesFiltrados" :key="p.id">
            <td class="id-col">{{ p.id }}</td>
            <td class="nombre-col">{{ p.nombre }} {{ p.apePat }} {{ p.apeMat }}</td>
            <td>{{ p.nss }}</td>
            <td>{{ p.correo }}</td>
            <td><span class="chip-sexo" :class="p.sexo.toLowerCase()">{{ p.sexo }}</span></td>
            <td>{{ p.fechaNac }}</td>
            <td>{{ p.telefono }}</td>
            <td class="acciones">
              <button class="icon-btn" @click="verExpediente(p)" title="Ver expediente">📋</button>
            </td>
          </tr>
          <tr v-if="pacientesFiltrados.length === 0">
            <td colspan="8" class="empty">No se encontraron pacientes.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Expediente detalle -->
    <div v-if="pacienteSeleccionado" class="expediente">
      <button class="btn-back" @click="pacienteSeleccionado = null">← Volver a la lista</button>

      <div class="exp-header">
        <div class="exp-avatar">{{ iniciales(pacienteSeleccionado) }}</div>
        <div>
          <h3>{{ pacienteSeleccionado.nombre }} {{ pacienteSeleccionado.apePat }} {{ pacienteSeleccionado.apeMat }}</h3>
          <p>NSS: {{ pacienteSeleccionado.nss }} · {{ pacienteSeleccionado.correo }}</p>
        </div>
      </div>

      <div class="exp-grid">
        <div class="exp-card">
          <h4>Datos personales</h4>
          <div class="field"><span>Sexo</span><strong>{{ pacienteSeleccionado.sexo }}</strong></div>
          <div class="field"><span>Fecha nac.</span><strong>{{ pacienteSeleccionado.fechaNac }}</strong></div>
          <div class="field"><span>Teléfono</span><strong>{{ pacienteSeleccionado.telefono }}</strong></div>
          <div class="field"><span>Peso</span><strong>{{ pacienteSeleccionado.peso }} kg</strong></div>
          <div class="field"><span>Estatura</span><strong>{{ pacienteSeleccionado.estatura }} m</strong></div>
          <div class="field"><span>IMC</span><strong>{{ imc(pacienteSeleccionado) }}</strong></div>
        </div>

        <div class="exp-card">
          <h4>Historial de citas</h4>
          <ul class="historial-list">
            <li v-for="h in pacienteSeleccionado.historial" :key="h.id">
              <span class="hist-fecha">{{ h.fecha }}</span>
              <span class="hist-desc">{{ h.medico }} — {{ h.motivo }}</span>
              <span class="chip" :class="h.estado">{{ h.estadoLabel }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const busqueda = ref('')
const filtroSexo = ref('')
const pacienteSeleccionado = ref(null)

const pacientes = ref([
  {
    id: 1, nombre: 'Ana', apePat: 'Martínez', apeMat: 'López',
    nss: '12345678901', correo: 'ana@mail.com', sexo: 'Femenino',
    fechaNac: '1990-04-12', telefono: '4421234567', peso: 62, estatura: 1.65,
    historial: [
      { id: 1, fecha: '2025-06-10', medico: 'Dr. López',    motivo: 'Revisión general',  estado: 'completada', estadoLabel: 'Completada' },
      { id: 2, fecha: '2025-05-20', medico: 'Dra. Ramírez', motivo: 'Control pediatría', estado: 'completada', estadoLabel: 'Completada' },
    ]
  },
  {
    id: 2, nombre: 'Luis', apePat: 'Hernández', apeMat: 'Cruz',
    nss: '98765432100', correo: 'luis@mail.com', sexo: 'Masculino',
    fechaNac: '1985-11-30', telefono: '4429876543', peso: 80, estatura: 1.75,
    historial: [
      { id: 1, fecha: '2025-06-15', medico: 'Dr. Gómez', motivo: 'Dolor de espalda', estado: 'completada', estadoLabel: 'Completada' },
    ]
  },
  {
    id: 3, nombre: 'Sofía', apePat: 'Torres', apeMat: 'Ruiz',
    nss: '11122233344', correo: 'sofia@mail.com', sexo: 'Femenino',
    fechaNac: '2000-07-05', telefono: '4423344556', peso: 55, estatura: 1.60,
    historial: []
  },
])

const pacientesFiltrados = computed(() => {
  const q = busqueda.value.toLowerCase()
  return pacientes.value.filter(p => {
    const nombre = `${p.nombre} ${p.apePat} ${p.apeMat}`.toLowerCase()
    const matchQ = !q || nombre.includes(q) || p.nss.includes(q) || p.correo.toLowerCase().includes(q)
    const matchS = !filtroSexo.value || p.sexo === filtroSexo.value
    return matchQ && matchS
  })
})

const verExpediente = (p) => { pacienteSeleccionado.value = p }

const iniciales = (p) => `${p.nombre[0]}${p.apePat[0]}`

const imc = (p) => {
  const val = (p.peso / (p.estatura * p.estatura)).toFixed(1)
  return `${val}`
}
</script>

<style scoped>
.pacientes-view { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }

.filtros-bar { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.input-search { flex: 1; min-width: 200px; padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; }
.input-search:focus { border-color: #0d8a72; outline: none; }
.select-filtro { padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; background: white; }

.card { background: white; border-radius: 14px; padding: 1.2rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
.tabla th { text-align: left; color: #94a3b8; font-weight: 600; font-size: 0.75rem; padding: 0 0.75rem 0.7rem; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
.tabla td { padding: 0.7rem 0.75rem; color: #334155; border-bottom: 1px solid #f8fafc; }
.tabla tbody tr:hover { background: #f8fafc; }
.id-col { color: #94a3b8; font-size: 0.8rem; }
.nombre-col { font-weight: 500; }
.empty { text-align: center; color: #94a3b8; padding: 2rem; }
.acciones { display: flex; gap: 0.4rem; }
.icon-btn { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.2rem 0.4rem; border-radius: 6px; transition: background 0.15s; }
.icon-btn:hover { background: #f1f5f9; }

.chip-sexo { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.chip-sexo.femenino  { background: #fce7f3; color: #9d174d; }
.chip-sexo.masculino { background: #eff6ff; color: #1d4ed8; }
.chip-sexo.otro      { background: #f1f5f9; color: #475569; }

/* Expediente */
.btn-back { background: none; border: none; color: #0d8a72; font-size: 0.9rem; font-weight: 600; cursor: pointer; padding: 0; margin-bottom: 0.5rem; }
.btn-back:hover { text-decoration: underline; }

.exp-header { display: flex; align-items: center; gap: 1rem; background: white; border-radius: 14px; padding: 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.exp-avatar { width: 52px; height: 52px; background: #0d8a72; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 700; flex-shrink: 0; }
.exp-header h3 { margin: 0 0 0.2rem; font-size: 1.1rem; color: #1e293b; }
.exp-header p { margin: 0; font-size: 0.85rem; color: #64748b; }

.exp-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; }
.exp-card { background: white; border-radius: 14px; padding: 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.exp-card h4 { margin: 0 0 1rem; font-size: 0.95rem; color: #1e293b; font-weight: 700; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.6rem; }

.field { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.87rem; border-bottom: 1px solid #f8fafc; }
.field span { color: #64748b; }
.field strong { color: #1e293b; }

.historial-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.historial-list li { display: flex; align-items: center; gap: 0.75rem; font-size: 0.87rem; flex-wrap: wrap; }
.hist-fecha { color: #94a3b8; font-size: 0.78rem; white-space: nowrap; }
.hist-desc { flex: 1; color: #334155; }
.chip { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.chip.completada { background: #eff6ff; color: #2563eb; }
.chip.confirmada { background: #e6f4f1; color: #0d8a72; }
.chip.cancelada  { background: #fee2e2; color: #dc2626; }

@media (max-width: 800px) { .exp-grid { grid-template-columns: 1fr; } }
</style>
