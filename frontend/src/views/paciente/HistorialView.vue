<script setup>
import { ref } from 'vue'

// La lista empieza completamente vacía. No se mostrará nada hasta que tú cargues datos reales.
const historial = ref([])

// Función por si en el futuro quieres programar la acción de ver detalles
const verDetalle = (id) => {
  console.log('Mostrando detalle del registro:', id)
}
</script>

<template>
  <div class="contenedor-historial">
    <h2 class="titulo-seccion">Historial Médico</h2>

    <div v-if="historial.length === 0" class="tarjeta-vacia">
      <p class="texto-vacio">No tienes registros médicos previos.</p>
    </div>

    <div v-else class="lista-historial">
      <div v-for="registro in historial" :key="registro.id" class="tarjeta-registro">
        
        <div class="bloque-info-izquierda">
          
          <div :class="['contenedor-icono', registro.colorIcono === 'morado' ? 'icono-morado' : 'icono-azul']">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="svg-documento">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
          </div>

          <div class="textos-registro">
            <h3 class="encabezado-tarjeta">
              {{ registro.tipo }} - <span class="especialidad-resaltada">{{ registro.especialidad }}</span>
            </h3>
            <p class="nombre-medico">{{ registro.doctor }}</p>
            <p class="fecha-registro">{{ registro.fecha }}</p>
          </div>
        </div>

        <div class="bloque-accion-derecha">
          <button @click="verDetalle(registro.id)" class="btn-ver-detalle">
            Ver detalle
            <span class="flecha-indicador">&gt;</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* ESTILOS INMUNES A FALLOS DE TAILWIND */
.contenedor-historial {
  padding: 24px;
  max-width: 850px;
  margin: 0 auto;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.titulo-seccion {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 24px;
  letter-spacing: -0.5px;
}

/* Tarjeta Estado Vacío */
.tarjeta-vacia {
  background-color: #ffffff;
  padding: 32px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.texto-vacio {
  color: #64748b;
  font-size: 14px;
  margin: 0;
}

/* Lista de Tarjetas */
.lista-historial {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.tarjeta-registro {
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.01);
  transition: all 0.2s ease-in-out;
}

.tarjeta-registro:hover {
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.bloque-info-izquierda {
  display: flex;
  align-items: center;
  gap: 18px;
}

/* Contenedores de Icono */
.contenedor-icono {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icono-azul {
  background-color: #ecfeff;
  color: #0891b2;
  border: 1px solid #cffafe;
}

.icono-morado {
  background-color: #f5f3ff;
  color: #7c3aed;
  border: 1px solid #ede9fe;
}

.svg-documento {
  width: 20px;
  height: 20px;
}

/* Tipografías y Textos */
.textos-registro {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.encabezado-tarjeta {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.especialidad-resaltada {
  font-weight: 600;
  color: #475569;
}

.nombre-medico {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.fecha-registro {
  font-size: 13px;
  color: #94a3b8;
  margin: 0;
}

/* Botón Ver Detalle */
.btn-ver-detalle {
  background: none;
  border: none;
  font-size: 14px;
  font-weight: 600;
  color: #2563eb;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background-color 0.15s;
}

.btn-ver-detalle:hover {
  color: #1d4ed8;
  background-color: #f0f4ff;
}

.flecha-indicador {
  font-size: 12px;
  font-weight: 700;
  position: relative;
  top: -1px;
}
</style>