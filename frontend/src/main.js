import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(router) // Aquí conectamos el sistema de rutas
app.mount('#app')