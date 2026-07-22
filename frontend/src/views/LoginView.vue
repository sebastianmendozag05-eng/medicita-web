
<template>
  <main class="container">
    <div class="form-wrapper">
      
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      
      <h2 class="title">Bienvenido de nuevo</h2>
      <p class="subtitle">Inicie sesión para continuar</p>
 
      <!-- Mensaje de error del servidor -->
      <div v-if="errorMsg" class="error-banner">{{ errorMsg }}</div>
 
      <form @submit.prevent="iniciarSesion">
        
        <div class="form-group">
          <label for="correo">Correo electrónico</label>
          <input 
            type="email" 
            id="correo"
            v-model="credenciales.correo" 
            required 
            placeholder="ejemplo@correo.com" 
          />
        </div>
 
        <div class="form-group password-container">
          <label for="password">Contraseña</label>
          <div class="input-with-icon">
            <input 
              :type="mostrarPass ? 'text' : 'password'" 
              id="password"
              v-model="credenciales.password" 
              required 
              placeholder="••••••••" 
            />
            <span class="eye-icon" @click="mostrarPass = !mostrarPass">
              {{ mostrarPass ? '👁️' : '🙈' }}
            </span>
          </div>
        </div>
 
        <div class="forgot-link-container">
          <router-link to="/recuperar" class="forgot-password">
            ¿Olvidaste tu contraseña?
          </router-link>
        </div>
 
        <button type="submit" :disabled="!formularioValido || cargando" :class="{ 'btn-deshabilitado': !formularioValido || cargando }">
          {{ cargando ? 'Iniciando sesión...' : 'Iniciar sesión' }}
        </button>
      </form>
 
      <div class="register-redirect">
        ¿No tienes cuenta? <router-link to="/">Regístrate aquí</router-link>
      </div>
      
    </div>
  </main>
</template>
 
<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
 
const router = useRouter()
const mostrarPass = ref(false)
const cargando = ref(false)
const errorMsg = ref('')
 
const credenciales = ref({
  correo: '',
  password: ''
})
 
const formularioValido = computed(() => {
  return credenciales.value.correo.trim() !== '' && credenciales.value.password.length >= 4
})
 
const iniciarSesion = async () => {
  if (!formularioValido.value || cargando.value) return
 
  cargando.value = true
  errorMsg.value = ''
 
  try {
    const res = await fetch('http://localhost:8000/api/v1/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: credenciales.value.correo,
        password: credenciales.value.password
      })
    })
 
    const data = await res.json()
 
    if (!res.ok) {
      // El backend devuelve { message: 'Credenciales incorrectas' } con 401
      errorMsg.value = data.message || 'Error al iniciar sesión. Intenta de nuevo.'
      return
    }
 
    // ── Guardar token y datos del usuario ──
    localStorage.setItem('token', data.token)
    localStorage.setItem('usuarioNombre', data.user.name ?? '')
    localStorage.setItem('usuarioCorreo', data.user.email ?? '')
    localStorage.setItem('usuarioRol', data.user.rol ?? 'paciente')
    if (data.user.pacId) localStorage.setItem('pacId', data.user.pacId)
    if (data.user.medId) localStorage.setItem('medicoId', data.user.medId)
    if (data.user.astId) localStorage.setItem('astId', data.user.astId)
 
    // ── Redirigir según el rol devuelto por el backend ──
    if (data.user.rol === 'medico') {
      router.push('/medico/inicio')
    } else {
      router.push('/dashboard')
    }
 
  } catch (err) {
    // Error de red (el backend no está corriendo, CORS, etc.)
    errorMsg.value = 'No se pudo conectar al servidor. Verifica que el backend esté activo.'
  } finally {
    cargando.value = false
  }
}
</script>
 
<style scoped>
.container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #ffffff; padding: 1.5rem; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
.form-wrapper { width: 100%; max-width: 360px; background: white; padding: 1rem; }
.brand { display: flex; align-items: center; justify-content: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon { background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
h1 { color: #0d8a72; font-size: 1.7rem; font-weight: 700; margin: 0; letter-spacing: -0.5px; }
.title { color: #1e293b; font-size: 1.5rem; font-weight: 700; margin: 0 0 0.4rem 0; text-align: center; }
.subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 2.5rem; text-align: center; }
.error-banner { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.9rem; margin-bottom: 1.2rem; text-align: center; }
.form-group { display: flex; flex-direction: column; margin-bottom: 1.2rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem; text-align: left; }
input { width: 100%; padding: 0.85rem 1rem; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.95rem; background-color: #fff; color: #1e293b; box-sizing: border-box; transition: border-color 0.2s; }
input::placeholder { color: #94a3b8; }
input:focus { border-color: #0d8a72; outline: none; }
.input-with-icon { position: relative; display: flex; align-items: center; }
.input-with-icon input { padding-right: 2.5rem; }
.eye-icon { position: absolute; right: 1rem; cursor: pointer; font-size: 1rem; user-select: none; color: #64748b; }
.forgot-link-container { display: flex; justify-content: flex-end; margin-top: -0.5rem; margin-bottom: 2rem; }
.forgot-password { font-size: 0.8rem; color: #3b82f6; text-decoration: none; font-weight: 500; }
.forgot-password:hover { text-decoration: underline; }
button { width: 100%; padding: 0.9rem; background-color: #0d8a72; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; margin-bottom: 2rem; }
button:hover:not(:disabled) { background-color: #0a6c59; }
.btn-deshabilitado { background-color: #cbd5e1 !important; cursor: not-allowed; }
.register-redirect { text-align: center; font-size: 0.9rem; color: #64748b; }
.register-redirect a { color: #3b82f6; text-decoration: none; font-weight: 600; }
.register-redirect a:hover { text-decoration: underline; }
</style>