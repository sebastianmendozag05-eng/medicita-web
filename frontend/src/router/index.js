import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue' // Importamos tu nueva vista de Login

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'registro',
      component: HomeView
    },
    {
      path: '/login', // Cuando entres a http://localhost:5173/login...
      name: 'login',
      component: LoginView // ...Vue cargará este componente
    }
  ]
})

export default router