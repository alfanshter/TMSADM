<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ENDPOINTS } from '@/config/api'

const statistics = ref([
  {
    title: 'User',
    stats: '0',
    icon: 'ri-user-star-line',
    color: 'primary',
  },
  {
    title: 'Item Machine',
    stats: '0',
    icon: 'ri-pie-chart-2-line',
    color: 'warning',
  },
  {
    title: 'Sparepart',
    stats: '0',
    icon: 'ri-arrow-left-right-line',
    color: 'info',
  },
])

const moreList = [
  { title: 'Last 28 Days', value: 'Last 28 Days' },
  { title: 'Last Month', value: 'Last Month' },
  { title: 'Last Year', value: 'Last Year' },
]

onMounted(async () => {
  try {
    // Ambil data paralel
    const [usersRes, itemsRes, sparepartsRes] = await Promise.all([
      axios.get(ENDPOINTS.users),
      axios.get(ENDPOINTS.itemMachines),
      axios.get(ENDPOINTS.spareparts)
    ])

    // Deteksi apakah API mengembalikan array langsung atau dibungkus dalam objek data
    const getCount = (res) => Array.isArray(res.data) ? res.data.length : res.data.data.length

    statistics.value = [
      {
        title: 'User',
        stats: getCount(usersRes).toString(),
        icon: 'ri-user-star-line',
        color: 'primary',
      },
      {
        title: 'Item Machine',
        stats: getCount(itemsRes).toString(),
        icon: 'ri-pie-chart-2-line',
        color: 'warning',
      },
      {
        title: 'Sparepart',
        stats: getCount(sparepartsRes).toString(),
        icon: 'ri-arrow-left-right-line',
        color: 'info',
      },
    ]
  } catch (err) {
    console.error('Gagal mengambil data statistik:', err)
  }
})
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>User Overview</VCardTitle>
      <template #append>
        <div class="mt-n7 me-n3">
          <MoreBtn :menu-list="moreList" />
        </div>
      </template>
    </VCardItem>

    <VCardText>
      <div class="d-flex justify-space-between flex-column flex-sm-row gap-4 flex-wrap">
        <div
          v-for="item in statistics"
          :key="item.title"
          class="d-flex align-center"
        >
          <VAvatar
            :color="item.color"
            rounded
            variant="tonal"
            size="40"
            class="me-3"
          >
            <VIcon size="24" :icon="item.icon" />
          </VAvatar>

          <div class="d-flex flex-column">
            <h5 class="text-h5">{{ item.stats }}</h5>
            <div class="text-body-1">{{ item.title }}</div>
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
