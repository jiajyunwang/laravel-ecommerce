<script setup>
import { RouterView } from 'vue-router'
import AppHeader from './components/frontend/layout/AppHeader.vue'
import AppChat from './components/frontend/layout/AppChat.vue'
</script>

<template>
  <AppHeader />
  <RouterView />
  <AppChat />
</template>
