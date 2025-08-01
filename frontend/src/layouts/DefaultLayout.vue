<template>
    <div class="layout">
      <Sidebar />
      <div class="main">
        <Header />
        <div class="content">
          <router-view />
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import Sidebar from '@/components/Sidebar.vue'
  import Header from '@/components/Header.vue'
  </script>
  
  <style scoped>
  .layout {
    display: flex;
    height: 100vh;
  }
  .main {
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  .content {
    padding: 16px;
    background-color: #fff;
    flex: 1;
  }
  </style>
  