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

      <h2 class="title">Nueva contraseña</h2>
      <p class="subtitle">Ingresa tu nueva contraseña para la cuenta <strong>{{ correo }}</strong>.</p>

      <form v-if="!exito" @submit.prevent="restablecer">
        <div class="form-group">
          <label for="password">Nueva contraseña</label>
          <input type="password" id="password" v-model="password" required minlength="8" placeholder="Mínimo 8 caracteres" />
        </div>

        <div class="form-group">
          <label for="confirmar">Confirmar contraseña</label>
          <input type="password" id="confirmar" v-model="confirmar" required minlength="8" placeholder="Repite la contraseña" />
        </div>

        <p v-if="error" class="error-msg">{{ error }}</p>

        <button type="submit" :disabled="enviando">
          {{ enviando ? 'Guardando...' : 'Restablecer contraseña' }}
        </button>
      </form>

      <div v-else>
        <p class="subtitle">¡Tu contraseña fue actualizada! Ya puedes iniciar sesión.</p>
        <router-link to="/login" class="login-redirect">Ir a iniciar sesión</router-link>
      </div>

      <div v-if="!exito" class="login-redirect">
        <router-link to="/login">Volver a inicio de sesión</router-link>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const BASE_URL = 'http://localhost:8000/api/v1'

const correo = ref(route.query.email || '')
const token = ref(route.query.token || '')
const password = ref('')
const confirmar = ref('')
const error = ref('')
const enviando = ref(false)
const exito = ref(false)

const restablecer = async () => {
  error.value = ''

  if (!token.value || !correo.value) {
    error.value = 'El enlace no es válido. Solicita uno nuevo.'
    return
  }
  if (password.value !== confirmar.value) {
    error.value = 'Las contraseñas no coinciden.'
    return
  }

  enviando.value = true
  try {
    const res = await fetch(`${BASE_URL}/reset-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: correo.value,
        token: token.value,
        password: password.value,
        password_confirmation: confirmar.value
      })
    })
    const data = await res.json()
    if (!res.ok) {
      error.value = data.message || 'No se pudo restablecer la contraseña.'
      return
    }
    exito.value = true
  } catch {
    error.value = 'No se pudo conectar con el servidor. Intenta de nuevo.'
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
}
.form-wrapper {
  width: 100%;
  max-width: 400px;
}
.top-bar { margin-bottom: 1rem; }
.back-arrow { text-decoration: none; font-size: 1.3rem; color: #0d8a72; }
.brand { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.logo-icon { background: #0d8a72; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; }
.brand h1 { font-size: 1.3rem; color: #1e293b; margin: 0; }
.title { font-size: 1.4rem; color: #1e293b; margin-bottom: 0.4rem; }
.subtitle { color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.85rem; color: #334155; font-weight: 600; }
.form-group input { padding: 0.7rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; }
button[type="submit"] { width: 100%; background: #0d8a72; color: white; border: none; border-radius: 8px; padding: 0.8rem; font-size: 0.95rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; }
button[type="submit"]:disabled { opacity: 0.6; cursor: not-allowed; }
.error-msg { color: #dc2626; font-size: 0.85rem; margin-bottom: 0.5rem; }
.login-redirect { margin-top: 1.5rem; text-align: center; font-size: 0.9rem; }
.login-redirect a { color: #0d8a72; text-decoration: none; font-weight: 600; }
</style>
