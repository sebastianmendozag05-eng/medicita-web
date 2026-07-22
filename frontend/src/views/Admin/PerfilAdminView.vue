<template>
  <div class="perfil-view">
    <div class="page-header">
      <div>
        <h2>Mi Perfil</h2>
        <p class="subtitle">Información y configuración de tu cuenta</p>
      </div>
    </div>

    <div class="perfil-grid">
      <!-- Tarjeta izquierda -->
      <div class="card perfil-card">
        <div class="avatar-section">
          <div class="avatar">AD</div>
          <h3>{{ formulario.nombre }} {{ formulario.apePat }}</h3>
          <span class="rol-badge">Administrador</span>
        </div>
        <div class="info-list">
          <div class="info-row"><span>📧 Correo</span><strong>{{ formulario.correo }}</strong></div>
        </div>
      </div>

      <!-- Formulario derecha -->
      <div class="card form-card">
        <div class="form-section">
          <h4>Datos personales</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Nombre(s)</label>
              <input type="text" v-model="formulario.nombre" :disabled="!editando" />
            </div>
            <div class="form-group">
              <label>Apellido Paterno</label>
              <input type="text" v-model="formulario.apePat" :disabled="!editando" />
            </div>
          </div>
          <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" v-model="formulario.correo" :disabled="!editando" />
          </div>
        </div>

        <div class="form-section" v-if="editando">
          <h4>Cambiar contraseña <span class="opcional">(opcional)</span></h4>
          <div class="form-group">
            <label>Contraseña actual</label>
            <input type="password" v-model="passwords.actual" placeholder="••••••••" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Nueva contraseña</label>
              <input type="password" v-model="passwords.nueva" placeholder="••••••••" />
            </div>
            <div class="form-group">
              <label>Confirmar contraseña</label>
              <input type="password" v-model="passwords.confirmar" placeholder="••••••••" />
            </div>
          </div>
          <p v-if="passwords.nueva && passwords.nueva.length < 8" class="invalido">✗ Mínimo 8 caracteres.</p>
          <p v-if="passwords.confirmar && passwords.nueva !== passwords.confirmar" class="invalido">✗ Las contraseñas no coinciden.</p>
          <p v-if="errorPassword" class="invalido">✗ {{ errorPassword }}</p>
        </div>

        <div class="form-actions">
          <template v-if="!editando">
            <button class="btn-primary" @click="editando = true">Editar perfil</button>
          </template>
          <template v-else>
            <button class="btn-secondary" @click="cancelarEdicion">Cancelar</button>
            <button class="btn-primary" @click="guardarCambios">Guardar cambios</button>
          </template>
        </div>

        <div v-if="mensajeGuardado" class="mensaje-ok">✓ Cambios guardados correctamente.</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const editando = ref(false)
const mensajeGuardado = ref(false)
const passwords = ref({ actual: '', nueva: '', confirmar: '' })
const errorPassword = ref('')

const formulario = ref({ nombre: '', apePat: '', correo: '' })
let formularioOriginal = {}

onMounted(async () => {
  try {
    const res = await fetch(`${BASE_URL}/user`, { headers: getHeaders() })
    if (res.ok) {
      const u = await res.json()
      const partes = (u.name || '').split(' ')
      formulario.value.nombre = partes[0] ?? ''
      formulario.value.apePat = partes.slice(1).join(' ') ?? ''
      formulario.value.correo = u.email ?? ''
      formularioOriginal = { ...formulario.value }
      return
    }
  } catch { /* silencioso */ }
  const nombre = localStorage.getItem('usuarioNombre') ?? ''
  const partes = nombre.split(' ')
  formulario.value.nombre = partes[0] ?? ''
  formulario.value.apePat = partes[1] ?? ''
  formulario.value.correo = localStorage.getItem('usuarioCorreo') ?? ''
  formularioOriginal = { ...formulario.value }
})

const cancelarEdicion = () => {
  Object.assign(formulario.value, formularioOriginal)
  passwords.value = { actual: '', nueva: '', confirmar: '' }
  editando.value = false
}

const guardarCambios = async () => {
  errorPassword.value = ''
  if (passwords.value.nueva && passwords.value.nueva.length < 8) {
    errorPassword.value = 'La nueva contraseña debe tener al menos 8 caracteres.'
    return
  }
  if (passwords.value.nueva && passwords.value.nueva !== passwords.value.confirmar) {
    errorPassword.value = 'Las contraseñas no coinciden.'
    return
  }
  try {
    const res = await fetch(`${BASE_URL}/user`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify({
        name: `${formulario.value.nombre} ${formulario.value.apePat}`.trim(),
        email: formulario.value.correo
      })
    })
    if (!res.ok) { alert('No se pudo guardar el perfil.'); return }

    if (passwords.value.nueva) {
      const resPass = await fetch(`${BASE_URL}/user/password`, {
        method: 'PUT',
        headers: getHeaders(),
        body: JSON.stringify({
          password_actual: passwords.value.actual,
          password: passwords.value.nueva,
          password_confirmation: passwords.value.confirmar
        })
      })
      const dataPass = await resPass.json()
      if (!resPass.ok) {
        errorPassword.value = dataPass.message ?? 'No se pudo cambiar la contraseña.'
        return
      }
      // El backend rota el token al cambiar contraseña; actualizamos el guardado localmente
      localStorage.setItem('token', dataPass.token)
    }

    localStorage.setItem('usuarioNombre', `${formulario.value.nombre} ${formulario.value.apePat}`)
    localStorage.setItem('usuarioCorreo', formulario.value.correo)
    formularioOriginal = { ...formulario.value }
    editando.value = false
    mensajeGuardado.value = true
    passwords.value = { actual: '', nueva: '', confirmar: '' }
    setTimeout(() => { mensajeGuardado.value = false }, 3000)
  } catch {
    alert('Error de conexión al guardar el perfil.')
  }
}
</script>

<style scoped>
.perfil-view { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header h2 { margin: 0 0 0.2rem; font-size: 1.4rem; color: #1e293b; font-weight: 700; }
.subtitle { margin: 0; color: #64748b; font-size: 0.9rem; }

.perfil-grid { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start; }
.card { background: white; border-radius: 14px; padding: 1.6rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }

/* Perfil card */
.avatar-section { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.avatar { width: 72px; height: 72px; background: #0d8a72; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 700; }
.avatar-section h3 { margin: 0; font-size: 1rem; color: #1e293b; font-weight: 700; text-align: center; }
.rol-badge { background: #e6f4f1; color: #0d8a72; font-size: 0.78rem; font-weight: 700; padding: 3px 12px; border-radius: 99px; }

.info-list { display: flex; flex-direction: column; gap: 0.75rem; }
.info-row { display: flex; flex-direction: column; gap: 0.1rem; font-size: 0.85rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; }
.info-row span { color: #94a3b8; font-size: 0.78rem; }
.info-row strong { color: #334155; }

/* Form card */
.form-card { display: flex; flex-direction: column; gap: 1.5rem; }
.form-section h4 { margin: 0 0 1rem; font-size: 0.95rem; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.6rem; }
.opcional { font-weight: 400; font-size: 0.8rem; color: #94a3b8; }

.form-row { display: flex; gap: 1rem; }
.form-row .form-group { flex: 1; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.8rem; color: #64748b; font-weight: 500; }
.form-group input { padding: 0.65rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; color: #334155; transition: border-color 0.2s; background: white; }
.form-group input:focus { border-color: #0d8a72; outline: none; }
.form-group input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

.invalido { color: #ef4444; font-size: 0.8rem; margin: -0.4rem 0 0.5rem; }

.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-primary { background: #0d8a72; color: white; border: none; border-radius: 8px; padding: 0.65rem 1.4rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }
.btn-primary:hover { background: #0a6c59; }
.btn-secondary { background: #f1f5f9; color: #334155; border: none; border-radius: 8px; padding: 0.65rem 1.4rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }

.mensaje-ok { text-align: center; background: #e6f4f1; color: #0d8a72; font-size: 0.88rem; font-weight: 600; padding: 0.65rem; border-radius: 8px; }

@media (max-width: 800px) { .perfil-grid { grid-template-columns: 1fr; } .form-row { flex-direction: column; } }
</style>
