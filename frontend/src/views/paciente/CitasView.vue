
<script setup>
import { ref, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'

const citas = ref([])
const cargandoCitas = ref(false)
const errorCarga = ref('')

const mostrarModal = ref(false)
const guardando = ref(false)

const nuevoMedicoId = ref('')
const nuevaEspecialidad = ref('Consulta General')
const nuevaFecha = ref('')
const nuevaHora = ref('')
const medicos = ref([])

const getToken = () => localStorage.getItem('token')

const headers = () => ({
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${getToken()}`
})

const cargarCitas = async () => {
  cargandoCitas.value = true
  errorCarga.value = ''
  try {
    const res = await fetch(`${BASE_URL}/citas`, { headers: headers() })
    if (!res.ok) throw new Error('Error al cargar citas')
    const data = await res.json()
    citas.value = Array.isArray(data) ? data : (data.data ?? [])
  } catch (err) {
    errorCarga.value = 'No se pudieron cargar las citas. Verifica tu conexión.'
  } finally {
    cargandoCitas.value = false
  }
}

const cargarMedicos = async () => {
  try {
    const res = await fetch(`${BASE_URL}/medicos`, { headers: headers() })
    if (!res.ok) return
    const data = await res.json()
    medicos.value = Array.isArray(data) ? data : (data.data ?? [])
  } catch { /* silencioso */ }
}

onMounted(() => {
  cargarCitas()
  cargarMedicos()
})

const abrirFormulario = () => { mostrarModal.value = true }

const cerrarFormulario = () => {
  mostrarModal.value = false
  nuevoMedicoId.value = ''
  nuevaEspecialidad.value = 'Consulta General'
  nuevaFecha.value = ''
  nuevaHora.value = ''
}

const guardarCita = async () => {
  if (!nuevoMedicoId.value || !nuevaFecha.value || !nuevaHora.value) {
    alert('Por favor, llena todos los campos.')
    return
  }

  guardando.value = true
  try {
    const pacIdRaw = localStorage.getItem('pacId')
    if (!pacIdRaw) {
      alert('Tu cuenta no está vinculada a un expediente de paciente. Contacta al administrador.')
      return
    }
    const pacId = parseInt(pacIdRaw)
    const res = await fetch(`${BASE_URL}/citas`, {
      method: 'POST',
      headers: headers(),
      body: JSON.stringify({
        medId: parseInt(nuevoMedicoId.value),
        pacId: pacId,
        citFecha: nuevaFecha.value,
        citHora: nuevaHora.value + ':00',
        citMotivo: nuevaEspecialidad.value
      })
    })

    if (!res.ok) {
      const err = await res.json()
      alert(err.message ?? 'Error al crear la cita.')
      return
    }

    await cargarCitas()
    cerrarFormulario()

  } catch {
    alert('No se pudo conectar al servidor.')
  } finally {
    guardando.value = false
  }
}

const eliminarCita = async (id) => {
  if (!confirm('¿Estás seguro de que deseas cancelar esta cita?')) return
  try {
    const res = await fetch(`${BASE_URL}/citas/${id}`, {
      method: 'DELETE',
      headers: headers()
    })
    if (res.ok) {
      citas.value = citas.value.filter(c => c.citId !== id)
    } else {
      alert('No se pudo cancelar la cita.')
    }
  } catch {
    alert('Error de conexión.')
  }
}

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return ''
  try {
    return new Date(fechaStr).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
  } catch { return fechaStr }
}

const formatearHora = (horaStr) => {
  if (!horaStr) return ''
  try {
    const [h, min] = horaStr.split(':')
    const sufijo = h >= 12 ? 'PM' : 'AM'
    return `${h % 12 || 12}:${min} ${sufijo}`
  } catch { return horaStr }
}
</script>
 
<template>
  <div class="contenedor-citas">
    
    <div class="encabezado-seccion">
      <h2 class="titulo-principal">Mis Citas Médicas</h2>
      <button @click="abrirFormulario" class="btn-agendar">
        + Agendar Nueva Cita
      </button>
    </div>

    <div v-if="cargandoCitas" class="tarjeta-vacia">
      <p class="texto-vacio-principal">Cargando citas...</p>
    </div>

    <div v-else-if="errorCarga" class="tarjeta-vacia error">
      <p class="texto-vacio-principal">⚠️ {{ errorCarga }}</p>
      <button @click="cargarCitas" class="btn-agendar" style="margin-top:12px">Reintentar</button>
    </div>

    <div v-else-if="citas.length === 0" class="tarjeta-vacia">
      <p class="texto-vacio-principal">Aún no tienes citas agendadas.</p>
      <p class="texto-vacio-secundario">Usa el botón superior para agendar una nueva consulta médica.</p>
    </div>

    <div v-else class="lista-tarjetas">
      <div v-for="cita in citas" :key="cita.citId" class="tarjeta-cita">
        
        <div class="bloque-izquierda">
          <div class="contenedor-icono-azul">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="icono-svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
          </div>
          
          <div class="textos-cita">
            <div class="linea-doctor">
              <h3 class="nombre-doctor">{{ cita.medico?.medNombre ?? 'Médico' }}</h3>
              <span class="separador-especialidad">— {{ cita.citMotivo }}</span>
            </div>
            <p class="fecha-cita">
              <span class="emoji-calendario">📅</span>
              {{ formatearFecha(cita.citFecha) }} a las {{ formatearHora(cita.citHora) }}
            </p>
          </div>
        </div>

        <div class="bloque-derecha">
          <span class="etiqueta-estado">
            <span class="punto-verde"></span>
            {{ cita.citEstatus ?? 'agendada' }}
          </span>
          <button @click="eliminarCita(cita.citId)" class="btn-cancelar">
            Cancelar
          </button>
        </div>

      </div>
    </div>

    <div v-if="mostrarModal" class="capa-modal">
      <div class="ventana-modal">
        <h3 class="modal-titulo">Agendar Nueva Cita</h3>
        
        <div class="formulario-cuerpo">

          <div class="campo-grupo">
            <label class="campo-etiqueta">Médico</label>
            <select v-model="nuevoMedicoId" class="campo-select">
              <option value="" disabled>Selecciona un médico</option>
              <option v-for="med in medicos" :key="med.medId" :value="med.medId">
                {{ med.medNombre }} {{ med.medApePat }}
              </option>
            </select>
          </div>

          <div class="campo-grupo">
            <label class="campo-etiqueta">Motivo</label>
            <select v-model="nuevaEspecialidad" class="campo-select">
              <option value="Consulta General">Consulta General</option>
              <option value="Cardiología">Cardiología</option>
              <option value="Dermatología">Dermatología</option>
              <option value="Pediatría">Pediatría</option>
            </select>
          </div>

          <div class="campo-fila-doble">
            <div class="campo-grupo">
              <label class="campo-etiqueta">Fecha</label>
              <input v-model="nuevaFecha" type="date" class="campo-input" />
            </div>
            <div class="campo-grupo">
              <label class="campo-etiqueta">Hora</label>
              <input v-model="nuevaHora" type="time" class="campo-input" />
            </div>
          </div>
        </div>

        <div class="modal-botones">
          <button @click="cerrarFormulario" class="btn-modal-cerrar">Cerrar</button>
          <button @click="guardarCita" :disabled="guardando" class="btn-modal-confirmar">
            {{ guardando ? 'Guardando...' : 'Confirmar Cita' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>
 
<style scoped>
.contenedor-citas { padding: 24px; max-width: 850px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.titulo-principal { font-size: 24px; font-weight: 600; color: #1e293b; margin: 0; }
.btn-agendar { background-color: #115e59; color: #ffffff; font-size: 14px; font-weight: 500; padding: 10px 18px; border: none; border-radius: 12px; cursor: pointer; transition: background-color 0.2s; }
.btn-agendar:hover { background-color: #0f524d; }
.tarjeta-vacia { background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px; text-align: center; }
.tarjeta-vacia.error { border-color: #fca5a5; background-color: #fef2f2; }
.texto-vacio-principal { color: #64748b; font-weight: 500; font-size: 15px; margin: 0 0 6px 0; }
.texto-vacio-secundario { color: #94a3b8; font-size: 13px; margin: 0; }
.lista-tarjetas { display: flex; flex-direction: column; gap: 16px; }
.tarjeta-cita { background-color: #ffffff; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease; }
.tarjeta-cita:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.bloque-izquierda { display: flex; align-items: center; gap: 16px; }
.contenedor-icono-azul { width: 42px; height: 42px; background-color: #ecfeff; color: #0891b2; border: 1px solid #cffafe; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.icono-svg { width: 20px; height: 20px; }
.textos-cita { display: flex; flex-direction: column; }
.linea-doctor { display: flex; align-items: baseline; gap: 8px; }
.nombre-doctor { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
.separador-especialidad { font-size: 13px; color: #64748b; }
.fecha-cita { font-size: 13px; color: #64748b; margin: 6px 0 0 0; display: flex; align-items: center; gap: 6px; }
.bloque-derecha { display: flex; align-items: center; gap: 16px; }
.etiqueta-estado { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #115e59; background-color: #f0fdfa; padding: 6px 12px; border-radius: 9999px; border: 1px solid #ccfbf1; }
.punto-verde { width: 6px; height: 6px; border-radius: 50%; background-color: #14b8a6; }
.btn-cancelar { background: none; border: none; color: #ef4444; font-size: 13px; font-weight: 500; cursor: pointer; padding: 4px 8px; }
.btn-cancelar:hover { text-decoration: underline; color: #dc2626; }
.capa-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.ventana-modal { background-color: #ffffff; border-radius: 16px; width: 100%; max-width: 400px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.modal-titulo { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0; }
.formulario-cuerpo { display: flex; flex-direction: column; gap: 14px; }
.campo-grupo { display: flex; flex-direction: column; gap: 4px; }
.campo-etiqueta { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.campo-input, .campo-select { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; color: #334155; background-color: #f8fafc; outline: none; }
.campo-input:focus, .campo-select:focus { border-color: #115e59; background-color: #ffffff; }
.campo-fila-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.modal-botones { display: flex; justify-content: flex-end; gap: 8px; margin-top: 24px; }
.btn-modal-cerrar { background: none; border: none; color: #64748b; font-size: 13px; font-weight: 500; padding: 8px 16px; cursor: pointer; border-radius: 8px; }
.btn-modal-cerrar:hover { background-color: #f1f5f9; }
.btn-modal-confirmar { background-color: #115e59; color: white; border: none; font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
.btn-modal-confirmar:hover:not(:disabled) { background-color: #0f524d; }
.btn-modal-confirmar:disabled { background-color: #94a3b8; cursor: not-allowed; }
</style>