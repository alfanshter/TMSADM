<script setup>
import EcommerceSalesOverview from '@/views/dashboards/ecommerce/EcommerceSalesOverview.vue'
import illustration1 from '@images/cards/illustration-1.png'
import illustration2 from '@images/cards/illustration-2.png'
import illustration3 from '@images/cards/illustration-3.png'
import illustration4 from '@images/cards/illustration-4.png'

const statisticsWithImages = [
  {
    title: 'Activity TMS',
    subtitle: 'Year of 2025',
    stats: '10',
    image: illustration1,
    imgWidth: 99,
    color: 'primary',
  },
  {
    title: 'FAW Report',
    subtitle: 'Year of 2025',
    stats: '20',
    image: illustration2,
    imgWidth: 85,
    color: 'success',
  },
  {
    title: 'Leakage Report',
    subtitle: 'Year of 2025',
    stats: '25',
    image: illustration3,
    imgWidth: 85,
    color: 'success',
  },

  {
    title: 'Schedule',
    subtitle: 'Year of 2025',
    stats: '10',
    image: illustration4,
    imgWidth: 85,
    color: 'success',
  },
]
</script>

<template>
  <VRow class="match-height">
    <!-- 👉 Sales Overview -->
    <VCol
      cols="12"
      md="6"
    >
      <EcommerceSalesOverview />
    </VCol>

    <!-- 👉 Images Cards -->
    <VCol
      v-for="statistics in statisticsWithImages"
      :key="statistics.title"
      cols="12"
      sm="6"
      md="3"
    >
      <CardStatisticsWithImages v-bind="statistics" />
    </VCol>


    
  
    
  </VRow>
</template>

<style lang="scss">
@use "@core/scss/template/libs/apex-chart.scss";
</style>
