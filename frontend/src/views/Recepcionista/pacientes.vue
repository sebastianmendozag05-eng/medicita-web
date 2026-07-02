<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const busqueda = ref('')
const pacienteSeleccionado = ref(null)
const mostrarModalNuevo = ref(false)

const pacientes = ref([
  { id: 1, nombre: 'María González',   edad: 34, sexo: 'F', telefono: '442 111 2233', email: 'maria@email.com',   sangre: 'O+',  alergias: 'Penicilina',    ultimaVisita: '2026-06-20', totalCitas: 5 },
  { id: 2, nombre: 'Carlos Pérez',     edad: 52, sexo: 'M', telefono: '442 222 3344', email: 'carlos@email.com',  sangre: 'A+',  alergias: 'Ninguna',       ultimaVisita: '2026-06-20', totalCitas: 3 },
  { id: 3, nombre: 'Ana Martínez',     edad: 28, sexo: 'F', telefono: '442 333 4455', email: 'ana@email.com',     sangre: 'B-',  alergias: 'Ibuprofeno',    ultimaVisita: '2026-06-18', totalCitas: 7 },
  { id: 4, nombre: 'Luis Hernández',   edad: 45, sexo: 'M', telefono: '442 444 5566', email: 'luis@email.com',    sangre: 'AB+', alergias: 'Ninguna',       ultimaVisita: '2026-06-20', totalCitas: 2 },
  { id: 5, nombre: 'Sofía Morales',    edad: 61, sexo: 'F', telefono: '442 555 6677', email: 'sofia@email.com',   sangre: 'O-',  alergias: 'Aspirina',      ultimaVisita: '2026-06-10', totalCitas: 9 },
  { id: 6, nombre: 'Roberto Díaz',     edad: 38, sexo: 'M', telefono: '442 666 7788', email: 'roberto@email.com', sangre: 'A-',  alergias: 'Ninguna',       ultimaVisita: '2026-06-15', totalCitas: 4 },
  { id: 7, nombre: 'Elena Castillo',   edad: 22, sexo: 'F', telefono: '442 777 8899', email: 'elena@email.com',   sangre: 'B+',  alergias: 'Látex',         ultimaVisita: '2026-06-21', totalCitas: 1 },
])

const nuevoPaciente = ref({ nombre: '', edad: '', sexo: '', telefono: '', email: '', sangre: '', alergias: '' })

const pacientesFiltrados = computed(() => {
  const q = busqueda.value.toLowerCase()
  if (!q) return pacientes.value
  return pacientes.value.filter(p =>
    p.nombre.toLowerCase().includes(q) ||
    p.email.toLowerCase().includes(q) ||
    p.telefono.includes(q)
  )
})

const seleccionar = (p) => { pacienteSeleccionado.value = p }

const guardarPaciente = () => {
  if (!nuevoPaciente.value.nombre || !nuevoPaciente.value.telefono) return
  pacientes.value.push({
    id: Date.now(),
    ...nuevoPaciente.value,
    edad: Number(nuevoPaciente.value.edad),
    ultimaVisita: new Date().toISOString().split('T')[0],
    totalCitas: 0
  })
  nuevoPaciente.value = { nombre: '', edad: '', sexo: '', telefono: '', email: '', sangre: '', alergias: '' }
  mostrarModalNuevo.value = false
}

const cancelarModal = () => {
  nuevoPaciente.value = { nombre: '', edad: '', sexo: '', telefono: '', email: '', sangre: '', alergias: '' }
  mostrarModalNuevo.value = false
}

const formatearFecha = (f) => {
  const [y, m, d] = f.split('-')
  return new Date(y, m - 1, d).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
}

const colorAvatar = (sexo) => sexo === 'F' ? 'avatar-f' : 'avatar-m'
const inicial = (nombre) => nombre.charAt(0).toUpperCase()

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
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera">
        <h1 class="saludo-principal">👥 Pacientes</h1>
        <button @click="mostrarModalNuevo = true" class="btn-nuevo">+ Nuevo paciente</button>
      </div>

      <div class="layout-pacientes">
        <!-- Lista -->
        <div class="panel-lista">
          <div class="busqueda-wrap">
            <input v-model="busqueda" type="text" class="input-busqueda" placeholder="🔍 Buscar por nombre, email o teléfono..." />
          </div>
          <p class="conteo">{{ pacientesFiltrados.length }} paciente(s)</p>

          <div class="lista-pacientes">
            <div
              v-for="p in pacientesFiltrados" :key="p.id"
              class="fila-paciente"
              :class="{ 'fila-activa': pacienteSeleccionado?.id === p.id }"
              @click="seleccionar(p)"
            >
              <div :class="['avatar', colorAvatar(p.sexo)]">{{ inicial(p.nombre) }}</div>
              <div class="info-fila">
                <p class="nombre-fila">{{ p.nombre }}</p>
                <p class="meta-fila">{{ p.edad }} años · {{ p.telefono }}</p>
                <p class="meta-fila">{{ p.totalCitas }} cita(s) · Última: {{ formatearFecha(p.ultimaVisita) }}</p>
              </div>
            </div>
            <div v-if="pacientesFiltrados.length === 0" class="sin-resultados">No se encontraron pacientes</div>
          </div>
        </div>

        <!-- Detalle -->
        <div class="panel-detalle">
          <div v-if="!pacienteSeleccionado" class="estado-vacio">
            <div class="icono-vacio">👥</div>
            <p class="titulo-vacio">Selecciona un paciente</p>
            <p class="subtitulo-vacio">Haz clic en un paciente para ver su información</p>
          </div>

          <div v-else>
            <div class="tarjeta-perfil">
              <div :class="['avatar-grande', colorAvatar(pacienteSeleccionado.sexo)]">{{ inicial(pacienteSeleccionado.nombre) }}</div>
              <div class="datos-perfil">
                <h2 class="nombre-grande">{{ pacienteSeleccionado.nombre }}</h2>
                <div class="badges-row">
                  <span class="badge-dato">{{ pacienteSeleccionado.edad }} años</span>
                  <span class="badge-dato">{{ pacienteSeleccionado.sexo === 'F' ? 'Femenino' : 'Masculino' }}</span>
                  <span class="badge-sangre">🩸 {{ pacienteSeleccionado.sangre }}</span>
                </div>
              </div>
              <div class="resumen-citas">
                <span class="num-citas">{{ pacienteSeleccionado.totalCitas }}</span>
                <span class="label-citas">cita(s)</span>
              </div>
            </div>

            <div class="grilla-datos">
              <div class="dato-grupo">
                <span class="dato-label">📞 Teléfono</span>
                <span class="dato-valor">{{ pacienteSeleccionado.telefono }}</span>
              </div>
              <div class="dato-grupo">
                <span class="dato-label">📧 Email</span>
                <span class="dato-valor">{{ pacienteSeleccionado.email || '—' }}</span>
              </div>
              <div class="dato-grupo">
                <span class="dato-label">⚕️ Alergias</span>
                <span class="dato-valor">{{ pacienteSeleccionado.alergias || 'Ninguna' }}</span>
              </div>
              <div class="dato-grupo">
                <span class="dato-label">📅 Última visita</span>
                <span class="dato-valor">{{ formatearFecha(pacienteSeleccionado.ultimaVisita) }}</span>
              </div>
            </div>

            <router-link
              :to="`/recepcionista/citas`"
              class="btn-ver-citas"
            >
              Ver citas de este paciente →
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal nuevo paciente -->
    <div v-if="mostrarModalNuevo" class="overlay-modal" @click.self="cancelarModal">
      <div class="modal">
        <h2 class="titulo-modal">Registrar nuevo paciente</h2>
        <div class="grilla-modal">
          <div class="campo-modal campo-ancho">
            <label>Nombre completo *</label>
            <input v-model="nuevoPaciente.nombre" type="text" class="input-modal" placeholder="Nombre del paciente" />
          </div>
          <div class="campo-modal">
            <label>Edad</label>
            <input v-model="nuevoPaciente.edad" type="number" class="input-modal" placeholder="Años" min="0" max="120" />
          </div>
          <div class="campo-modal">
            <label>Sexo</label>
            <select v-model="nuevoPaciente.sexo" class="input-modal">
              <option value="">Seleccionar</option>
              <option value="F">Femenino</option>
              <option value="M">Masculino</option>
            </select>
          </div>
          <div class="campo-modal">
            <label>Teléfono *</label>
            <input v-model="nuevoPaciente.telefono" type="tel" class="input-modal" placeholder="442 000 0000" />
          </div>
          <div class="campo-modal">
            <label>Email</label>
            <input v-model="nuevoPaciente.email" type="email" class="input-modal" placeholder="correo@ejemplo.com" />
          </div>
          <div class="campo-modal">
            <label>Tipo de sangre</label>
            <select v-model="nuevoPaciente.sangre" class="input-modal">
              <option value="">Seleccionar</option>
              <option v-for="t in ['A+','A-','B+','B-','AB+','AB-','O+','O-']" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div class="campo-modal campo-ancho">
            <label>Alergias conocidas</label>
            <input v-model="nuevoPaciente.alergias" type="text" class="input-modal" placeholder="Ej. Penicilina, Aspirina, o Ninguna" />
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