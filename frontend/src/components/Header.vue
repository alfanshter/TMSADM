<template>
    <header class="header">
      <h2>Dashboard</h2>
      <button @click="logout">Logout</button>
    </header>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router'
  const router = useRouter()
  
  function logout() {
    localStorage.removeItem('token')
    router.push('/login')
  }
  </script>
  
  <style scoped>
  .header {
    background: #eee;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  </style>
  