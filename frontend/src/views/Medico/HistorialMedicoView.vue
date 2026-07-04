<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const busqueda = ref('')
const pacientes = ref([])
const pacienteSeleccionado = ref(null)
const consultaExpandida = ref(null)
const notasPorPaciente = ref({})

onMounted(async () => {
  const res = await fetch(`${BASE_URL}/pacientes`, { headers: getHeaders() })
  if (res.ok) pacientes.value = await res.json()
})

const cargarNotas = async (pacId) => {
  if (notasPorPaciente.value[pacId]) return
  const res = await fetch(`${BASE_URL}/notas/paciente/${pacId}`, { headers: getHeaders() })
  if (res.ok) notasPorPaciente.value[pacId] = await res.json()
}

const seleccionarPaciente = async (p) => {
  pacienteSeleccionado.value = p
  consultaExpandida.value = null
  await cargarNotas(p.pacId)
}

const consultasDelPaciente = computed(() => {
  if (!pacienteSeleccionado.value) return []
  return notasPorPaciente.value[pacienteSeleccionado.value.pacId] ?? []
})

const pacientesFiltrados = computed(() => {
  const q = busqueda.value.toLowerCase()
  if (!q) return pacientes.value
  return pacientes.value.filter(p =>
    `${p.pacNombre} ${p.pacApePat}`.toLowerCase().includes(q)
  )
})

const toggleConsulta = (idx) => { consultaExpandida.value = consultaExpandida.value === idx ? null : idx }

const formatearFecha = (f) => {
  if (!f) return '—'
  return new Date(f).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
}

const inicialNombre = (nombre) => (nombre ?? '?').charAt(0).toUpperCase()
const colorSexo = (sexo) => sexo === 'Femenino' ? 'avatar-f' : 'avatar-m'
const cerrarSesion = () => { localStorage.clear(); router.push('/login') }
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand"><span class="logo-icon">+</span><h1>MediCita</h1></div>
      <nav class="menu-navegacion">
        <router-link to="/medico/inicio" class="enlace-menu"><span>🏠</span> Inicio</router-link>
        <router-link to="/medico/perfil" class="enlace-menu"><span>👤</span> Mi Perfil</router-link>
        <router-link to="/medico/agenda" class="enlace-menu"><span>📅</span> Mis Citas</router-link>
        <router-link to="/medico/historiales" class="enlace-menu activo"><span>📂</span> Historial Médico</router-link>
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera-medico">
        <h1 class="saludo-principal">📂 Historial Médico</h1>
        <span class="fecha-cabecera">{{ pacientes.length }} pacientes registrados</span>
      </div>

      <div class="layout-historial">
        <div class="panel-pacientes">
          <div class="barra-busqueda">
            <input v-model="busqueda" type="text" class="input-busqueda" placeholder="🔍 Buscar paciente..." />
          </div>
          <div class="lista-pacientes">
            <div v-for="paciente in pacientesFiltrados" :key="paciente.pacId"
              class="fila-paciente"
              :class="{ 'fila-activa': pacienteSeleccionado?.pacId === paciente.pacId }"
              @click="seleccionarPaciente(paciente)">
              <div :class="['avatar-paciente', colorSexo(paciente.pacSexo)]">{{ inicialNombre(paciente.pacNombre) }}</div>
              <div class="info-paciente-fila">
                <p class="nombre-paciente-lista">{{ paciente.pacNombre }} {{ paciente.pacApePat }}</p>
                <p class="meta-paciente">{{ formatearFecha(paciente.pacFechaNac) }}</p>
              </div>
              <span class="conteo-consultas">{{ (notasPorPaciente[paciente.pacId] ?? []).length }}</span>
            </div>
            <div v-if="pacientesFiltrados.length === 0" class="sin-resultados"><p>No se encontraron pacientes</p></div>
          </div>
        </div>

        <div class="panel-detalle">
          <div v-if="!pacienteSeleccionado" class="estado-vacio">
            <div class="icono-vacio">📋</div>
            <p class="titulo-vacio">Selecciona un paciente</p>
            <p class="subtitulo-vacio">Haz clic en un paciente para ver su historial</p>
          </div>

          <div v-else>
            <div class="tarjeta-encabezado-paciente">
              <div :class="['avatar-grande', colorSexo(pacienteSeleccionado.pacSexo)]">{{ inicialNombre(pacienteSeleccionado.pacNombre) }}</div>
              <div class="datos-encabezado">
                <h2 class="nombre-detalle">{{ pacienteSeleccionado.pacNombre }} {{ pacienteSeleccionado.pacApePat }}</h2>
                <div class="badges-datos">
                  <span class="badge-dato">{{ pacienteSeleccionado.pacSexo }}</span>
                </div>
                <p class="ultima-visita-texto">Nacimiento: {{ formatearFecha(pacienteSeleccionado.pacFechaNac) }}</p>
              </div>
              <div class="resumen-consultas">
                <span class="numero-resumen">{{ consultasDelPaciente.length }}</span>
                <span class="label-resumen">consulta(s)</span>
              </div>
            </div>

            <div class="seccion-historial">
              <h3 class="titulo-historial">Historial de consultas</h3>
              <div class="linea-tiempo">
                <div v-for="(consulta, idx) in consultasDelPaciente" :key="idx" class="nodo-consulta">
                  <div class="punto-linea"></div>
                  <div class="tarjeta-consulta" :class="{ 'tarjeta-expandida': consultaExpandida === idx }">
                    <div class="cabecera-consulta" @click="toggleConsulta(idx)">
                      <div class="info-consulta-header">
                        <span class="fecha-consulta">{{ formatearFecha(consulta.notaFechaCreacion) }}</span>
                        <p class="motivo-consulta">{{ consulta.notaDiagnostico }}</p>
                      </div>
                      <div class="acciones-consulta">
                        <span class="chevron">{{ consultaExpandida === idx ? '▲' : '▼' }}</span>
                      </div>
                    </div>
                    <div v-if="consultaExpandida === idx" class="cuerpo-consulta">
                      <div class="dato-consulta">
                        <span class="etiqueta-dato">💊 Receta</span>
                        <p class="valor-dato">{{ consulta.notaReceta || '—' }}</p>
                      </div>
                      <div class="dato-consulta">
                        <span class="etiqueta-dato">📝 Diagnóstico</span>
                        <p class="valor-dato">{{ consulta.notaDiagnostico || '—' }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="consultasDelPaciente.length === 0" class="sin-resultados">Sin consultas registradas</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout {
  display: flex; min-height: 100vh; background-color: #f8fafc;
}
.sidebar-izquierdo {
  width: 260px; background-color: #ffffff; border-right: 1px solid #e2e8f0;
  display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center;
  justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; letter-spacing: -0.5px; }
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
  flex-grow: 1; padding: 24px; background-color: #f8fafc; box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.cabecera-medico {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;
}
.saludo-principal { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
.fecha-cabecera {
  font-size: 14px; color: #64748b; background: white;
  padding: 6px 12px; border-radius: 20px; border: 1px solid #e2e8f0;
}

.layout-historial { display: grid; grid-template-columns: 320px 1fr; gap: 20px; align-items: start; }

/* Panel pacientes */
.panel-pacientes {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.barra-busqueda { padding: 14px; border-bottom: 1px solid #f1f5f9; }
.input-busqueda {
  width: 100%; box-sizing: border-box; padding: 9px 14px; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 14px; color: #1e293b; outline: none;
  font-family: inherit; background: #f8fafc;
}
.input-busqueda:focus { border-color: #0d8a72; background: white; }

.lista-pacientes { max-height: 70vh; overflow-y: auto; }
.fila-paciente {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  cursor: pointer; border-bottom: 1px solid #f8fafc; transition: background 0.15s;
}
.fila-paciente:hover { background: #f8fafc; }
.fila-activa { background: #e6f4f1 !important; }
.avatar-paciente {
  width: 42px; height: 42px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-size: 16px;
  font-weight: 700; flex-shrink: 0; color: white;
}
.avatar-f { background: linear-gradient(135deg, #ec4899, #f43f5e); }
.avatar-m { background: linear-gradient(135deg, #3b82f6, #6366f1); }

.info-paciente-fila { flex-grow: 1; min-width: 0; }
.nombre-paciente-lista { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 2px 0; }
.meta-paciente { font-size: 11px; color: #94a3b8; margin: 0 0 2px 0; }
.diagnostico-lista { font-size: 12px; color: #0d8a72; font-weight: 600; margin: 0; }

.conteo-consultas {
  width: 24px; height: 24px; background: #f1f5f9; border-radius: 20px;
  font-size: 12px; font-weight: 700; color: #475569;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.sin-resultados { text-align: center; padding: 32px; font-size: 14px; color: #94a3b8; }

/* Panel detalle */
.panel-detalle { display: flex; flex-direction: column; gap: 16px; }

.estado-vacio {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 80px 20px; text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.icono-vacio { font-size: 48px; margin-bottom: 12px; opacity: 0.4; }
.titulo-vacio { font-size: 16px; font-weight: 700; color: #475569; margin: 0 0 6px 0; }
.subtitulo-vacio { font-size: 13px; color: #94a3b8; margin: 0; }

.tarjeta-encabezado-paciente {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 20px; display: flex; align-items: center; gap: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.avatar-grande {
  width: 60px; height: 60px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-size: 24px;
  font-weight: 700; flex-shrink: 0; color: white;
}
.datos-encabezado { flex-grow: 1; }
.nombre-detalle { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
.badges-datos { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 6px; }
.badge-dato {
  background: #f1f5f9; color: #475569; font-size: 12px;
  font-weight: 600; padding: 3px 10px; border-radius: 20px;
}
.diagnostico-badge { background: #e6f4f1; color: #0d8a72; }
.ultima-visita-texto { font-size: 12px; color: #94a3b8; margin: 0; }
.resumen-consultas {
  display: flex; flex-direction: column; align-items: center;
  background: #f0fdfa; border-radius: 10px; padding: 12px 16px; flex-shrink: 0;
}
.numero-resumen { font-size: 28px; font-weight: 800; color: #0d8a72; line-height: 1; }
.label-resumen { font-size: 12px; color: #0d8a72; font-weight: 600; }

.seccion-historial {
  background: white; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.titulo-historial { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 20px 0; }

.linea-tiempo { display: flex; flex-direction: column; gap: 0; }
.nodo-consulta { display: flex; gap: 16px; position: relative; }
.nodo-consulta:not(:last-child)::before {
  content: ''; position: absolute; left: 7px; top: 16px; bottom: -12px;
  width: 2px; background: #e2e8f0;
}
.punto-linea {
  width: 16px; height: 16px; border-radius: 50%; background: #0d8a72;
  flex-shrink: 0; margin-top: 12px; border: 2px solid white;
  box-shadow: 0 0 0 2px #0d8a72; z-index: 1;
}
.tarjeta-consulta {
  flex-grow: 1; border: 1px solid #f1f5f9; border-radius: 10px;
  margin-bottom: 12px; overflow: hidden; transition: border-color 0.2s;
}
.tarjeta-consulta:hover { border-color: #cbd5e1; }
.tarjeta-expandida { border-color: #0d8a72; }

.cabecera-consulta {
  display: flex; justify-content: space-between; align-items: flex-start;
  padding: 14px 16px; cursor: pointer; gap: 12px;
}
.info-consulta-header { flex-grow: 1; }
.fecha-consulta { font-size: 12px; font-weight: 700; color: #0d8a72; display: block; margin-bottom: 3px; }
.motivo-consulta { font-size: 14px; font-weight: 600; color: #1e293b; margin: 0; }
.acciones-consulta { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.badge-diagnostico {
  background: #f1f5f9; color: #475569; font-size: 11px;
  font-weight: 600; padding: 3px 8px; border-radius: 4px; max-width: 160px;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.chevron { font-size: 10px; color: #94a3b8; }

.cuerpo-consulta {
  padding: 0 16px 16px;
  border-top: 1px solid #f1f5f9;
  display: flex; flex-direction: column; gap: 12px; padding-top: 14px;
}
.dato-consulta { display: flex; flex-direction: column; gap: 4px; }
.etiqueta-dato { font-size: 12px; font-weight: 700; color: #64748b; }
.valor-dato { font-size: 14px; color: #1e293b; margin: 0; line-height: 1.5; }

@media (max-width: 900px) {
  .layout-historial { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
}
</style>
