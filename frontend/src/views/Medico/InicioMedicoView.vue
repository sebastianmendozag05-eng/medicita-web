<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const medicoNombre = ref('Médico')
const fechaActual = ref('')

const citasHoy = ref([])

const estadisticas = ref({
  citasHoy: 0,
  citasSemana: 0,
  citasMes: 0
})

const fmt = (d) => d.toISOString().split('T')[0]

const cargarDashboard = async () => {
  const medId = localStorage.getItem('medicoId')
  if (!medId) return

  const hoy = new Date()
  const inicioSemana = new Date(hoy)
  inicioSemana.setDate(hoy.getDate() - hoy.getDay())
  const finSemana = new Date(inicioSemana)
  finSemana.setDate(inicioSemana.getDate() + 6)
  const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1)
  const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0)

  try {
    const [resHoy, resSemana, resMes] = await Promise.all([
      fetch(`${BASE_URL}/agenda/medico/${medId}?fecha=${fmt(hoy)}`, { headers: getHeaders() }),
      fetch(`${BASE_URL}/agenda/medico/${medId}/semana?inicio=${fmt(inicioSemana)}&fin=${fmt(finSemana)}`, { headers: getHeaders() }),
      fetch(`${BASE_URL}/agenda/medico/${medId}/semana?inicio=${fmt(inicioMes)}&fin=${fmt(finMes)}`, { headers: getHeaders() })
    ])

    if (resHoy.ok) {
      const citas = await resHoy.json()
      citasHoy.value = citas.map(c => ({
        id: c.citId,
        hora: c.citHora?.substring(0, 5),
        paciente: `${c.paciente?.pacNombre ?? ''} ${c.paciente?.pacApePat ?? ''}`,
        detalle: c.citMotivo
      }))
      estadisticas.value.citasHoy = citas.length
    }
    if (resSemana.ok) {
      const data = await resSemana.json()
      estadisticas.value.citasSemana = data.citas?.length ?? 0
    }
    if (resMes.ok) {
      const data = await resMes.json()
      estadisticas.value.citasMes = data.citas?.length ?? 0
    }
  } catch { /* silencioso */ }
}

onMounted(() => {
  const nombreGuardado = localStorage.getItem('usuarioNombre')
  if (nombreGuardado && nombreGuardado !== 'Medico' && nombreGuardado !== 'M') {
    medicoNombre.value = nombreGuardado
  }

  const opciones = { day: 'numeric', month: 'long', year: 'numeric' }
  fechaActual.value = new Date().toLocaleDateString('es-ES', opciones)

  cargarDashboard()
})

const cerrarSesion = () => {
  localStorage.clear()
  router.push('/login')
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
        <router-link to="/medico/inicio" class="enlace-menu activo">
          <span class="icono">🏠</span> Inicio
        </router-link>
        <router-link to="/medico/perfil" class="enlace-menu">
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
        <h1 class="saludo-principal">Bienvenido, Dr. {{ medicoNombre }} 👋</h1>
        <span class="fecha-cabecera">{{ fechaActual }}</span>
      </div>

      <div class="distribucion-paneles">
        
        <div class="bloque-principal-citas">
          <h2 class="subtitulo-seccion">Citas de hoy</h2>
          
          <div v-if="citasHoy.length > 0" class="tabla-citas">
            <div v-for="cita in citasHoy" :key="cita.id" class="fila-cita">
              <div class="celda-hora">{{ cita.hora }}</div>
              <div class="celda-detalles">
                <span class="paciente-nombre">{{ cita.paciente }}</span>
                <span class="paciente-motivo">{{ cita.detalle }}</span>
              </div>
            </div>
          </div>

          <div v-else class="contenedor-sin-citas">
            <div class="icono-calendario-vacio">📅</div>
            <p class="texto-sin-citas">No tienes citas programadas por el momento.</p>
          </div>

          <button class="btn-agenda-enlace">Ver agenda completa</button>
        </div>

        <div class="bloque-lateral-stats">
          <h2 class="subtitulo-seccion">Estadísticas</h2>
          
          <div class="tarjetero-stats">
            <div class="tarjeta-mini-stat">
              <div class="icono-cuadrado color-azul">📋</div>
              <div class="info-stat-num">
                <span class="numero-stat">{{ estadisticas.citasHoy }}</span>
                <span class="leyenda-stat">Citas hoy</span>
              </div>
            </div>

            <div class="tarjeta-mini-stat">
              <div class="icono-cuadrado color-verde">🏥</div>
              <div class="info-stat-num">
                <span class="numero-stat">{{ estadisticas.citasSemana }}</span>
                <span class="leyenda-stat">Citas esta semana</span>
              </div>
            </div>

            <div class="tarjeta-mini-stat">
              <div class="icono-cuadrado color-morado">🕒</div>
              <div class="info-stat-num">
                <span class="numero-stat">{{ estadisticas.citasMes }}</span>
                <span class="leyenda-stat">Citas este mes</span>
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
.enlace-menu:hover {
  background-color: #f1f5f9;
  color: #1e293b;
}
.enlace-menu.activo {
  background-color: #e6f4f1;
  color: #0d8a72;
}
.sidebar-pie {
  margin-top: auto;
}
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
.btn-cerrar-sesion:hover {
  background-color: #fef3c7;
}
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
.distribucion-paneles {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 24px;
}
.bloque-principal-citas, .bloque-lateral-stats {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.subtitulo-seccion {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin-top: 0;
  margin-bottom: 20px;
}
.contenedor-sin-citas {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}
.icono-calendario-vacio {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.7;
}
.texto-sin-citas {
  font-size: 15px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}
.fila-cita {
  display: flex;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #f1f5f9;
}
.fila-cita:last-child {
  border-bottom: none;
}
.celda-hora {
  width: 100px;
  font-size: 13px;
  font-weight: 600;
  color: #0d8a72;
}
.celda-detalles {
  display: flex;
  flex-direction: column;
}
.paciente-nombre {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  display: block;
}
.paciente-motivo {
  font-size: 13px;
  color: #64748b;
  margin-top: 2px;
}
.btn-agenda-enlace {
  width: 100%;
  margin-top: 16px;
  background: #f0fdfa;
  border: 1px solid #ccfbf1;
  color: #0d8a72;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  padding: 10px;
  border-radius: 8px;
  transition: all 0.2s;
}
.btn-agenda-enlace:hover {
  background: #ccfbf1;
}
.tarjetero-stats {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.tarjeta-mini-stat {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #f1f5f9;
}
.icono-cuadrado {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}
.color-azul { background-color: #ecfeff; }
.color-verde { background-color: #f0fdfa; }
.color-morado { background-color: #f5f3ff; }
.info-stat-num {
  display: flex;
  flex-direction: column;
}
.numero-stat {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1;
}
.leyenda-stat {
  font-size: 13px;
  color: #64748b;
  margin-top: 4px;
}
@media (max-width: 768px) {
  .distribucion-paneles { grid-template-columns: 1fr; }
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
}
</style>