<template>
  <main class="container">
    <div class="form-wrapper">
      
      <div class="top-bar">
        <router-link to="/login" class="back-arrow">←</router-link>
      </div>
      
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      
      <h2 class="title">Recuperar contraseña</h2>
      <p class="subtitle">
        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
      </p>

      <form @submit.prevent="enviarEnlace">
        
        <div class="form-group">
          <label for="correo">Correo electrónico</label>
          <input 
            type="email" 
            id="correo"
            v-model="correo" 
            required 
            placeholder="ejemplo@correo.com" 
          />
        </div>

        <button type="submit" :disabled="!correoValido || enviando" :class="{ 'btn-deshabilitado': !correoValido }">
          {{ enviando ? 'Enviando...' : 'Enviar enlace' }}
        </button>
      </form>

      <p v-if="mensaje" class="subtitle" style="margin-top: 1rem;">{{ mensaje }}</p>

      <div class="login-redirect">
        <router-link to="/login">Volver a inicio de sesión</router-link>
      </div>
      
    </div>
  </main>
</template>

<script setup>
import { ref, computed } from 'vue'

const BASE_URL = 'http://localhost:8000/api/v1'
const correo = ref('')
const enviando = ref(false)
const mensaje = ref('')

// Validar estructura básica del correo
const correoValido = computed(() => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return regex.test(correo.value)
})

const enviarEnlace = async () => {
  if (!correoValido.value) return
  enviando.value = true
  mensaje.value = ''
  try {
    const res = await fetch(`${BASE_URL}/forgot-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: correo.value })
    })
    const data = await res.json()
    mensaje.value = data.message || 'Si el correo existe, se envió un enlace de recuperación.'
  } catch {
    mensaje.value = 'No se pudo conectar con el servidor. Intenta de nuevo.'
  } finally {
    enviando.value = false
  }
}
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #ffffff;
  padding: 1.5rem;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.form-wrapper {
  width: 100%;
  max-width: 360px;
  background: white;
  padding: 1rem;
  position: relative;
}
.top-bar {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 1rem;
}
.back-arrow {
  color: #64748b;
  font-size: 1.4rem;
  text-decoration: none;
  font-weight: normal;
  transition: color 0.2s;
}
.back-arrow:hover {
  color: #0d8a72;
}
.brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  margin-bottom: 2rem;
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
h1 { 
  color: #0d8a72; 
  font-size: 1.7rem; 
  font-weight: 700; 
  margin: 0; 
  letter-spacing: -0.5px;
}
.title { 
  color: #1e293b; 
  font-size: 1.5rem; 
  font-weight: 700; 
  margin: 0 0 0.6rem 0; 
  text-align: center;
}
.subtitle { 
  color: #64748b; 
  font-size: 0.95rem; 
  line-height: 1.5;
  margin-bottom: 2.5rem; 
  text-align: center; 
}
.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 1.8rem;
}
.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
  text-align: left;
}
input {
  width: 100%;
  padding: 0.85rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  box-sizing: border-box;
}
input:focus {
  border-color: #0d8a72;
  outline: none;
}
button {
  width: 100%;
  padding: 0.9rem;
  background-color: #0d8a72;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 2rem;
}
.btn-deshabilitado { 
  background-color: #cbd5e1; 
  cursor: not-allowed; 
}
.login-redirect {
  text-align: center;
  font-size: 0.9rem;
}
.login-redirect a { 
  color: #3b82f6; 
  text-decoration: none; 
  font-weight: 600; 
}
.login-redirect a:hover {
  text-decoration: underline;
}
</style>