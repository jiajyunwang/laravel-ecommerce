<script setup>
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import AppChat from './components/AppChat.vue'
</script>

<template>
  <AppHeader />
  <RouterView />
  <AppChat />
</template>
