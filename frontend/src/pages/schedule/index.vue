<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { computed, onMounted, ref } from "vue";
import ScheduleDialog from "@/components/dialogs/schedule/ScheduleDialog.vue";
const page = ref(1);
const itemsPerPage = ref(10);
const searchQuery = ref("");
const isLoading = ref(false);

const isListActivity = ref(false)
const selectedActivityIds = ref([])

// --- Filter Tahun & Bulan ---
const currentYear = new Date().getFullYear();
const currentMonth = String(new Date().getMonth() + 1).padStart(2, "0");

const selectedYear = ref(currentYear);
const selectedMonth = ref(currentMonth);

// List tahun misalnya 5 tahun ke belakang & depan
const years = ref(
  Array.from({ length: 10 }, (_, i) => currentYear - 5 + i)
);
const months = ref([
  { label: "January", value: "01" },
  { label: "February", value: "02" },
  { label: "March", value: "03" },
  { label: "April", value: "04" },
  { label: "May", value: "05" },
  { label: "June", value: "06" },
  { label: "July", value: "07" },
  { label: "August", value: "08" },
  { label: "September", value: "09" },
  { label: "October", value: "10" },
  { label: "November", value: "11" },
  { label: "December", value: "12" },
]);

// --- Data
const itemMachines = ref([]);
const totalItemMachines = computed(() => itemMachines.value.length);
const headers = ref([
  { title: "No", key: "no" },
  { title: "Nama Mesin", key: "name" },
  { title: "Nomor Mesin", key: "code" },
  { title: "Lokasi", key: "location" },
  { title: "ACT / Month", key: "act" },
]);

// --- Filter pencarian
const filteredItemMachines = computed(() => {
  if (!searchQuery.value) return itemMachines.value;
  return itemMachines.value.filter((item) =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// --- Ambil data dari backend ---
const fetchScheduleData = async () => {
  try {
    isLoading.value = true;

    const res = await axios.get(
      `${ENDPOINTS.ACTIVITY_SUMMARY}?month=${selectedYear.value}-${selectedMonth.value}`
    );
    

    if (res.data.status && res.data.data.length) {
      // Header tetap Week 1-4
      headers.value = [
        { title: "No", key: "no" },
        { title: "Nama Mesin", key: "name" },
        { title: "Nomor Mesin", key: "code" },
        { title: "Lokasi", key: "location" },
        { title: "ACT / Month", key: "act" },
        { title: "Week 1", key: "week_1" },
        { title: "Week 2", key: "week_2" },
        { title: "Week 3", key: "week_3" },
        { title: "Week 4", key: "week_4" },

      ];

      // Data untuk tabel
      itemMachines.value = res.data.data.map((item, index) => {
        return {
          id: index + 1,
          no: index + 1,
          name: item.name,
          code: item.code,
          location: item.location,
          act: item.act_per_month + "x",
          week_1: item.week_1 > 0 ? "✔" : "",
          week_2: item.week_2 > 0 ? "✔" : "",
          week_3: item.week_3 > 0 ? "✔" : "",
          week_4: item.week_4 > 0 ? "✔" : "",
          week_1_ids: item.week_1_ids,
          week_2_ids: item.week_2_ids,
          week_3_ids: item.week_3_ids,
          week_4_ids: item.week_4_ids,
        };
      });
    } else {
      itemMachines.value = [];
    }
  } catch (err) {
    console.error("Error fetching schedule:", err);
    itemMachines.value = [];
  } finally {
    isLoading.value = false;
  }
};



// EXPORT TO EXCEL
const exportToExcel = async (year) => {
  if (!year) {
    console.error("Tahun belum dipilih");
    return;
  }
  try {
    const res = await axios.get(ENDPOINTS.exportSchedule(selectedYear.value, selectedMonth.value));
    window.open(res.data.data.download_link, "_blank");

  } catch (err) {
    console.error("Gagal export excel:", err);
  }
};

function openScheduleDialog(item, weekKey) {
  // Ambil array ids dari weekKey
  selectedActivityIds.value = item[`${weekKey}_ids`]
  
  isListActivity.value = true
}

// --- Re-fetch jika tahun / bulan berubah ---
watch([selectedYear, selectedMonth], () => {
  fetchScheduleData();
});


onMounted(() => {
  fetchScheduleData(); // default ambil bulan sekarang
});
</script>

<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Filters</VCardTitle>
    </VCardItem>
    <VCardText>
      <VRow>


        <!-- Pilih Tahun -->
        <VCol cols="12" sm="4">
          <VSelect v-model="selectedYear" label="Select Year" :items="years" />
        </VCol>

        <!-- Pilih Bulan -->
        <VCol cols="12" sm="4">
          <VSelect v-model="selectedMonth" label="Select Month" :items="months" item-title="label" item-value="value" />
        </VCol>
      </VRow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4 align-center">
      <VBtn variant="outlined" color="secondary" prepend-icon="ri-upload-2-line" @click="exportToExcel">
        Export
      </VBtn>
      <VSpacer />
      <div class="d-flex align-center gap-4 flex-wrap">
        <div class="app-user-search-filter" style="min-width: 250px; flex: 1">
          <VTextField v-model="searchQuery" placeholder="Search Machine" density="compact" variant="outlined"
            hide-details />
        </div>
      </div>
    </VCardText>

    <VDataTable v-model:page="page" :headers="headers" :items="filteredItemMachines" :loading="isLoading"
      class="text-no-wrap rounded-0" :items-per-page="itemsPerPage">
      <template v-for="header in headers" v-slot:[`item.${header.key}`]="{ item }">
        <span>{{ item[header.key] }}</span>
      </template>

      <template v-for="header in headers" v-slot:[`item.${header.key}`]="{ item }">
        <!-- Cek jika header week_1 - week_4  -->
        <span v-if="['week_1', 'week_2', 'week_3', 'week_4'].includes(header.key)">
          <span v-if="item[header.key] === '✔'"  @click="openScheduleDialog(item, header.key)"
   
            style="cursor: pointer; color: green;">
            ✔
          </span>
        </span>
        <span v-else>{{ item[header.key] }}</span>
      </template>
  
   
      <template #bottom>
        <VDivider />
        <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
          <div class="d-flex align-center gap-x-2 text-medium-emphasis text-base">
            Rows Per Page:
            <VSelect v-model="itemsPerPage" class="per-page-select" variant="plain" :items="[10, 20, 25, 50, 100]" />
          </div>
          <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
            {{ page }} / {{ Math.ceil(totalItemMachines / itemsPerPage) }}
          </p>
          <div class="d-flex gap-x-2 align-center me-2">
            <VBtn icon="ri-arrow-left-s-line" variant="text" density="comfortable" color="high-emphasis"
              :disabled="page <= 1" @click="page <= 1 ? (page = 1) : page--" />
            <VBtn icon="ri-arrow-right-s-line" density="comfortable" variant="text" color="high-emphasis"
              :disabled="page >= Math.ceil(totalItemMachines / itemsPerPage)" @click="
                page >= Math.ceil(totalItemMachines / itemsPerPage)
                  ? (page = Math.ceil(totalItemMachines / itemsPerPage))
                  : page++
                " />
          </div>
        </div>
      </template>



    </VDataTable>
    <ScheduleDialog
  v-model:isDialogVisible="isListActivity"
  :activity-ids="selectedActivityIds"
/>
  </VCard>
</template>