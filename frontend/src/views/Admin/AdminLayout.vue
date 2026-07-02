<template>
  <div class="admin-shell">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-brand">
        <span class="logo-icon">+</span>
        <span class="brand-name">MediCita</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/admin" exact class="nav-item">
          <span class="nav-icon">🏠</span>
          <span class="nav-label">Inicio</span>
        </router-link>
        <router-link to="/admin/citas" class="nav-item">
          <span class="nav-icon">📅</span>
          <span class="nav-label">Citas</span>
        </router-link>
        <router-link to="/admin/pacientes" class="nav-item">
          <span class="nav-icon">👥</span>
          <span class="nav-label">Pacientes</span>
        </router-link>
        <router-link to="/admin/reportes" class="nav-item">
          <span class="nav-icon">📊</span>
          <span class="nav-label">Reportes</span>
        </router-link>
        <router-link to="/admin/notificaciones" class="nav-item">
          <span class="nav-icon">🔔</span>
          <span class="nav-label">Notificaciones</span>
          <span v-if="notifCount > 0" class="badge">{{ notifCount }}</span>
        </router-link>
        <router-link to="/admin/perfil" class="nav-item">
          <span class="nav-icon">👤</span>
          <span class="nav-label">Perfil</span>
        </router-link>
      </nav>

      <button class="sidebar-logout" @click="cerrarSesion">
        <span class="nav-icon">🚪</span>
        <span class="nav-label">Cerrar sesión</span>
      </button>
    </aside>

    <!-- Main area -->
    <div class="main-area">
      <header class="topbar">
        <button class="toggle-btn" @click="sidebarCollapsed = !sidebarCollapsed">☰</button>
        <div class="topbar-right">
          <span class="topbar-date">{{ fechaHoy }}</span>
          <div class="topbar-avatar">AD</div>
        </div>
      </header>

      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const sidebarCollapsed = ref(false)
const notifCount = ref(3)

const fechaHoy = computed(() => {
  return new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

const cerrarSesion = () => {
  localStorage.removeItem('token')
  router.push('/login')
}
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-shell {
  display: flex;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f1f5f9;
}

/* ── Sidebar ── */
.sidebar {
  width: 240px;
  background: #0d8a72;
  display: flex;
  flex-direction: column;
  padding: 1.5rem 1rem;
  transition: width 0.25s ease;
  overflow: hidden;
  flex-shrink: 0;
  position: sticky;
  top: 0;
  height: 100vh;
}
.sidebar.collapsed { width: 68px; }
.sidebar.collapsed .brand-name,
.sidebar.collapsed .nav-label { display: none; }
.sidebar.collapsed .badge { display: none; }

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 2rem;
  padding-left: 0.2rem;
}
.logo-icon {
  background: white;
  color: #0d8a72;
  font-weight: bold;
  font-size: 1.1rem;
  width: 30px;
  height: 30px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.brand-name { color: white; font-size: 1.2rem; font-weight: 700; white-space: nowrap; }

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: 10px;
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
  position: relative;
}
.nav-item:hover { background: rgba(255,255,255,0.12); color: white; }
.nav-item.router-link-active { background: rgba(255,255,255,0.2); color: white; }

.nav-icon { font-size: 1.1rem; flex-shrink: 0; width: 22px; text-align: center; }
.badge {
  margin-left: auto;
  background: #ef4444;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 99px;
}

.sidebar-logout {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: 10px;
  background: none;
  border: none;
  color: rgba(255,255,255,0.7);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  white-space: nowrap;
  transition: background 0.15s;
}
.sidebar-logout:hover { background: rgba(255,255,255,0.12); color: white; }

/* ── Main area ── */
.main-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.topbar {
  background: white;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 10;
}
.toggle-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #64748b;
  padding: 0.3rem;
  border-radius: 6px;
  line-height: 1;
}
.toggle-btn:hover { background: #f1f5f9; }

.topbar-right { display: flex; align-items: center; gap: 1rem; }
.topbar-date { font-size: 0.82rem; color: #94a3b8; text-transform: capitalize; }
.topbar-avatar {
  width: 34px;
  height: 34px;
  background: #0d8a72;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
}

.page-content {
  padding: 2rem;
  flex: 1;
}
</style>
