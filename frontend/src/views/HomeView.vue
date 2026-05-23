<template>
  <main class="container">
    <div class="form-wrapper">
      <h1>MediCita</h1>
      <h2>Registro de Paciente</h2>
      
      <form @submit.prevent="registrarPaciente">
        <div class="form-group">
          <label for="nombre">Nombre completo</label>
          <input type="text" id="nombre" v-model="paciente.nombre" required placeholder="Ej. Juan Pérez" />
        </div>

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" v-model="paciente.email" required placeholder="tu@correo.com" />
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" v-model="paciente.password" required placeholder="Mínimo 8 caracteres" />
        </div>

        <button type="submit">Registrarme</button>
      </form>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'

// Aquí guardamos temporalmente lo que el usuario escribe
const paciente = ref({
  nombre: '',
  email: '',
  password: ''
})

// Esta función se ejecuta al darle clic al botón
const registrarPaciente = () => {
  // Más adelante aquí validaremos que la contraseña sea segura
  console.log('Datos listos para enviar al backend:', paciente.value)
  alert('¡Datos capturados! Revisa la consola de tu navegador.')
}
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f4f7f6;
  font-family: Arial, sans-serif;
}

.form-wrapper {
  background: white;
  padding: 2.5rem;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 400px;
}

h1 {
  color: #2c3e50;
  text-align: center;
  margin-bottom: 0.2rem;
}

h2 {
  color: #42b983;
  text-align: center;
  font-size: 1.1rem;
  margin-bottom: 2rem;
  font-weight: normal;
}

.form-group {
  margin-bottom: 1.2rem;
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: #4a4a4a;
  font-size: 0.9rem;
}

input {
  padding: 0.75rem;
  border: 1px solid #dcdcdc;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

input:focus {
  border-color: #42b983;
  outline: none;
}

button {
  width: 100%;
  padding: 0.85rem;
  background-color: #42b983;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.3s;
}

button:hover {
  background-color: #33996b;
}
</style>