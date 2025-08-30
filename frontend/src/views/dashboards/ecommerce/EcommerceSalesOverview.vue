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

const isLoading = ref(true) // loader lokal

onMounted(async () => {
  try {
    const now = new Date()
    const year = now.getFullYear()
    const monthNum = String(now.getMonth() + 1).padStart(2, '0')
    const month = `${year}-${monthNum}`

    const res = await axios.get(`${ENDPOINTS.dashboardStatistics}?month=${month}`)
    const data = res.data?.data || {}

    statistics.value = [
      {
        title: 'User',
        stats: (data.user_count || 0).toString(),
        icon: 'ri-user-star-line',
        color: 'primary',
      },
      {
        title: 'Item Machine',
        stats: (data.item_machine_count || 0).toString(),
        icon: 'ri-pie-chart-2-line',
        color: 'warning',
      },
      {
        title: 'Sparepart',
        stats: (data.stok_sparepart_count || 0).toString(),
        icon: 'ri-arrow-left-right-line',
        color: 'info',
      },
    ]
  } catch (err) {
    console.error('Gagal mengambil data statistik:', err)
  } finally {
    isLoading.value = false
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
        <!-- Skeleton loader -->
        <template v-if="isLoading">
          <VSkeletonLoader v-for="n in 3" :key="n" type="list-item-avatar" />
        </template>

        <!-- Data muncul setelah loading selesai -->
        <template v-else>
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
        </template>
      </div>
    </VCardText>
  </VCard>
</template>
