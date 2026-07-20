<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const editando = ref(false)
const guardando = ref(false)
const mensajeGuardado = ref(false)
const error = ref('')

const perfil = ref({ nombre: '', apePat: '', apeMat: '', correo: '', telefono: '', edad: '' })
let perfilOriginal = {}

const cargarPerfil = async () => {
  const astId = localStorage.getItem('astId')
  if (!astId) return
  try {
    const res = await fetch(`${BASE_URL}/recepcionistas/${astId}`, { headers: getHeaders() })
    if (!res.ok) return
    const r = await res.json()
    perfil.value = {
      nombre: r.astNombre || '', apePat: r.astApePat || '', apeMat: r.astApeMat || '',
      correo: r.astCorreo || '', telefono: r.astTelefono || '', edad: r.astEdad || ''
    }
    perfilOriginal = { ...perfil.value }
  } catch { /* silencioso */ }
}

onMounted(cargarPerfil)

const iniciales = () => {
  if (!perfil.value.nombre) return 'R'
  return (perfil.value.nombre.charAt(0) + (perfil.value.apePat.charAt(0) || '')).toUpperCase()
}

const activarEdicion = () => { perfilOriginal = { ...perfil.value }; editando.value = true }
const cancelarEdicion = () => { perfil.value = { ...perfilOriginal }; editando.value = false; error.value = '' }

const guardarCambios = async () => {
  const astId = localStorage.getItem('astId')
  if (!astId) { error.value = 'Tu cuenta no está vinculada a un perfil de recepcionista.'; return }
  guardando.value = true
  error.value = ''
  try {
    const res = await fetch(`${BASE_URL}/recepcionistas/${astId}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify({
        recNombre: perfil.value.nombre,
        recApePat: perfil.value.apePat,
        recApeMat: perfil.value.apeMat || null,
        recCorreo: perfil.value.correo,
        recTelefono: perfil.value.telefono,
        recEdad: perfil.value.edad
      })
    })
    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      error.value = data.message ?? 'No se pudo guardar el perfil.'
      return
    }
    localStorage.setItem('usuarioNombre', `${perfil.value.nombre} ${perfil.value.apePat}`)
    perfilOriginal = { ...perfil.value }
    editando.value = false
    mensajeGuardado.value = true
    setTimeout(() => { mensajeGuardado.value = false }, 3000)
  } catch {
    error.value = 'Error de conexión al guardar el perfil.'
  } finally {
    guardando.value = false
  }
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
        <router-link to="/recepcionista/checkin"   class="enlace-menu"><span>✅</span> Check-in</router-link>
        <router-link to="/recepcionista/reportes"  class="enlace-menu"><span>📊</span> Reportes</router-link>
        <router-link to="/recepcionista/notificaciones"  class="enlace-menu"><span>🔔</span> Notificaciones</router-link>
        <router-link to="/recepcionista/perfil"    class="enlace-menu activo"><span>👤</span> Mi Perfil</router-link>
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera"><h1 class="saludo-principal">Mi Perfil</h1></div>

      <div class="tarjeta-perfil">
        <div class="encabezado-perfil">
          <div class="avatar-circulo">{{ iniciales() }}</div>
          <div>
            <h2>{{ perfil.nombre }} {{ perfil.apePat }}</h2>
            <span class="etiqueta-rol">Recepcionista</span>
          </div>
        </div>

        <div class="cuadricula-campos">
          <div class="campo">
            <label>Nombre(s)</label>
            <input type="text" v-model="perfil.nombre" :disabled="!editando" />
          </div>
          <div class="campo">
            <label>Apellido Paterno</label>
            <input type="text" v-model="perfil.apePat" :disabled="!editando" />
          </div>
          <div class="campo">
            <label>Apellido Materno</label>
            <input type="text" v-model="perfil.apeMat" :disabled="!editando" />
          </div>
          <div class="campo">
            <label>Edad</label>
            <input type="number" v-model="perfil.edad" :disabled="!editando" />
          </div>
          <div class="campo">
            <label>Correo electrónico</label>
            <input type="email" v-model="perfil.correo" :disabled="!editando" />
          </div>
          <div class="campo">
            <label>Teléfono</label>
            <input type="tel" v-model="perfil.telefono" :disabled="!editando" />
          </div>
        </div>

        <p v-if="error" class="mensaje-error">✗ {{ error }}</p>
        <p v-if="mensajeGuardado" class="mensaje-ok">✓ Perfil actualizado correctamente.</p>

        <div class="acciones-perfil">
          <button v-if="!editando" class="btn-primario" @click="activarEdicion">Editar Perfil</button>
          <template v-else>
            <button class="btn-secundario" @click="cancelarEdicion">Cancelar</button>
            <button class="btn-primario" :disabled="guardando" @click="guardarCambios">
              {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background-color: #f8fafc; }
.sidebar-izquierdo {
  width: 260px; background-color: #ffffff; border-right: 1px solid #e2e8f0;
  display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; }
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
  flex-grow: 1; padding: 24px; box-sizing: border-box; max-width: 720px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.cabecera { margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; }
.saludo-principal { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
.tarjeta-perfil { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
.encabezado-perfil { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.avatar-circulo {
  width: 64px; height: 64px; background: #e6f4f1; color: #0d8a72; font-weight: 700;
  font-size: 1.4rem; display: flex; align-items: center; justify-content: center;
  border-radius: 50%; border: 2px solid #cbd5e1;
}
.encabezado-perfil h2 { margin: 0 0 4px 0; font-size: 1.2rem; color: #0f172a; }
.etiqueta-rol {
  font-size: 0.78rem; font-weight: 700; color: #0d8a72; background: #e6f4f1;
  padding: 2px 10px; border-radius: 99px;
}
.cuadricula-campos { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
.campo { display: flex; flex-direction: column; gap: 6px; }
.campo label { font-size: 0.8rem; color: #64748b; font-weight: 600; }
.campo input {
  padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 0.9rem; color: #0f172a; background: white;
}
.campo input:disabled { background: #f8fafc; color: #94a3b8; }
.campo input:focus { border-color: #0d8a72; outline: none; }
.mensaje-error { color: #b91c1c; font-size: 0.85rem; margin: 8px 0 0; }
.mensaje-ok { color: #0d8a72; font-size: 0.85rem; margin: 8px 0 0; font-weight: 600; }
.acciones-perfil { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.btn-primario {
  background: #0d8a72; color: white; border: none; border-radius: 8px;
  padding: 0.65rem 1.4rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;
}
.btn-primario:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secundario {
  background: #f1f5f9; color: #334155; border: none; border-radius: 8px;
  padding: 0.65rem 1.4rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;
}

@media (max-width: 700px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
  .cuadricula-campos { grid-template-columns: 1fr; }
}
</style>
