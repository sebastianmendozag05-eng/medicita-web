<template>
  <div class="chatbot-wrapper">
    <button class="chatbot-burbuja" @click="abierto = !abierto" :aria-label="abierto ? 'Cerrar chat' : 'Abrir chat'">
      <span v-if="!abierto">💬</span>
      <span v-else>✕</span>
    </button>

    <div v-if="abierto" class="chatbot-panel">
      <div class="chatbot-header">
        <div class="chatbot-avatar">🩺</div>
        <div>
          <p class="chatbot-titulo">Asistente MediCita</p>
          <p class="chatbot-subtitulo">Preguntas frecuentes</p>
        </div>
      </div>

      <div class="chatbot-mensajes" ref="listaMensajes">
        <div v-for="(m, i) in mensajes" :key="i" class="chatbot-fila" :class="m.de">
          <div class="chatbot-burbuja-msg" :class="m.de">{{ m.texto }}</div>
        </div>
        <div v-if="cargando" class="chatbot-fila bot">
          <div class="chatbot-burbuja-msg bot chatbot-escribiendo">Escribiendo...</div>
        </div>
      </div>

      <div v-if="mostrarSugerencias" class="chatbot-sugerencias">
        <button
          v-for="faq in sugerenciasVisibles" :key="faq.faqId"
          class="chip-sugerencia"
          @click="preguntarSugerida(faq)"
        >
          {{ faq.faqPregunta }}
        </button>
      </div>

      <form class="chatbot-input-row" @submit.prevent="enviarMensaje">
        <input
          v-model="entrada"
          type="text"
          placeholder="Escribe tu pregunta..."
          class="chatbot-input"
        />
        <button type="submit" class="chatbot-btn-enviar" :disabled="!entrada.trim() || cargando">➤</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const abierto = ref(false)
const entrada = ref('')
const cargando = ref(false)
const listaMensajes = ref(null)
const preguntasFrecuentes = ref([])

const mensajes = ref([
  { de: 'bot', texto: '¡Hola! Soy el asistente de MediCita. Puedo responder preguntas frecuentes sobre citas, tu cuenta y el sistema. ¿En qué te ayudo?' },
])

const mostrarSugerencias = computed(() => !cargando.value && preguntasFrecuentes.value.length > 0)
const sugerenciasVisibles = computed(() => preguntasFrecuentes.value.slice(0, 4))

const cargarSugerencias = async () => {
  try {
    const res = await fetch(`${BASE_URL}/chatbot/preguntas-frecuentes`, { headers: getHeaders() })
    if (!res.ok) return
    const data = await res.json()
    preguntasFrecuentes.value = Object.values(data).flat()
  } catch { /* silencioso */ }
}

const scrollAbajo = () => {
  nextTick(() => {
    if (listaMensajes.value) listaMensajes.value.scrollTop = listaMensajes.value.scrollHeight
  })
}

const consultarBackend = async (texto) => {
  cargando.value = true
  try {
    const res = await fetch(`${BASE_URL}/chatbot/consultar`, {
      method: 'POST', headers: getHeaders(), body: JSON.stringify({ mensaje: texto }),
    })
    const data = await res.json()
    mensajes.value.push({ de: 'bot', texto: data.respuesta || 'No pude procesar tu pregunta, intenta reformularla.' })
  } catch {
    mensajes.value.push({ de: 'bot', texto: 'No pude conectar con el asistente. Intenta de nuevo en un momento.' })
  } finally {
    cargando.value = false
    scrollAbajo()
  }
}

const enviarMensaje = async () => {
  const texto = entrada.value.trim()
  if (!texto) return
  mensajes.value.push({ de: 'usuario', texto })
  entrada.value = ''
  scrollAbajo()
  await consultarBackend(texto)
}

const preguntarSugerida = async (faq) => {
  mensajes.value.push({ de: 'usuario', texto: faq.faqPregunta })
  scrollAbajo()
  await consultarBackend(faq.faqPregunta)
}

onMounted(cargarSugerencias)
</script>

<style scoped>
.chatbot-wrapper {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.chatbot-burbuja {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background-color: #0d8a72;
  color: white;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(13, 138, 114, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: auto;
  transition: transform 0.15s ease;
}
.chatbot-burbuja:hover { transform: scale(1.06); }

.chatbot-panel {
  width: 340px;
  height: 460px;
  background: #ffffff;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 12px 32px rgba(0,0,0,0.14);
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chatbot-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  background-color: #0d8a72;
  color: white;
}
.chatbot-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.chatbot-titulo { margin: 0; font-size: 0.92rem; font-weight: 700; }
.chatbot-subtitulo { margin: 0; font-size: 0.72rem; opacity: 0.85; }

.chatbot-mensajes {
  flex: 1;
  overflow-y: auto;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  background-color: #f8fafc;
}

.chatbot-fila { display: flex; }
.chatbot-fila.usuario { justify-content: flex-end; }
.chatbot-fila.bot { justify-content: flex-start; }

.chatbot-burbuja-msg {
  max-width: 80%;
  padding: 9px 13px;
  border-radius: 14px;
  font-size: 0.85rem;
  line-height: 1.35;
}
.chatbot-burbuja-msg.bot {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  border-bottom-left-radius: 4px;
}
.chatbot-burbuja-msg.usuario {
  background: #0d8a72;
  color: white;
  border-bottom-right-radius: 4px;
}
.chatbot-escribiendo { color: #94a3b8; font-style: italic; }

.chatbot-sugerencias {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 0 14px 10px 14px;
  background-color: #f8fafc;
}
.chip-sugerencia {
  background: white;
  border: 1px solid #cbd5e1;
  color: #0d8a72;
  border-radius: 999px;
  padding: 5px 11px;
  font-size: 0.72rem;
  cursor: pointer;
  text-align: left;
}
.chip-sugerencia:hover { background-color: #e6f4f1; }

.chatbot-input-row {
  display: flex;
  gap: 8px;
  padding: 12px;
  border-top: 1px solid #e2e8f0;
  background: white;
}
.chatbot-input {
  flex: 1;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 0.85rem;
  outline: none;
}
.chatbot-input:focus { border-color: #0d8a72; }
.chatbot-btn-enviar {
  width: 38px;
  border: none;
  border-radius: 10px;
  background-color: #0d8a72;
  color: white;
  font-size: 0.95rem;
  cursor: pointer;
}
.chatbot-btn-enviar:disabled { background-color: #cbd5e1; cursor: not-allowed; }

@media (max-width: 480px) {
  .chatbot-panel { width: calc(100vw - 32px); }
}
</style>
