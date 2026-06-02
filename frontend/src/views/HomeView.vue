<template>
  <main class="container">
    <div class="form-wrapper">
      <div class="brand">
        <span class="logo-icon">+</span>
        <h1>MediCita</h1>
      </div>
      <h2>Crear cuenta</h2>
      <p class="subtitle">Completa tus datos para registrarte</p>
      
      <div class="role-selector">
        <label>
          <input type="radio" value="paciente" v-model="tipoUsuario" />
          <span>Paciente</span>
        </label>
        <label>
          <input type="radio" value="medico" v-model="tipoUsuario" />
          <span>Médico</span>
        </label>
      </div>

      <form @submit.prevent="registrarUsuario">
        
        <div class="form-group-row">
          <div class="form-group">
            <input type="text" v-model="formulario.nombre" required placeholder="Nombre(s)" />
          </div>
          <div class="form-group">
            <input type="text" v-model="formulario.apePat" required placeholder="Apellido Paterno" />
          </div>
        </div>

        <div class="form-group">
          <input type="text" v-model="formulario.apeMat" placeholder="Apellido Materno (Opcional)" />
        </div>

        <div class="form-group">
          <input type="email" v-model="formulario.correo" required placeholder="Correo electrónico" />
        </div>

        <div class="form-group">
          <select v-model="formulario.sexo" required>
            <option value="" disabled selected>Selecciona tu Sexo</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
        </div>

        <div v-if="tipoUsuario === 'paciente'" class="dynamic-fields">
          <div class="form-group">
            <label class="input-label">Fecha de Nacimiento</label>
            <input type="date" v-model="formulario.fechaNac" required />
          </div>
          <div class="form-group">
            <input type="text" v-model="formulario.nss" required placeholder="NSS (Número de Seguro Social)" />
          </div>
          <div class="form-group">
            <input type="tel" v-model="formulario.telefono" required placeholder="Teléfono" />
          </div>
          <div class="form-group-row">
            <div class="form-group">
              <input type="number" step="0.1" v-model="formulario.peso" required placeholder="Peso (kg)" />
            </div>
            <div class="form-group">
              <input type="number" step="0.01" v-model="formulario.estatura" required placeholder="Estatura (m) ej: 1.75" />
            </div>
          </div>
        </div>

        <div v-if="tipoUsuario === 'medico'" class="dynamic-fields">
          <div class="form-group">
            <input type="number" v-model="formulario.edad" required placeholder="Edad" min="18" />
          </div>
          <div class="form-group">
            <input type="text" v-model="formulario.cedula" required placeholder="Cédula Profesional" />
          </div>
          <div class="form-group">
            <select v-model="formulario.especialidadId" required>
              <option value="" disabled selected>Selecciona tu Especialidad</option>
              <option v-for="esp in especialidades" :key="esp.espeId" :value="esp.espeId">
                {{ esp.espeNombre }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <input type="text" v-model="formulario.turnos" placeholder="Turnos (ej: Matutino, Vespertino)" />
          </div>
        </div>

        <div class="form-group password-field">
          <input :type="mostrarPass ? 'text' : 'password'" v-model="formulario.password" required placeholder="Contraseña" />
          <span class="eye-icon" @click="mostrarPass = !mostrarPass">{{ mostrarPass ? '👁️' : '🙈' }}</span>
        </div>

        <div class="form-group password-field">
          <input :type="mostrarPass ? 'text' : 'password'" v-model="formulario.confirmarPassword" required placeholder="Confirmar Contraseña" />
        </div>

        <p v-if="formulario.password && !esPasswordValida" class="invalido">
          ✗ La contraseña debe tener al menos 8 caracteres.
        </p>
        <p v-if="formulario.confirmarPassword && !contraseniasCoinciden" class="invalido">
          ✗ Las contraseñas no coinciden.
        </p>

        <div class="terms">
          <input type="checkbox" id="terms" v-model="formulario.aceptarTerminos" required />
          <label for="terms">Acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a></label>
        </div>

        <button type="submit" :disabled="!formularioValido" :class="{ 'btn-deshabilitado': !formularioValido }">
          Registrarme
        </button>
      </form>

      <div class="login-redirect">
        ¿Ya tienes cuenta? <router-link to="/login">Inicie sesión</router-link>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const tipoUsuario = ref('paciente') 
const mostrarPass = ref(false)

const BlackboxEspecialidades = [
  { espeId: 1, espeNombre: 'Medicina General' },
  { espeId: 2, espeNombre: 'Pediatría' },
  { espeId: 3, espeNombre: 'Cardiología' },
  { espeId: 4, espeNombre: 'Ginecología' }
]
const BlackboxEspecialidadesRef = ref(BlackboxEspecialidades)
const especialidades = computed(() => BlackboxEspecialidadesRef.value)

const inicializarFormulario = () => ({
  nombre: '',
  apePat: '',
  apeMat: '',
  correo: '',
  sexo: '',
  password: '',
  confirmarPassword: '',
  aceptarTerminos: false,
  fechaNac: '',
  nss: '',
  telefono: '',
  peso: '',
  estatura: '',
  edad: '',
  cedula: '',
  especialidadId: '',
  turnos: ''
})

const formulario = ref(inicializarFormulario())

watch(tipoUsuario, () => {
  const camposLimpios = inicializarFormulario()
  Object.keys(formulario.value).forEach(key => {
    if (!['nombre', 'apePat', 'apeMat', 'correo', 'sexo', 'password', 'confirmarPassword', 'aceptarTerminos'].includes(key)) {
      formulario.value[key] = camposLimpios[key]
    }
  })
})

const esPasswordValida = computed(() => formulario.value.password.length >= 8)
const contraseniasCoinciden = computed(() => formulario.value.password === formulario.value.confirmarPassword)

const formularioValido = computed(() => {
  const baseValida = 
    formulario.value.nombre && 
    formulario.value.apePat && 
    formulario.value.correo && 
    formulario.value.sexo && 
    esPasswordValida.value && 
    contraseniasCoinciden.value && 
    formulario.value.aceptarTerminos

  if (!baseValida) return false

  if (tipoUsuario.value === 'paciente') {
    return formulario.value.fechaNac && formulario.value.nss && formulario.value.telefono && formulario.value.peso && formulario.value.estatura
  } else {
    return formulario.value.edad && formulario.value.cedula && formulario.value.especialidadId
  }
})

const registrarUsuario = () => {
  if (!formularioValido.value) return

  let payload = {}

  if (tipoUsuario.value === 'paciente') {
    payload = {
      pacNombre: formulario.value.nombre,
      pacApePat: formulario.value.apePat,
      pacApeMat: formulario.value.apeMat,
      pacSexo: formulario.value.sexo,
      pacFechaNac: formulario.value.fechaNac,
      pacNSS: formulario.value.nss,
      pacCorreo: formulario.value.correo,
      pacTelefono: formulario.value.telefono,
      pacPeso: parseFloat(formulario.value.peso),
      pacEstatura: parseFloat(formulario.value.estatura),
      pacEstatus: 1,
      password: formulario.value.password
    }

    localStorage.setItem('usuarioNombre', formulario.value.nombre)
    localStorage.setItem('usuarioRol', 'paciente')
    localStorage.setItem('usuarioApePat', formulario.value.apePat)
    localStorage.setItem('usuarioApeMat', formulario.value.apeMat || '')
    localStorage.setItem('usuarioCorreo', formulario.value.correo)
    localStorage.setItem('usuarioTelefono', formulario.value.telefono)
    localStorage.setItem('usuarioNss', formulario.value.nss)
    localStorage.setItem('usuarioSexo', formulario.value.sexo)
    localStorage.setItem('usuarioFechaNac', formulario.value.fechaNac)
    localStorage.setItem('usuarioPeso', formulario.value.peso)
    localStorage.setItem('usuarioEstatura', formulario.value.estatura)
    
    console.log('Paciente registrado y guardado localmente:', payload)
    alert('¡Registro de PACIENTE exitoso! Ahora inicia sesión.')
    router.push('/login')

  } else {
    payload = {
      medNombre: formulario.value.nombre,
      medApePat: formulario.value.apePat,
      medApeMat: formulario.value.apeMat,
      medSexo: formulario.value.sexo,
      medEdad: parseInt(formulario.value.edad),
      medCorreo: formulario.value.correo,
      medCedula: formulario.value.cedula,
      medEstatus: 1,
      medTurnos: formulario.value.turnos,
      especialidadId: formulario.value.especialidadId
    }

    localStorage.setItem('usuarioNombre', formulario.value.nombre)
    localStorage.setItem('usuarioRol', 'medico')
    localStorage.setItem('usuarioCorreo', formulario.value.correo)
    
    console.log('Médico registrado:', payload)
    alert('¡Registro de MÉDICO exitoso! Ahora inicia sesión.')
    router.push('/login')
  }
}
</script>

<style scoped>
.container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8fafc; padding: 2rem 1rem; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.form-wrapper { background: white; padding: 2.5rem 2rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03); width: 100%; max-width: 440px; }
.brand { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.logo-icon { background-color: #0d8a72; color: white; font-weight: bold; font-size: 1.4rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
h1 { color: #0d8a72; font-size: 1.6rem; font-weight: bold; margin: 0; }
h2 { color: #1e293b; font-size: 1.4rem; margin: 0 0 0.3rem 0; font-weight: 600; text-align: left;}
.subtitle { color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; text-align: left; }
.role-selector { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.role-selector label { flex: 1; cursor: pointer; }
.role-selector input { display: none; }
.role-selector span { display: block; text-align: center; padding: 0.6rem; border: 1px solid #e2e8f0; border-radius: 8px; color: #64748b; font-weight: 500; transition: all 0.2s; }
.role-selector input:checked + span { background-color: #e6f4f1; border-color: #0d8a72; color: #0d8a72; }
.form-group { margin-bottom: 1rem; position: relative; }
.form-group-row { display: flex; gap: 1rem; }
.form-group-row .form-group { flex: 1; }
.input-label { font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem; display: block; }
input, select { width: 100%; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; background-color: #fff; color: #334155; box-sizing: border-box; transition: border-color 0.2s; }
input:focus, select:focus { border-color: #0d8a72; outline: none; }
.password-field .eye-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1rem; user-select: none; }
.invalido { color: #ef4444; font-size: 0.8rem; margin: -0.5rem 0 0.8rem 0; }
.terms { display: flex; align-items: flex-start; gap: 0.5rem; margin: 1.2rem 0; }
.terms input { width: auto; margin-top: 0.2rem; }
.terms label { font-size: 0.85rem; color: #64748b; line-height: 1.3; }
.terms a { color: #0d8a72; text-decoration: none; font-weight: 500; }
button { width: 100%; padding: 0.85rem; background-color: #0d8a72; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
button:hover { background-color: #0a6c59; }
.btn-deshabilitado { background-color: #cbd5e1; cursor: not-allowed; }
.btn-deshabilitado:hover { background-color: #cbd5e1; }
.login-redirect { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #64748b; }
.login-redirect a { color: #0d8a72; text-decoration: none; font-weight: 500; }
</style>