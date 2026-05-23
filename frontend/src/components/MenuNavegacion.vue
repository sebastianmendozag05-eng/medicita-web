<template>
  <nav class="navbar">
    <div class="logo">MediCita</div>
    <div class="nav-links">
      <RouterLink to="/">Registrarse</RouterLink>
      <RouterLink to="/login">Iniciar Sesión</RouterLink>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background-color: #2c3e50;
  color: white;
}

.logo {
  font-size: 1.4rem;
  font-weight: bold;
  color: #42b983;
}

.nav-links a {
  color: white;
  text-decoration: none;
  margin-left: 1.5rem;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-links a:hover {
  color: #42b983;
}

/* Clase automática de Vue para resaltar el enlace de la página donde estamos */
.router-link-active {
  color: #42b983 !important;
  border-bottom: 2px solid #42b983;
}
</style>