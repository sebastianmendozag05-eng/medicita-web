<script setup>
import { ref, onMounted } from 'vue'

const medicoNombre = ref('Luis')
const fechaActual = ref('')

const citasHoy = ref([
  { id: 1, hora: '10:00 AM', paciente: 'María Geerdes', detalle: 'Control cardiológico' },
  { id: 2, hora: '11:00 AM', paciente: 'Juan Pérez', detalle: 'Primera consulta' },
  { id: 3, hora: '12:00 PM', paciente: 'Ana Klosit', detalle: 'Seguimiento' },
  { id: 4, hora: '02:00 PM', paciente: 'Carlos Méndez', detalle: 'Control anual' }
])

const estadisticas = ref({
  citasHoy: 12,
  citasSemana: 48,
  citasMes: 128
})

onMounted(() => {
  const opciones = { day: 'numeric', month: 'long', year: 'numeric' }
  fechaActual.value = new Date().toLocaleDateString('es-ES', opciones)
})
</script>

<template>
  <div class="contenedor-dashboard">
    
    <div class="cabecera-medico">
      <h1 class="saludo-principal">Bienvenido, Dr. {{ medicoNombre }} 👋</h1>
      <span class="fecha-cabecera">{{ fechaActual }}</span>
    </div>

    <div class="distribucion-paneles">
      
      <div class="bloque-principal-citas">
        <h2 class="subtitulo-seccion">Citas de hoy</h2>
        
        <div class="tabla-citas">
          <div v-for="cita in citasHoy" :key="cita.id" class="fila-cita">
            <div class="celda-hora">{{ cita.hora }}</div>
            <div class="celda-detalles">
              <span class="paciente-nombre">{{ cita.paciente }}</span>
              <span class="paciente-motivo">{{ cita.detalle }}</span>
            </div>
          </div>
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
</template>

<style scoped>
.contenedor-dashboard {
  padding: 24px;
  background-color: #f8fafc;
  min-height: 100vh;
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
}
</style>