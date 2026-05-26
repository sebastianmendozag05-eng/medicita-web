<template>
  <div class="dashboard-container">
    
    <aside class="sidebar">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      
      <nav class="nav-menu">
        <a href="#" class="nav-item active">
          <span class="icon">🏠</span> Inicio
        </a>
        <a href="#" class="nav-item">
          <span class="icon">👤</span> Mi Perfil
        </a>
        <a href="#" class="nav-item">
          <span class="icon">💬</span> Mis Citas
        </a>
        <a href="#" class="nav-item">
          <span class="icon">📁</span> Historial Médico
        </a>
        <a href="#" class="nav-item">
          <span class="icon">🔔</span> Notificaciones
        </a>
      </nav>
      
      <div class="sidebar-footer">
        <router-link to="/login" class="nav-item logout" @click="cerrarSesion">
          <span class="icon">🚪</span> Cerrar Sesión
        </router-link>
      </div>
    </aside>

    <main class="main-content">
      
      <header class="top-header">
        <div class="spacer"></div>
        <div class="user-profile">
          <span class="welcome-text">Hola, {{ nombreUsuario }} 👋</span>
          <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=120" alt="Avatar de paciente" class="avatar" />
        </div>
      </header>

      <section class="overview-section">
        <h2 class="section-title">Resumen</h2>
        <div class="metrics-grid">
          
          <div class="metric-card">
            <div class="metric-icon-wrapper blue">🗓️</div>
            <div class="metric-info">
              <h3>3</h3>
              <p>Próximas citas</p>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon-wrapper red">❤️</div>
            <div class="metric-info">
              <h3>2</h3>
              <p>Consultas</p>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon-wrapper purple">📄</div>
            <div class="metric-info">
              <h3>5</h3>
              <p>Historiales</p>
            </div>
          </div>

        </div>
      </section>

      <section class="appointments-section">
        <div class="section-header">
          <h2 class="section-title">Próximas citas</h2>
          <a href="#" class="view-all-link">Ver todas</a>
        </div>

        <div class="appointments-list">
          
          <div class="appointment-card">
            <div class="appointment-details">
              <div class="specialty-icon">🫀</div>
              <div>
                <h4>Cardiología</h4>
                <p class="doctor-name">Dr. Carlos Hernández</p>
                <p class="appointment-date">21 de mayo, 2026 - 10:50 AM</p>
              </div>
            </div>
            <div class="appointment-status">
              <span class="badge status-confirmed">Confirmada</span>
              <span class="arrow-icon">›</span>
            </div>
          </div>

          <div class="appointment-card">
            <div class="appointment-details">
              <div class="specialty-icon">🩺</div>
              <div>
                <h4>Medicina General</h4>
                <p class="doctor-name">Dra. Irene Torres</p>
                <p class="appointment-date">30 de mayo, 2026 - 08:35 AM</p>
              </div>
            </div>
            <div class="appointment-status">
              <span class="badge status-pending">Pendiente</span>
              <span class="arrow-icon">›</span>
            </div>
          </div>

          <div class="appointment-card">
            <div class="appointment-details">
              <div class="specialty-icon">🧪</div>
              <div>
                <h4>Dermatología</h4>
                <p class="doctor-name">Dr. Pedro Sisniega</p>
                <p class="appointment-date">30 de junio, 2026 - 11:00 AM</p>
              </div>
            </div>
            <div class="appointment-status">
              <span class="badge status-confirmed">Confirmada</span>
              <span class="arrow-icon">›</span>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const nombreUsuario = ref('Paciente')

onMounted(() => {
  // Buscamos si hay un nombre guardado en el navegador al montar la vista
  const nombreGuardado = localStorage.getItem('usuarioNombre')
  if (nombreGuardado) {
    nombreUsuario.value = nombreGuardado
  }
})

const cerrarSesion = () => {
  // Elimina la sesión guardada al salir
  localStorage.removeItem('usuarioNombre')
}
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  width: 100vw;
}

.sidebar {
  width: 260px;
  background-color: #ffffff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  padding: 2rem 1.5rem;
  box-sizing: border-box;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 3rem;
}

.logo-icon {
  background-color: #0d8a72;
  color: white;
  font-weight: bold;
  font-size: 1.2rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.brand h1 {
  color: #0d8a72;
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex-grow: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.85rem 1rem;
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  border-radius: 10px;
  transition: all 0.2s;
}

.nav-item:hover {
  background-color: #f1f5f9;
  color: #1e293b;
}

.nav-item.active {
  background-color: #e6f4f1;
  color: #0d8a72;
}

.logout {
  color: #ef4444;
}
.logout:hover {
  background-color: #fef2f2;
  color: #ef4444;
}

.main-content {
  flex-grow: 1;
  padding: 2rem 3rem;
  box-sizing: border-box;
  overflow-y: auto;
  text-align: left;
}

.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.welcome-text {
  font-weight: 600;
  color: #1e293b;
  font-size: 1.1rem;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.section-title {
  color: #1e293b;
  font-size: 1.3rem;
  font-weight: 700;
  margin: 0 0 1.2rem 0;
}

.overview-section {
  margin-bottom: 3rem;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.metric-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.2rem;
}

.metric-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.metric-icon-wrapper.blue { background-color: #eff6ff; }
.metric-icon-wrapper.red { background-color: #fef2f2; }
.metric-icon-wrapper.purple { background-color: #faf5ff; }

.metric-info h3 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e293b;
}

.metric-info p {
  margin: 0;
  color: #64748b;
  font-size: 0.95rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
}

.view-all-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.95rem;
}

.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.appointment-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.2rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: box-shadow 0.2s;
}

.appointment-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
}

.appointment-details {
  display: flex;
  align-items: center;
  gap: 1.2rem;
}

.specialty-icon {
  width: 44px;
  height: 44px;
  background-color: #f8fafc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
}

.appointment-details h4 {
  margin: 0 0 0.2rem 0;
  color: #1e293b;
  font-size: 1.05rem;
  font-weight: 600;
}

.doctor-name {
  margin: 0 0 0.2rem 0;
  color: #64748b;
  font-size: 0.9rem;
}

.appointment-date {
  margin: 0;
  color: #94a3b8;
  font-size: 0.85rem;
}

.appointment-status {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.badge {
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-confirmed {
  background-color: #e6f4f1;
  color: #0d8a72;
}

.status-pending {
  background-color: #fff7ed;
  color: #f97316;
}

.arrow-icon {
  color: #94a3b8;
  font-size: 1.5rem;
  font-weight: 300;
  cursor: pointer;
}
</style>