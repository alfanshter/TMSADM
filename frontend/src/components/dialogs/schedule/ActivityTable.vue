<script setup>
import { ref, computed } from 'vue'
import ScheduleDialog from './ScheduleDialog.vue'

// --- Reactive state ---
const isListActivity = ref(false) // untuk kontrol dialog
const searchQuery = ref('')
const page = ref(1)
const itemsPerPage = ref(10)
const isLoading = ref(false)

// --- Table headers ---
const headers = ref([
  { title: "No", key: "no" },
  { title: "Nama Mesin", key: "name" },
  { title: "Nomor Mesin", key: "code" },
  { title: "Lokasi", key: "location" },
  { title: "ACT / Month", key: "act" },
  { title: "Week 1", key: "week_1" },
  { title: "Week 2", key: "week_2" },
  { title: "Week 3", key: "week_3" },
  { title: "Week 4", key: "week_4" },
])

// --- Dummy data ---
const itemMachines = ref([
  { no: 1, name: 'Mesin A', code: 'A001', location: 'Jakarta', act: '4x', week_1: 1, week_2: 0, week_3: 1, week_4: 0 },
  { no: 2, name: 'Mesin B', code: 'B001', location: 'Bandung', act: '3x', week_1: 0, week_2: 1, week_3: 1, week_4: 1 },
])

// --- Filter pencarian ---
const filteredItemMachines = computed(() => {
  if (!searchQuery.value) return itemMachines.value
  return itemMachines.value.filter(item =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})
</script>

<template>
  <VCard>
    <VCardText>
      <VTextField v-model="searchQuery" placeholder="Search Machine" />
    </VCardText>

    <VDataTable
      v-model:page="page"
      :headers="headers"
      :items="filteredItemMachines"
      :items-per-page="itemsPerPage"
      :loading="isLoading"
    >
      <template v-for="header in headers" v-slot:[`item.${header.key}`]="{ item }">
        <span v-if="['week_1','week_2','week_3','week_4'].includes(header.key)">
          <span
            v-if="item[header.key] > 0"
            @click="isListActivity = true"
            style="cursor: pointer; color: green;"
          >
            ✔
          </span>
        </span>
        <span v-else>{{ item[header.key] }}</span>
      </template>
    </VDataTable>

    <!-- Schedule Dialog -->
    <ScheduleDialog v-model:is-dialog-visible="isListActivity" />
  </VCard>
</template>
