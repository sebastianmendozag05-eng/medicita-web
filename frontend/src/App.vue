<script setup>
import { RouterView } from 'vue-router'
import MenuNavegacion from './components/MenuNavegacion.vue'
</script>

<template>
  <MenuNavegacion />
  <RouterView />
</template>

<style>
body {
  margin: 0;
  padding: 0;
  font-family: Arial, sans-serif;
  background-color: #f4f7f6;
}
</style>