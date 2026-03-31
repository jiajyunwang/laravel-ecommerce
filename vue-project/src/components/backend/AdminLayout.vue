<script setup>
import { onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import AdminHeader from './AdminHeader.vue'

const cssFiles = [
  { id: 'admin-style-css', href: '/backend/css/admin-style.css' },
  { id: 'admin-editor-css', href: '/backend/css/editor.css' },
]

onMounted(() => {
  cssFiles.forEach(({ id, href }) => {
    const link = document.createElement('link')
    link.id = id
    link.rel = 'stylesheet'
    link.href = href
    document.head.appendChild(link)
  })
})

onUnmounted(() => {
  cssFiles.forEach(({ id }) => {
    const el = document.getElementById(id)
    if (el) el.remove()
  })
})
</script>

<template>
  <AdminHeader />
  <RouterView />
</template>
