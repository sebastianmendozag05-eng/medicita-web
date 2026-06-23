<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const periodoSeleccionado = ref('hoy')

const periodos = [
  { value: 'hoy', label: 'Hoy' },
  { value: 'semana', label: 'Esta semana' },
  { value: 'mes', label: 'Este mes' },
]

const datosReporte = {
  hoy: {
    totalCitas: 35, completadas: 18, canceladas: 3, pendientes: 6,
    porMedico: [
      { nombre: 'Dr. Ramírez', citas: 12, completadas: 8 },
      { nombre: 'Dra. López',  citas: 10, completadas: 5 },
      { nombre: 'Dr. Torres',  citas: 8,  completadas: 3 },
      { nombre: 'Dra. Vega',   citas: 5,  completadas: 2 },
    ],
    porHora: [
      { hora: '08:00', cantidad: 4 }, { hora: '09:00', cantidad: 7 },
      { hora: '10:00', cantidad: 9 }, { hora: '11:00', cantidad: 8 },
      { hora: '12:00', cantidad: 4 }, { hora: '13:00', cantidad: 3 },
    ]
  },
  semana: {
    totalCitas: 187, completadas: 142, canceladas: 15, pendientes: 30,
    porMedico: [
      { nombre: 'Dr. Ramírez', citas: 60, completadas: 48 },
      { nombre: 'Dra. López',  citas: 55, completadas: 42 },
      { nombre: 'Dr. Torres',  citas: 42, completadas: 32 },
      { nombre: 'Dra. Vega',   citas: 30, completadas: 20 },
    ],
    porHora: [
      { hora: 'Lun', cantidad: 38 }, { hora: 'Mar', cantidad: 42 },
      { hora: 'Mié', cantidad: 35 }, { hora: 'Jue', cantidad: 40 },
      { hora: 'Vie', cantidad: 32 },
    ]
  },
  mes: {
    totalCitas: 820, completadas: 634, canceladas: 58, pendientes: 128,
    porMedico: [
      { nombre: 'Dr. Ramírez', citas: 260, completadas: 210 },
      { nombre: 'Dra. López',  citas: 240, completadas: 185 },
      { nombre: 'Dr. Torres',  citas: 190, completadas: 145 },
      { nombre: 'Dra. Vega',   citas: 130, completadas: 94  },
    ],
    porHora: [
      { hora: 'Sem 1', cantidad: 198 }, { hora: 'Sem 2', cantidad: 215 },
      { hora: 'Sem 3', cantidad: 220 }, { hora: 'Sem 4', cantidad: 187 },
    ]
  }
}

const datos = computed(() => datosReporte[periodoSeleccionado.value])
const maxCantidad = computed(() => Math.max(...datos.value.porHora.map(h => h.cantidad)))
const porcentajeCompletadas = computed(() => Math.round((datos.value.completadas / datos.value.totalCitas) * 100))
const barraAncho = (citas, total) => Math.round((citas / total) * 100) + '%'

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
        <router-link to="/recepcionista/reportes"  class="enlace-menu activo"><span>📊</span> Reportes</router-link>
      </nav>
      <div class="sidebar-pie"><button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button></div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera">
        <h1 class="saludo-principal">📊 Reportes</h1>
        <div class="selector-periodo">
          <button v-for="p in periodos" :key="p.value" :class="['btn-periodo', { activo: periodoSeleccionado === p.value }]" @click="periodoSeleccionado = p.value">{{ p.label }}</button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grilla-stats">
        <div class="tarjeta-stat borde-azul">
          <div class="stat-icono">📅</div>
          <div class="stat-info"><span class="stat-numero">{{ datos.totalCitas }}</span><span class="stat-label">Total de citas</span></div>
        </div>
        <div class="tarjeta-stat borde-verde">
          <div class="stat-icono">✅</div>
          <div class="stat-info"><span class="stat-numero">{{ datos.completadas }}</span><span class="stat-label">Completadas</span></div>
        </div>
        <div class="tarjeta-stat borde-rojo">
          <div class="stat-icono">✕</div>
          <div class="stat-info"><span class="stat-numero">{{ datos.canceladas }}</span><span class="stat-label">Canceladas</span></div>
        </div>
        <div class="tarjeta-stat borde-amarillo">
          <div class="stat-icono">⏳</div>
          <div class="stat-info"><span class="stat-numero">{{ datos.pendientes }}</span><span class="stat-label">Pendientes</span></div>
        </div>
      </div>

      <div class="grilla-paneles">
        <!-- Barras -->
        <div class="tarjeta-panel">
          <h2 class="titulo-panel">{{ periodoSeleccionado === 'hoy' ? 'Citas por hora' : periodoSeleccionado === 'semana' ? 'Citas por día' : 'Citas por semana' }}</h2>
          <div class="grafica-barras">
            <div v-for="item in datos.porHora" :key="item.hora" class="barra-col">
              <span class="barra-valor">{{ item.cantidad }}</span>
              <div class="barra-fondo">
                <div class="barra-fill" :style="{ height: Math.round((item.cantidad / maxCantidad) * 100) + '%' }"></div>
              </div>
              <span class="barra-label">{{ item.hora }}</span>
            </div>
          </div>
        </div>

        <!-- Círculo -->
        <div class="tarjeta-panel">
          <h2 class="titulo-panel">Tasa de completadas</h2>
          <div class="circulo-progreso-wrap">
            <div class="circulo-exterior">
              <svg viewBox="0 0 100 100" class="svg-circulo">
                <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                <circle cx="50" cy="50" r="40" fill="none" stroke="#0d8a72" stroke-width="10"
                  stroke-linecap="round"
                  :stroke-dasharray="`${porcentajeCompletadas * 2.51} 251`"
                  stroke-dashoffset="62.75"
                  transform="rotate(-90 50 50)"
                />
              </svg>
              <div class="porcentaje-centro">
                <span class="num-porcentaje">{{ porcentajeCompletadas }}%</span>
                <span class="label-porcentaje">completadas</span>
              </div>
            </div>
            <div class="leyenda-circulo">
              <div class="leyenda-item"><div class="punto-leyenda verde"></div><span>Completadas: {{ datos.completadas }}</span></div>
              <div class="leyenda-item"><div class="punto-leyenda rojo"></div><span>Canceladas: {{ datos.canceladas }}</span></div>
              <div class="leyenda-item"><div class="punto-leyenda gris"></div><span>Pendientes: {{ datos.pendientes }}</span></div>
            </div>
          </div>
        </div>

        <!-- Tabla médicos -->
        <div class="tarjeta-panel panel-ancho">
          <h2 class="titulo-panel">Citas por médico</h2>
          <div class="tabla-medicos">
            <div class="fila-enc-m"><span>Médico</span><span>Total</span><span>Completadas</span><span>Progreso</span></div>
            <div v-for="m in datos.porMedico" :key="m.nombre" class="fila-medico">
              <span class="nombre-medico">{{ m.nombre }}</span>
              <span class="dato-medico">{{ m.citas }}</span>
              <span class="dato-medico">{{ m.completadas }}</span>
              <div class="barra-medico-wrap">
                <div class="barra-medico-fondo"><div class="barra-medico-fill" :style="{ width: barraAncho(m.completadas, m.citas) }"></div></div>
                <span class="pct-medico">{{ Math.round((m.completadas / m.citas) * 100) }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background: #f8fafc; }
.sidebar-izquierdo { width: 240px; background: #fff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0; }
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
.selector-periodo { display: flex; gap: 4px; background: #f1f5f9; border-radius: 10px; padding: 4px; }
.btn-periodo { padding: 7px 16px; border: none; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; background: transparent; color: #64748b; transition: all 0.2s; }
.btn-periodo.activo { background: white; color: #0d8a72; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
.grilla-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.tarjeta-stat { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; }
.stat-icono { font-size: 26px; }
.stat-info { display: flex; flex-direction: column; }
.stat-numero { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 4px; }
.borde-azul { border-left: 4px solid #3b82f6; }
.borde-verde { border-left: 4px solid #10b981; }
.borde-rojo { border-left: 4px solid #ef4444; }
.borde-amarillo { border-left: 4px solid #f59e0b; }
.grilla-paneles { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.tarjeta-panel { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; }
.panel-ancho { grid-column: 1 / -1; }
.titulo-panel { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 20px; }
.grafica-barras { display: flex; align-items: flex-end; gap: 12px; height: 160px; padding-bottom: 28px; }
.barra-col { display: flex; flex-direction: column; align-items: center; flex: 1; height: 100%; justify-content: flex-end; gap: 4px; }
.barra-valor { font-size: 12px; font-weight: 700; color: #475569; }
.barra-fondo { width: 100%; flex-grow: 1; background: #f1f5f9; border-radius: 6px 6px 0 0; display: flex; align-items: flex-end; overflow: hidden; max-height: 120px; }
.barra-fill { width: 100%; background: linear-gradient(180deg, #0d8a72, #10b981); border-radius: 6px 6px 0 0; transition: height 0.4s ease; min-height: 4px; }
.barra-label { font-size: 11px; color: #94a3b8; font-weight: 600; white-space: nowrap; }
.circulo-progreso-wrap { display: flex; align-items: center; gap: 28px; }
.circulo-exterior { position: relative; width: 140px; height: 140px; flex-shrink: 0; }
.svg-circulo { width: 100%; height: 100%; }
.porcentaje-centro { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.num-porcentaje { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1; }
.label-porcentaje { font-size: 11px; color: #64748b; font-weight: 600; }
.leyenda-circulo { display: flex; flex-direction: column; gap: 10px; }
.leyenda-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; }
.punto-leyenda { width: 10px; height: 10px; border-radius: 50%; }
.punto-leyenda.verde { background: #10b981; }
.punto-leyenda.rojo { background: #ef4444; }
.punto-leyenda.gris { background: #cbd5e1; }
.tabla-medicos { display: flex; flex-direction: column; }
.fila-enc-m { display: grid; grid-template-columns: 1.5fr 1fr 1fr 2fr; padding: 8px 12px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; }
.fila-medico { display: grid; grid-template-columns: 1.5fr 1fr 1fr 2fr; padding: 14px 12px; border-bottom: 1px solid #f8fafc; align-items: center; }
.fila-medico:last-child { border-bottom: none; }
.nombre-medico { font-size: 14px; font-weight: 700; color: #1e293b; }
.dato-medico { font-size: 14px; color: #475569; font-weight: 600; }
.barra-medico-wrap { display: flex; align-items: center; gap: 10px; }
.barra-medico-fondo { flex-grow: 1; height: 8px; background: #f1f5f9; border-radius: 10px; overflow: hidden; }
.barra-medico-fill { height: 100%; background: #0d8a72; border-radius: 10px; transition: width 0.4s; }
.pct-medico { font-size: 12px; font-weight: 700; color: #0d8a72; min-width: 36px; }
@media (max-width: 1024px) { .grilla-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 900px) { .grilla-paneles { grid-template-columns: 1fr; } .panel-ancho { grid-column: 1; } }
@media (max-width: 768px) { .pantalla-layout { flex-direction: column; } .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; } }
</style>