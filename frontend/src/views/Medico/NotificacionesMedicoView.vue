<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const BASE_URL = 'http://localhost:8000/api/v1'
const getHeaders = () => ({ 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('token')}` })

const notificaciones = ref([])
const cargando = ref(false)

const cargarNotificaciones = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${BASE_URL}/notificaciones`, { headers: getHeaders() })
    if (res.ok) notificaciones.value = await res.json()
  } catch { /* silencioso */ } finally {
    cargando.value = false
  }
}

const marcarLeida = async (id) => {
  const n = notificaciones.value.find(n => n.notifId === id)
  if (!n || n.leida) return
  n.leida = true
  try {
    await fetch(`${BASE_URL}/notificaciones/${id}/leer`, { method: 'PUT', headers: getHeaders() })
  } catch { /* silencioso */ }
}

const cerrarSesion = () => { localStorage.clear(); router.push('/login') }

onMounted(cargarNotificaciones)
</script>

<template>
  <div class="pantalla-layout">
    <aside class="sidebar-izquierdo">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      <nav class="menu-navegacion">
        <router-link to="/medico/inicio" class="enlace-menu">
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
        <router-link to="/medico/notificaciones" class="enlace-menu activo">
          <span class="icono">🔔</span> Notificaciones
        </router-link>
      </nav>
      <div class="sidebar-pie">
        <button @click="cerrarSesion" class="btn-cerrar-sesion">🚪 Cerrar Sesión</button>
      </div>
    </aside>

    <div class="contenedor-dashboard">
      <div class="cabecera-medico">
        <h1 class="saludo-principal">🔔 Notificaciones</h1>
      </div>

      <div v-if="cargando" class="tarjeta-lista">
        <p class="texto-vacio-simple">Cargando...</p>
      </div>
      <div v-else-if="notificaciones.length === 0" class="tarjeta-lista">
        <p class="texto-vacio-simple">No tienes notificaciones por el momento.</p>
      </div>
      <div v-else class="tarjeta-lista">
        <div
          v-for="n in notificaciones" :key="n.notifId"
          @click="marcarLeida(n.notifId)"
          class="fila-notif"
          :class="{ 'fila-no-leida': !n.leida }"
        >
          <p class="titulo-notif">{{ n.titulo }}</p>
          <p class="desc-notif">{{ n.descripcion }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pantalla-layout { display: flex; min-height: 100vh; background-color: #f8fafc; }
.sidebar-izquierdo {
  width: 260px; background-color: #ffffff; border-right: 1px solid #e2e8f0;
  display: flex; flex-direction: column; padding: 1.5rem; box-sizing: border-box; flex-shrink: 0;
}
.brand { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 2.5rem; }
.logo-icon {
  background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.3rem;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;
}
.brand h1 { color: #0d8a72; font-size: 1.6rem; font-weight: 700; margin: 0; letter-spacing: -0.5px; }
.menu-navegacion { display: flex; flex-direction: column; gap: 0.5rem; flex-grow: 1; }
.enlace-menu {
  display: flex; align-items: center; gap: 0.8rem; padding: 0.85rem 1rem;
  color: #64748b; text-decoration: none; font-weight: 600; border-radius: 10px;
  transition: all 0.2s; font-size: 0.95rem;
}
.enlace-menu:hover { background-color: #f1f5f9; color: #1e293b; }
.enlace-menu.activo { background-color: #e6f4f1; color: #0d8a72; }
.sidebar-pie { margin-top: auto; }
.btn-cerrar-sesion {
  width: 100%; background: none; border: none; color: #b45309;
  padding: 0.85rem 1rem; font-weight: 600; font-size: 0.95rem;
  cursor: pointer; text-align: left; border-radius: 10px; transition: background-color 0.2s;
}
.btn-cerrar-sesion:hover { background-color: #fef3c7; }
.contenedor-dashboard {
  flex-grow: 1; padding: 24px; box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.cabecera-medico { margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; }
.saludo-principal { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
.tarjeta-lista {
  background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px;
}
.texto-vacio-simple { padding: 20px; text-align: center; color: #94a3b8; font-size: 14px; margin: 0; }
.fila-notif { padding: 14px 16px; border-radius: 10px; cursor: pointer; border-left: 3px solid transparent; }
.fila-notif:hover { background-color: #f8fafc; }
.fila-no-leida { border-left-color: #0d8a72; background-color: #f0fdfa; }
.titulo-notif { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; }
.desc-notif { font-size: 13px; color: #64748b; margin: 0; }

@media (max-width: 768px) {
  .pantalla-layout { flex-direction: column; }
  .sidebar-izquierdo { width: 100%; border-right: none; border-bottom: 1px solid #e2e8f0; }
}
</style>
