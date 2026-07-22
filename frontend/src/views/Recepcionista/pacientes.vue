<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const busqueda = ref('')
const pacienteSeleccionado = ref(null)
const mostrarModalNuevo = ref(false)
const pacientes = ref([])

const nuevoPaciente = ref({ pacNombre: '', pacApePat: '', pacApeMat: '', pacFechaNac: '', pacNSS: '', pacSexo: '', pacTelefono: '', pacCorreo: '', pacPeso: '', pacEstatura: '' })

onMounted(async () => {
  const res = await fetch(`${BASE_URL}/pacientes`, { headers: getHeaders() })
  if (res.ok) pacientes.value = await res.json()
})

const pacientesFiltrados = computed(() => {
  const q = busqueda.value.toLowerCase()
  if (!q) return pacientes.value
  return pacientes.value.filter(p =>
    `${p.pacNombre} ${p.pacApePat}`.toLowerCase().includes(q) ||
    (p.pacCorreo ?? '').toLowerCase().includes(q) ||
    (p.pacTelefono ?? '').includes(q)
  )
})

const seleccionar = (p) => { pacienteSeleccionado.value = p }

const guardarPaciente = async () => {
  if (!nuevoPaciente.value.pacNombre || !nuevoPaciente.value.pacTelefono) return
  const res = await fetch(`${BASE_URL}/pacientes`, {
    method: 'POST', headers: getHeaders(),
    body: JSON.stringify({ ...nuevoPaciente.value, pacPeso: parseFloat(nuevoPaciente.value.pacPeso), pacEstatura: parseFloat(nuevoPaciente.value.pacEstatura) })
  })
  if (res.ok) {
    const nuevo = await res.json()
    pacientes.value.push(nuevo)
    mostrarModalNuevo.value = false
    nuevoPaciente.value = { pacNombre: '', pacApePat: '', pacApeMat: '', pacFechaNac: '', pacNSS: '', pacSexo: '', pacTelefono: '', pacCorreo: '', pacPeso: '', pacEstatura: '' }
  }
}

const cancelarModal = () => { mostrarModalNuevo.value = false }

const formatearFecha = (f) => {
  if (!f) return '—'
  return new Date(f).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
}

const colorAvatar = (sexo) => sexo === 'Femenino' ? 'avatar-f' : 'avatar-m'
const inicial = (nombre) => (nombre ?? '?').charAt(0).toUpperCase()
const cerrarSesion = () => { localStorage.clear(); router.push('/login') }
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand"><span class="logo-icon">+</span><h1>MediCita</h1></div>
      <nav class="menu-navegacion">
        <router-link to="/recepcionista/inicio"    class="enlace-menu"><span>🏠</span> Inicio</router-link>
        <router-link to="/recepcionista/citas"     class="enlace-menu"><span>📋</span> Citas</router-link>
        <router-link to="/recepcionista/pacientes" class="enlace-menu activo"><span>👥</span> Pacientes</router-link>
        <router-link to="/recepcionista/checkin"   class="enlace-menu"><span>✅</span> Check-in</router-link>
        <router-link to="/recepcionista/reportes"  class="enlace-menu"><span>📊</span> Reportes</router-link>
        <router-link to="/recepcionista/notificaciones"  class="enlace-menu"><span>🔔</span> Notificaciones</router-link>
        <router-link to="/recepcionista/perfil"  class="enlace-menu"><span>👤</span> Mi Perfil</router-link>
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera">
        <h1 class="saludo-principal">👥 Pacientes</h1>
        <button @click="mostrarModalNuevo = true" class="btn-nuevo">+ Nuevo paciente</button>
      </div>

      <div class="layout-pacientes">
        <div class="panel-lista">
          <div class="busqueda-wrap">
            <input v-model="busqueda" type="text" class="input-busqueda" placeholder="🔍 Buscar por nombre, email o teléfono..." />
          </div>
          <p class="conteo">{{ pacientesFiltrados.length }} paciente(s)</p>
          <div class="lista-pacientes">
            <div v-for="p in pacientesFiltrados" :key="p.pacId"
              class="fila-paciente"
              :class="{ 'fila-activa': pacienteSeleccionado?.pacId === p.pacId }"
              @click="seleccionar(p)">
              <div :class="['avatar', colorAvatar(p.pacSexo)]">{{ inicial(p.pacNombre) }}</div>
              <div class="info-fila">
                <p class="nombre-fila">{{ p.pacNombre }} {{ p.pacApePat }}</p>
                <p class="meta-fila">{{ p.pacTelefono }}</p>
                <p class="meta-fila">{{ p.pacCorreo }}</p>
              </div>
            </div>
            <div v-if="pacientesFiltrados.length === 0" class="sin-resultados">No se encontraron pacientes</div>
          </div>
        </div>

        <div class="panel-detalle">
          <div v-if="!pacienteSeleccionado" class="estado-vacio">
            <div class="icono-vacio">👥</div>
            <p class="titulo-vacio">Selecciona un paciente</p>
            <p class="subtitulo-vacio">Haz clic en un paciente para ver su información</p>
          </div>

          <div v-else>
            <div class="tarjeta-perfil">
              <div :class="['avatar-grande', colorAvatar(pacienteSeleccionado.pacSexo)]">{{ inicial(pacienteSeleccionado.pacNombre) }}</div>
              <div class="datos-perfil">
                <h2 class="nombre-grande">{{ pacienteSeleccionado.pacNombre }} {{ pacienteSeleccionado.pacApePat }}</h2>
                <div class="badges-row">
                  <span class="badge-dato">{{ pacienteSeleccionado.pacSexo }}</span>
                </div>
              </div>
            </div>

            <div class="grilla-datos">
              <div class="dato-grupo">
                <span class="dato-label">📞 Teléfono</span>
                <span class="dato-valor">{{ pacienteSeleccionado.pacTelefono }}</span>
              </div>
              <div class="dato-grupo">
                <span class="dato-label">📧 Email</span>
                <span class="dato-valor">{{ pacienteSeleccionado.pacCorreo || '—' }}</span>
              </div>
              <div class="dato-grupo">
                <span class="dato-label">📅 Fecha nacimiento</span>
                <span class="dato-valor">{{ formatearFecha(pacienteSeleccionado.pacFechaNac) }}</span>
              </div>
              <div class="dato-grupo">
                <span class="dato-label">⚖️ Peso / Estatura</span>
                <span class="dato-valor">{{ pacienteSeleccionado.pacPeso }} kg / {{ pacienteSeleccionado.pacEstatura }} m</span>
              </div>
            </div>

            <router-link to="/recepcionista/citas" class="btn-ver-citas">
              Ver citas de este paciente →
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalNuevo" class="overlay-modal" @click.self="cancelarModal">
      <div class="modal">
        <h2 class="titulo-modal">Registrar nuevo paciente</h2>
        <div class="grilla-modal">
          <div class="campo-modal">
            <label>Nombre(s) *</label>
            <input v-model="nuevoPaciente.pacNombre" type="text" class="input-modal" placeholder="Nombre(s)" />
          </div>
          <div class="campo-modal">
            <label>Apellido Paterno *</label>
            <input v-model="nuevoPaciente.pacApePat" type="text" class="input-modal" placeholder="Apellido Paterno" />
          </div>
          <div class="campo-modal">
            <label>Apellido Materno</label>
            <input v-model="nuevoPaciente.pacApeMat" type="text" class="input-modal" placeholder="Apellido Materno" />
          </div>
          <div class="campo-modal">
            <label>Sexo</label>
            <select v-model="nuevoPaciente.pacSexo" class="input-modal">
              <option value="">Seleccionar</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
            </select>
          </div>
          <div class="campo-modal">
            <label>Fecha de Nacimiento *</label>
            <input v-model="nuevoPaciente.pacFechaNac" type="date" class="input-modal" />
          </div>
          <div class="campo-modal">
            <label>NSS *</label>
            <input v-model="nuevoPaciente.pacNSS" type="text" class="input-modal" placeholder="NSS" />
          </div>
          <div class="campo-modal">
            <label>Teléfono *</label>
            <input v-model="nuevoPaciente.pacTelefono" type="tel" class="input-modal" placeholder="442 000 0000" />
          </div>
          <div class="campo-modal">
            <label>Email</label>
            <input v-model="nuevoPaciente.pacCorreo" type="email" class="input-modal" placeholder="correo@ejemplo.com" />
          </div>
          <div class="campo-modal">
            <label>Peso (kg)</label>
            <input v-model="nuevoPaciente.pacPeso" type="number" class="input-modal" placeholder="65.5" />
          </div>
          <div class="campo-modal">
            <label>Estatura (m)</label>
            <input v-model="nuevoPaciente.pacEstatura" type="number" class="input-modal" placeholder="1.70" />
          </div>
        </div>
        <div class="botones-modal">
          <button @click="cancelarModal" class="btn-modal-cancelar">Cancelar</button>
          <button @click="guardarPaciente" class="btn-modal-guardar">Registrar paciente</button>
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
.cabecera { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; }
.saludo-principal { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0; }
.btn-nuevo { background: #0d8a72; color: white; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: background 0.2s; }
.btn-nuevo:hover { background: #0a7060; }

.layout-pacientes { display: grid; grid-template-columns: 320px 1fr; gap: 20px; }
.panel-lista { background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.busqueda-wrap { padding: 14px; border-bottom: 1px solid #f1f5f9; }
.input-busqueda { width: 100%; box-sizing: border-box; padding: 9px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; font-family: inherit; background: #f8fafc; }
.input-busqueda:focus { border-color: #0d8a72; background: white; }
.conteo { font-size: 12px; color: #94a3b8; padding: 6px 14px 0; margin: 0; font-weight: 600; }
.lista-pacientes { max-height: 65vh; overflow-y: auto; }
.fila-paciente { display: flex; gap: 12px; padding: 14px 16px; cursor: pointer; border-bottom: 1px solid #f8fafc; transition: background 0.15s; align-items: center; }
.fila-paciente:hover { background: #f8fafc; }
.fila-activa { background: #e6f4f1 !important; }
.avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: white; flex-shrink: 0; }
.avatar-f { background: linear-gradient(135deg, #ec4899, #f43f5e); }
.avatar-m { background: linear-gradient(135deg, #3b82f6, #6366f1); }
.info-fila { flex-grow: 1; }
.nombre-fila { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 2px; }
.meta-fila { font-size: 11px; color: #94a3b8; margin: 0; }
.sin-resultados { text-align: center; padding: 32px; font-size: 14px; color: #94a3b8; }

.panel-detalle { display: flex; flex-direction: column; gap: 16px; }
.estado-vacio { background: white; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px; text-align: center; }
.icono-vacio { font-size: 48px; margin-bottom: 12px; opacity: 0.4; }
.titulo-vacio { font-size: 16px; font-weight: 700; color: #475569; margin: 0 0 6px; }
.subtitulo-vacio { font-size: 13px; color: #94a3b8; margin: 0; }

.tarjeta-perfil { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
.avatar-grande { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: white; flex-shrink: 0; }
.datos-perfil { flex-grow: 1; }
.nombre-grande { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
.badges-row { display: flex; gap: 6px; flex-wrap: wrap; }
.badge-dato { background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
.badge-sangre { background: #fee2e2; color: #991b1b; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.resumen-citas { display: flex; flex-direction: column; align-items: center; background: #f0fdfa; border-radius: 10px; padding: 12px 16px; flex-shrink: 0; }
.num-citas { font-size: 28px; font-weight: 800; color: #0d8a72; line-height: 1; }
.label-citas { font-size: 12px; color: #0d8a72; font-weight: 600; }

.grilla-datos { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; }
.dato-grupo { display: flex; flex-direction: column; gap: 4px; }
.dato-label { font-size: 12px; font-weight: 700; color: #94a3b8; }
.dato-valor { font-size: 14px; font-weight: 600; color: #1e293b; }

.btn-ver-citas { display: block; text-align: center; padding: 11px; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; color: #0d8a72; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s; }
.btn-ver-citas:hover { background: #ccfbf1; }

/* Modal */
.overlay-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; border-radius: 16px; padding: 28px; width: 520px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.titulo-modal { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 20px; }
.grilla-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
.campo-modal { display: flex; flex-direction: column; gap: 5px; }
.campo-ancho { grid-column: 1 / -1; }
.campo-modal label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
.input-modal { padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; font-family: inherit; background: #f8fafc; }
.input-modal:focus { border-color: #0d8a72; background: white; }
.botones-modal { display: flex; justify-content: flex-end; gap: 10px; }
.btn-modal-cancelar { padding: 9px 18px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f1f5f9; color: #64748b; font-weight: 600; font-size: 14px; cursor: pointer; }
.btn-modal-guardar { padding: 9px 18px; border: none; border-radius: 8px; background: #0d8a72; color: white; font-weight: 700; font-size: 14px; cursor: pointer; }
.btn-modal-guardar:hover { background: #0a7060; }

@media (max-width: 900px) { .layout-pacientes { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .pantalla-layout { flex-direction: column; } .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; } }
</style>