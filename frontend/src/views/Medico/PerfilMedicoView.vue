<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const modoEdicion = ref(false)
const guardando = ref(false)
const mensajeExito = ref('')

const perfil = ref({
  nombre: '',
  especialidad: '',
  cedula: '',
  telefono: '',
  email: '',
  consultorio: '',
  horarioInicio: '08:00',
  horarioFin: '17:00',
  descripcion: ''
})

const perfilOriginal = ref({})

onMounted(() => {
  const nombre = localStorage.getItem('usuarioNombre') || 'Médico'
  const email = localStorage.getItem('usuarioEmail') || ''
  const perfilGuardado = localStorage.getItem('perfilMedico')

  if (perfilGuardado) {
    perfil.value = JSON.parse(perfilGuardado)
  } else {
    perfil.value.nombre = nombre
    perfil.value.email = email
  }
})

const activarEdicion = () => {
  perfilOriginal.value = { ...perfil.value }
  modoEdicion.value = true
}

const cancelarEdicion = () => {
  perfil.value = { ...perfilOriginal.value }
  modoEdicion.value = false
}

const guardarPerfil = () => {
  guardando.value = true
  setTimeout(() => {
    localStorage.setItem('perfilMedico', JSON.stringify(perfil.value))
    localStorage.setItem('usuarioNombre', perfil.value.nombre)
    guardando.value = false
    modoEdicion.value = false
    mensajeExito.value = 'Perfil actualizado correctamente'
    setTimeout(() => { mensajeExito.value = '' }, 3000)
  }, 600)
}

const cerrarSesion = () => {
  localStorage.clear()
  router.push('/login')
}

const inicialNombre = () => {
  return perfil.value.nombre ? perfil.value.nombre.charAt(0).toUpperCase() : 'M'
}
</script>

<template>
  <div class="pantalla-layout">

    <aside class="sidebar-izquierdo">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>

      <nav class="menu-navegacion">
        <router-link to="/medico/inicio" class="enlace-menu">
          <span class="icono">🏠</span> Inicio
        </router-link>
        <router-link to="/medico/perfil" class="enlace-menu activo">
          <span class="icono">👤</span> Mi Perfil
        </router-link>
        <router-link to="/medico/agenda" class="enlace-menu">
          <span class="icono">📅</span> Mis Citas
        </router-link>
        <router-link to="/medico/historiales" class="enlace-menu">
          <span class="icono">📂</span> Historial Médico
        </router-link>
      </nav>

      <div class="sidebar-pie">
        <button @click="cerrarSesion" class="btn-cerrar-sesion">
          🚪 Cerrar Sesión
        </button>
      </div>
    </aside>

    <div class="contenedor-dashboard">

      <div class="cabecera-medico">
        <h1 class="saludo-principal">Mi Perfil</h1>
        <span class="fecha-cabecera">Información profesional</span>
      </div>

      <!-- Mensaje de éxito -->
      <div v-if="mensajeExito" class="alerta-exito">
        ✅ {{ mensajeExito }}
      </div>

      <div class="layout-perfil">

        <!-- Tarjeta de avatar -->
        <div class="tarjeta-avatar">
          <div class="circulo-avatar">{{ inicialNombre() }}</div>
          <h2 class="nombre-display">Dr. {{ perfil.nombre || 'Sin nombre' }}</h2>
          <p class="especialidad-display">{{ perfil.especialidad || 'Sin especialidad' }}</p>
          <div class="badge-cedula" v-if="perfil.cedula">
            📋 Cédula: {{ perfil.cedula }}
          </div>
          <div class="horario-display" v-if="perfil.horarioInicio && perfil.horarioFin">
            🕐 {{ perfil.horarioInicio }} – {{ perfil.horarioFin }}
          </div>
        </div>

        <!-- Formulario de datos -->
        <div class="tarjeta-formulario">
          <div class="encabezado-form">
            <h2 class="subtitulo-seccion">Datos del médico</h2>
            <div class="botones-accion" v-if="!modoEdicion">
              <button @click="activarEdicion" class="btn-editar">✏️ Editar perfil</button>
            </div>
            <div class="botones-accion" v-else>
              <button @click="cancelarEdicion" class="btn-cancelar">Cancelar</button>
              <button @click="guardarPerfil" class="btn-guardar" :disabled="guardando">
                {{ guardando ? 'Guardando...' : '💾 Guardar cambios' }}
              </button>
            </div>
          </div>

          <div class="grilla-campos">
            <div class="campo-grupo">
              <label class="etiqueta-campo">Nombre completo</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.nombre"
                type="text"
                class="input-campo"
                placeholder="Ej. Juan García López"
              />
              <p v-else class="valor-campo">{{ perfil.nombre || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Especialidad</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.especialidad"
                type="text"
                class="input-campo"
                placeholder="Ej. Cardiología"
              />
              <p v-else class="valor-campo">{{ perfil.especialidad || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Cédula profesional</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.cedula"
                type="text"
                class="input-campo"
                placeholder="Ej. 1234567"
              />
              <p v-else class="valor-campo">{{ perfil.cedula || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Teléfono de contacto</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.telefono"
                type="tel"
                class="input-campo"
                placeholder="Ej. 442 123 4567"
              />
              <p v-else class="valor-campo">{{ perfil.telefono || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Correo electrónico</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.email"
                type="email"
                class="input-campo"
                placeholder="correo@hospital.com"
              />
              <p v-else class="valor-campo">{{ perfil.email || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Consultorio / Área</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.consultorio"
                type="text"
                class="input-campo"
                placeholder="Ej. Piso 3, Consultorio 12"
              />
              <p v-else class="valor-campo">{{ perfil.consultorio || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Horario inicio</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.horarioInicio"
                type="time"
                class="input-campo"
              />
              <p v-else class="valor-campo">{{ perfil.horarioInicio || '—' }}</p>
            </div>

            <div class="campo-grupo">
              <label class="etiqueta-campo">Horario fin</label>
              <input
                v-if="modoEdicion"
                v-model="perfil.horarioFin"
                type="time"
                class="input-campo"
              />
              <p v-else class="valor-campo">{{ perfil.horarioFin || '—' }}</p>
            </div>

            <div class="campo-grupo campo-ancho-completo">
              <label class="etiqueta-campo">Descripción / Notas profesionales</label>
              <textarea
                v-if="modoEdicion"
                v-model="perfil.descripcion"
                class="input-campo textarea-campo"
                placeholder="Breve descripción profesional, especialidades adicionales, etc."
                rows="3"
              ></textarea>
              <p v-else class="valor-campo">{{ perfil.descripcion || '—' }}</p>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.pantalla-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}
.sidebar-izquierdo {
  width: 260px;
  background-color: #ffffff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  box-sizing: border-box;
  flex-shrink: 0;
}
.brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 2.5rem;
}
.logo-icon {
  background-color: #0d8a72;
  color: white;
  font-weight: bold;
  font-size: 1.3rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}
.brand h1 {
  color: #0d8a72;
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: -0.5px;
}
.menu-navegacion {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex-grow: 1;
}
.enlace-menu {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.85rem 1rem;
  color: #64748b;
  text-decoration: none;
  font-weight: 600;
  border-radius: 10px;
  transition: all 0.2s;
  font-size: 0.95rem;
}
.enlace-menu:hover { background-color: #f1f5f9; color: #1e293b; }
.enlace-menu.activo { background-color: #e6f4f1; color: #0d8a72; }
.sidebar-pie { margin-top: auto; }
.btn-cerrar-sesion {
  width: 100%;
  background: none;
  border: none;
  color: #b45309;
  padding: 0.85rem 1rem;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  text-align: left;
  border-radius: 10px;
  transition: background-color 0.2s;
}
.btn-cerrar-sesion:hover { background-color: #fef3c7; }
.contenedor-dashboard {
  flex-grow: 1;
  padding: 24px;
  background-color: #f8fafc;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.cabecera-medico {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 16px;
}
.saludo-principal {
  font-size: 26px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.fecha-cabecera {
  font-size: 14px;
  color: #64748b;
  background: white;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
}
.alerta-exito {
  background: #f0fdfa;
  border: 1px solid #6ee7b7;
  color: #065f46;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 600;
  font-size: 14px;
}
.layout-perfil {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 24px;
  align-items: start;
}
.tarjeta-avatar {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 28px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 10px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.circulo-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0d8a72, #10b981);
  color: white;
  font-size: 2rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}
.nombre-display {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.especialidad-display {
  font-size: 13px;
  color: #0d8a72;
  font-weight: 600;
  margin: 0;
}
.badge-cedula {
  background: #f1f5f9;
  border-radius: 20px;
  padding: 4px 12px;
  font-size: 12px;
  color: #475569;
  font-weight: 500;
}
.horario-display {
  font-size: 12px;
  color: #64748b;
  background: #f8fafc;
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
}
.tarjeta-formulario {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.encabezado-form {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.subtitulo-seccion {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.botones-accion {
  display: flex;
  gap: 8px;
}
.btn-editar {
  background: #f0fdfa;
  border: 1px solid #ccfbf1;
  color: #0d8a72;
  font-weight: 600;
  font-size: 14px;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-editar:hover { background: #ccfbf1; }
.btn-cancelar {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #64748b;
  font-weight: 600;
  font-size: 14px;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancelar:hover { background: #e2e8f0; }
.btn-guardar {
  background: #0d8a72;
  border: none;
  color: white;
  font-weight: 600;
  font-size: 14px;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-guardar:hover:not(:disabled) { background: #0a7060; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }
.grilla-campos {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.campo-grupo { display: flex; flex-direction: column; gap: 6px; }
.campo-ancho-completo { grid-column: 1 / -1; }
.etiqueta-campo {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.input-campo {
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  background: #f8fafc;
  transition: border-color 0.2s;
  font-family: inherit;
  outline: none;
}
.input-campo:focus { border-color: #0d8a72; background: white; }
.textarea-campo { resize: vertical; }
.valor-campo {
  font-size: 15px;
  color: #1e293b;
  font-weight: 500;
  margin: 0;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

@media (max-width: 900px) {
  .layout-perfil { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
  .grilla-campos { grid-template-columns: 1fr; }
}
</style>
