<script setup>
import { ref } from 'vue'

const citas = ref([
  { id: 1, doctor: 'Dr. Alejandro Armas', especialidad: 'Consulta General', fecha: '28 de Mayo, 2026', hora: '10:00 AM', estado: 'Confirmada' }
])
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Mis Citas Médicas</h2>
      <button class="bg-teal-600 hover:bg-teal-700 text-white font-medium px-4 py-2 rounded-lg transition-colors">
        + Agendar Nueva Cita
      </button>
    </div>

    <div class="space-y-4">
      <div v-for="cita in citas" :key="cita.id" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="inline-block text-xs font-semibold bg-teal-50 text-teal-700 px-2 py-1 rounded mb-2">
            {{ cita.especialidad }}
          </span>
          <h3 class="text-lg font-semibold text-gray-800">{{ cita.doctor }}</h3>
          <p class="text-sm text-gray-500 mt-1">📅 {{ cita.fecha }} a las {{ cita.hora }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-green-600 bg-green-50 px-3 py-1 rounded-full">
            ● {{ cita.estado }}
          </span>
          <button class="text-sm text-red-500 hover:underline">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>