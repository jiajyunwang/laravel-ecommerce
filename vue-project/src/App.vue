<script setup>
import { RouterView } from 'vue-router'
import AppHeader from './components/layout/AppHeader.vue'
import AppChat from './components/layout/AppChat.vue'
</script>

<template>
  <AppHeader />
  <RouterView />
  <AppChat />
</template>
