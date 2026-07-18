<template>
  <div class="recep-view">
    <div class="page-header">
      <div>
        <h2>Recepcionistas</h2>
        <p class="subtitle">Administra el personal de recepción</p>
      </div>
      <button class="btn-primary" @click="abrirNuevo">+ Nueva Recepcionista</button>
    </div>

    <div class="card">
      <table class="tabla">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre completo</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Edad</th>
            <th>Estatus</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in recepcionistas" :key="r.astId">
            <td class="id-col">{{ r.astId }}</td>
            <td class="nombre-col">{{ r.astNombre }} {{ r.astApePat }} {{ r.astApeMat }}</td>
            <td>{{ r.astCorreo }}</td>
            <td>{{ r.astTelefono }}</td>
            <td>{{ r.astEdad }}</td>
            <td><span class="chip" :class="r.astEstatus ? 'activo' : 'inactivo'">{{ r.astEstatus ? 'Activo' : 'Inactivo' }}</span></td>
            <td class="acciones">
              <button class="icon-btn" @click="abrirEditar(r)" title="Editar">✏️</button>
              <button class="icon-btn delete" @click="desactivar(r)" title="Desactivar">🚫</button>
            </td>
          </tr>
          <tr v-if="recepcionistas.length === 0">
            <td colspan="7" class="empty">No hay recepcionistas registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="mostrarModal" class="capa-modal" @click.self="cerrarModal">
      <div class="ventana-modal">
        <h3>{{ editando ? 'Editar Recepcionista' : 'Nueva Recepcionista' }}</h3>
        <div class="form-body">
          <div class="form-row">
            <div class="form-group"><label>Nombre</label><input v-model="form.recNombre" type="text" /></div>
            <div class="form-group"><label>Apellido Paterno</label><input v-model="form.recApePat" type="text" /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Apellido Materno</label><input v-model="form.recApeMat" type="text" /></div>
            <div class="form-group"><label>Edad</label><input v-model.number="form.recEdad" type="number" /></div>
          </div>
          <div class="form-group"><label>Correo</label><input v-model="form.recCorreo" type="email" /></div>
          <div class="form-group"><label>Teléfono</label><input v-model="form.recTelefono" type="text" /></div>
        </div>
        <div class="modal-botones">
          <button class="btn-secondary" @click="cerrarModal">Cancelar</button>
          <button class="btn-primary" @click="guardar">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const recepcionistas = ref([])
const mostrarModal = ref(false)
const editando = ref(false)
const editandoId = ref(null)
const form = ref({ recNombre: '', recApePat: '', recApeMat: '', recEdad: '', recCorreo: '', recTelefono: '' })

const cargar = async () => {
  const res = await fetch(`${BASE_URL}/recepcionistas`, { headers: getHeaders() })
  if (res.ok) recepcionistas.value = await res.json()
}

onMounted(cargar)

const abrirNuevo = () => {
  editando.value = false
  editandoId.value = null
  form.value = { recNombre: '', recApePat: '', recApeMat: '', recEdad: '', recCorreo: '', recTelefono: '' }
  mostrarModal.value = true
}

const abrirEditar = (r) => {
  editando.value = true
  editandoId.value = r.astId
  form.value = {
    recNombre: r.astNombre, recApePat: r.astApePat, recApeMat: r.astApeMat,
    recEdad: r.astEdad, recCorreo: r.astCorreo, recTelefono: r.astTelefono
  }
  mostrarModal.value = true
}

const cerrarModal = () => { mostrarModal.value = false }

const guardar = async () => {
  const url = editando.value ? `${BASE_URL}/recepcionistas/${editandoId.value}` : `${BASE_URL}/recepcionistas`
  const res = await fetch(url, {
    method: editando.value ? 'PUT' : 'POST',
    headers: getHeaders(),
    body: JSON.stringify(form.value)
  })
  if (res.ok) {
    await cargar()
    cerrarModal()
  } else {
    const err = await res.json()
    alert(err.message ?? 'Error al guardar.')
  }
}

const desactivar = async (r) => {
  if (!confirm(`¿Desactivar a ${r.astNombre}?`)) return
  const res = await fetch(`${BASE_URL}/recepcionistas/${r.astId}`, { method: 'DELETE', headers: getHeaders() })
  if (res.ok) await cargar()
}
</script>

<style scoped>
.recep-view { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }
.btn-primary { background: #0d8a72; color: white; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }
.btn-primary:hover { background: #0a6c59; }
.btn-secondary { background: #f1f5f9; color: #334155; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }

.card { background: white; border-radius: 14px; padding: 1.2rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); overflow-x: auto; }
.tabla { width: 100%; border-collapse: collapse; font-size: 0.87rem; }
.tabla th { text-align: left; color: #94a3b8; font-weight: 600; font-size: 0.75rem; padding: 0 0.75rem 0.7rem; border-bottom: 1px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
.tabla td { padding: 0.7rem 0.75rem; color: #334155; border-bottom: 1px solid #f8fafc; }
.tabla tbody tr:hover { background: #f8fafc; }
.id-col { color: #94a3b8; font-size: 0.8rem; }
.nombre-col { font-weight: 500; }
.empty { text-align: center; color: #94a3b8; padding: 2rem; }
.acciones { display: flex; gap: 0.4rem; }
.icon-btn { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.2rem 0.4rem; border-radius: 6px; }
.icon-btn:hover { background: #f1f5f9; }
.chip { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.chip.activo { background: #e6f4f1; color: #0d8a72; }
.chip.inactivo { background: #f1f5f9; color: #64748b; }

.capa-modal { position: fixed; inset: 0; background: rgba(15,23,42,0.4); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.ventana-modal { background: white; border-radius: 16px; width: 100%; max-width: 420px; padding: 24px; }
.ventana-modal h3 { margin: 0 0 16px; font-size: 1.1rem; color: #1e293b; }
.form-body { display: flex; flex-direction: column; gap: 12px; }
.form-row { display: flex; gap: 12px; }
.form-row .form-group { flex: 1; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group label { font-size: 0.78rem; color: #64748b; font-weight: 600; }
.form-group input { padding: 0.6rem 0.8rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; }
.modal-botones { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
</style>
