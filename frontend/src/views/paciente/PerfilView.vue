<template>
  <div class="perfil-layout">
    <div class="perfil-card">
      <h1 class="perfil-title">Mi Perfil</h1>
      
      <div class="perfil-header-container">
        <div class="profile-header">
          <div class="avatar-circle">
            {{ obtenerIniciales(usuario.nombre, usuario.apePat) }}
          </div>
          <div class="header-text">
            <h2>{{ usuario.nombre }} {{ usuario.apePat }}</h2>
            <span class="user-role">Paciente Asegurado</span>
            <span class="user-email">{{ usuario.correo || 'No registrado' }}</span>
            <span class="user-phone">{{ usuario.telefono || 'No registrado' }}</span>
          </div>
        </div>
      </div>

      <div class="informacion-personal">
        <h3>Información Personal</h3>
        <table class="info-table">
          <tbody>
            <tr>
              <td class="label">Nombre(s)</td>
              <td class="value">
                <input v-if="isEditing" type="text" v-model="editForm.nombre" class="edit-input" />
                <span v-else>{{ usuario.nombre || 'No registrado' }}</span>
              </td>
            </tr>
            <tr>
              <td class="label">Apellido Paterno</td>
              <td class="value">
                <input v-if="isEditing" type="text" v-model="editForm.apePat" class="edit-input" />
                <span v-else>{{ usuario.apePat || 'No registrado' }}</span>
              </td>
            </tr>
            <tr>
              <td class="label">Apellido Materno</td>
              <td class="value">
                <input v-if="isEditing" type="text" v-model="editForm.apeMat" class="edit-input" placeholder="Opcional" />
                <span v-else>{{ usuario.apeMat || 'No registrado' }}</span>
              </td>
            </tr>

            <tr>
              <td class="label">Teléfono de Contacto</td>
              <td class="value">
                <input v-if="isEditing" type="tel" v-model="editForm.telefono" class="edit-input" />
                <span v-else>{{ usuario.telefono || 'No registrado' }}</span>
              </td>
            </tr>

            <tr>
              <td class="label">NSS (Número de Seguro Social)</td>
              <td class="value">
                <input v-if="isEditing" type="text" v-model="editForm.nss" class="edit-input" />
                <span v-else>{{ usuario.nss || 'No registrado' }}</span>
              </td>
            </tr>

            <tr>
              <td class="label">Sexo</td>
              <td class="value">
                <select v-if="isEditing" v-model="editForm.sexo" class="edit-input select-input">
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                  <option value="Otro">Otro</option>
                </select>
                <span v-else>{{ usuario.sexo || 'No especificado' }}</span>
              </td>
            </tr>

            <tr>
              <td class="label">Fecha de nacimiento</td>
              <td class="value">
                <input v-if="isEditing" type="date" v-model="editForm.fechaNac" class="edit-input" />
                <span v-else>{{ formatearFecha(usuario.fechaNac) }}</span>
              </td>
            </tr>

            <tr>
              <td class="label">Peso (kg)</td>
              <td class="value">
                <div v-if="isEditing" class="input-unit">
                  <input type="number" step="0.1" v-model="editForm.peso" class="edit-input" />
                  <span class="unit-tag">kg</span>
                </div>
                <span v-else>{{ usuario.peso ? usuario.peso + ' kg' : 'No registrado' }}</span>
              </td>
            </tr>

            <tr>
              <td class="label">Estatura (m)</td>
              <td class="value">
                <div v-if="isEditing" class="input-unit">
                  <input type="number" step="0.01" v-model="editForm.estatura" class="edit-input" />
                  <span class="unit-tag">m</span>
                </div>
                <span v-else>{{ usuario.estatura ? usuario.estatura + ' m' : 'No registrado' }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="action-buttons">
        <button v-if="!isEditing" @click="habilitarEdicion" class="btn-editar">
          Editar Perfil
        </button>
        <div v-else class="edit-buttons-group">
          <button @click="cancelarEdicion" class="btn-cancelar">Cancelar</button>
          <button @click="guardarCambios" class="btn-guardar">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Estado del modo edición
const isEditing = ref(false)

// Estado del usuario en pantalla
const usuario = ref({
  nombre: '',
  apePat: '',
  apeMat: '',
  correo: '',
  sexo: '',
  fechaNac: '',
  nss: '',
  telefono: '',
  peso: '',
  estatura: ''
})

// Copia temporal para cuando estemos editando (evita alterar los datos si cancela)
const editForm = ref({ ...usuario.value })

const obtenerIniciales = (nombre, apellido) => {
  if (!nombre) return 'P'
  const iniNombre = nombre.trim().charAt(0).toUpperCase()
  const iniApellido = apellido ? apellido.trim().charAt(0).toUpperCase() : ''
  return iniNombre + iniApellido
}

const formatearFecha = (fechaRaw) => {
  if (!fechaRaw) return 'No registrada'
  const partes = fechaRaw.split('-')
  if (partes.length === 3) {
    return `${partes[2]}/${partes[1]}/${partes[0]}`
  }
  return fechaRaw
}

// Cargar datos desde el LocalStorage
const cargarDatosLocal = () => {
  usuario.value.nombre = localStorage.getItem('usuarioNombre') || ''
  usuario.value.apePat = localStorage.getItem('usuarioApePat') || ''
  usuario.value.apeMat = localStorage.getItem('usuarioApeMat') || ''
  usuario.value.correo = localStorage.getItem('usuarioCorreo') || ''
  usuario.value.telefono = localStorage.getItem('usuarioTelefono') || ''
  usuario.value.nss = localStorage.getItem('usuarioNss') || ''
  usuario.value.sexo = localStorage.getItem('usuarioSexo') || ''
  usuario.value.fechaNac = localStorage.getItem('usuarioFechaNac') || ''
  usuario.value.peso = localStorage.getItem('usuarioPeso') || ''
  usuario.value.estatura = localStorage.getItem('usuarioEstatura') || ''
}

onMounted(() => {
  cargarDatosLocal()
})

// Acciones del botón
const habilitarEdicion = () => {
  // Pasamos los datos actuales al formulario de edición
  editForm.value = { ...usuario.value }
  isEditing.value = true
}

const cancelarEdicion = () => {
  isEditing.value = false
}

const guardarCambios = () => {
  // 1. Actualizamos el estado reactivo principal en la vista
  usuario.value = { ...editForm.value }

  // 2. Seteamos los nuevos valores directo en el LocalStorage
  localStorage.setItem('usuarioNombre', usuario.value.nombre)
  localStorage.setItem('usuarioApePat', usuario.value.apePat)
  localStorage.setItem('usuarioApeMat', usuario.value.apeMat)
  localStorage.setItem('usuarioTelefono', usuario.value.telefono)
  localStorage.setItem('usuarioNss', usuario.value.nss)
  localStorage.setItem('usuarioSexo', usuario.value.sexo)
  localStorage.setItem('usuarioFechaNac', usuario.value.fechaNac)
  localStorage.setItem('usuarioPeso', usuario.value.peso)
  localStorage.setItem('usuarioEstatura', usuario.value.estatura)

  // 3. Salimos del modo edición
  isEditing.value = false
  alert('¡Perfil actualizado con éxito de forma local! 🚀')
}
</script>

<style scoped>
.perfil-layout {
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
  padding: 2rem;
  background-color: #ffffff;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  min-height: 100vh;
}

.perfil-card {
  width: 100%;
  max-width: 700px;
  background: white;
  padding: 1rem;
  box-sizing: border-box;
}

.perfil-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 2rem;
  color: #0f172a;
}

.perfil-header-container {
  margin-bottom: 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  background: #ffffff;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.avatar-circle {
  width: 90px;
  height: 90px;
  background-color: #e6f4f1;
  color: #0d8a72;
  font-weight: 700;
  font-size: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  border: 2px solid #cbd5e1;
}

.header-text h2 {
  font-size: 1.6rem;
  margin: 0 0 0.2rem 0;
  font-weight: 700;
  color: #0f172a;
}

.user-role {
  display: inline-block;
  font-size: 0.85rem;
  color: #64748b;
  background-color: #f1f5f9;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-weight: 600;
  margin-bottom: 0.4rem;
}

.user-email, .user-phone {
  display: block;
  font-size: 0.95rem;
  color: #475569;
  margin-top: 0.2rem;
}

.informacion-personal h3 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 1.2rem;
  color: #1e293b;
}

.info-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2.5rem;
}

.info-table tr:nth-child(even) {
  background-color: #f8fafc;
}

.info-table td {
  padding: 0.9rem 1rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 1rem;
  vertical-align: middle;
}

.info-table .label {
  width: 45%;
  font-weight: 600;
  color: #475569;
  text-align: left;
}

.info-table .value {
  width: 55%;
  font-weight: 500;
  color: #0f172a;
}

/* --- ESTILOS DE LOS INPUTS DE EDICIÓN --- */
.edit-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #0f172a;
  background-color: #fff;
  box-sizing: border-box;
}

.edit-input:focus {
  border-color: #0d8a72;
  outline: none;
  box-shadow: 0 0 0 2px rgba(13, 138, 114, 0.15);
}

.select-input {
  cursor: pointer;
}

.input-unit {
  display: flex;
  align-items: center;
  position: relative;
}

.input-unit .edit-input {
  padding-right: 2.5rem;
}

.unit-tag {
  position: absolute;
  right: 0.75rem;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 600;
}

/* --- BOTONES DE ACCIÓN --- */
.action-buttons {
  width: 100%;
}

.btn-editar {
  background-color: #0d8a72;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.9rem;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: background-color 0.2s;
}

.btn-editar:hover {
  background-color: #0a6c59;
}

.edit-buttons-group {
  display: flex;
  gap: 1rem;
  width: 100%;
}

.btn-cancelar {
  flex: 1;
  background-color: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.9rem;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-cancelar:hover {
  background-color: #e2e8f0;
}

.btn-guardar {
  flex: 2;
  background-color: #0d8a72;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.9rem;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-guardar:hover {
  background-color: #0a6c59;
}

@media (max-width: 480px) {
  .profile-header { flex-direction: column; text-align: center; }
  .info-table td { padding: 0.75rem 0.5rem; font-size: 0.9rem; }
  .edit-buttons-group { flex-direction: column; }
}
</style>