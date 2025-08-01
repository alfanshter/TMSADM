<template>
    <div class="sidebar">
      <h3>Menu</h3>
      <router-link to="/dashboard">Dashboard</router-link>
    </div>
  </template>
  
  <style scoped>
  .sidebar {
    width: 200px;
    background: #333;
    color: white;
    padding: 16px;
  }
  .sidebar a {
    display: block;
    color: white;
    margin-top: 8px;
  }
  </style>
  