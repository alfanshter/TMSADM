<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { computed, onMounted, ref } from "vue";

const page = ref(1);
const itemsPerPage = ref(10);
const searchQuery = ref("");
const isLoading = ref(false);
const isAddNewItemMachinesDrawerVisible = ref(false);

const selectedScopeOfWork = ref(null);
const scope_of_work = ["Mechanical", "Electrical", "HVAC"];

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
const fetchScheduleData = async (month) => {
  try {
    isLoading.value = true;
    const res = await axios.get(`${ENDPOINTS.ACTIVITY_SUMMARY}?month=${month}`);

    if (res.data.status && res.data.data.length) {
      // Cari semua tanggal unik dari semua item
      const allDates = new Set();
      res.data.data.forEach((item) => {
        (item.dates || []).forEach((d) => allDates.add(d));
      });
      const sortedDates = Array.from(allDates).sort();

      // Update headers dynamic
      headers.value = [
        { title: "No", key: "no" },
        { title: "Nama Mesin", key: "name" },
        { title: "Nomor Mesin", key: "code" },
        { title: "Lokasi", key: "location" },
        { title: "ACT / Month", key: "act" },
        ...sortedDates.map((d, i) => ({ title: d, key: `date_${i}` })),
        { title: "Actions", key: "actions", sortable: false },
      ];

      // Data untuk tabel
      itemMachines.value = res.data.data.map((item, index) => {
        const row = {
          id: index + 1,
          no: index + 1,
          name: item.name,
          code: item.code,
          location: item.location,
          act: item.act_per_month + "x",
        };
        sortedDates.forEach((d, i) => {
          row[`date_${i}`] = item.dates.includes(d) ? "✓" : "";
        });
        return row;
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

const deleteItemMachine = (id) => {
  itemMachines.value = itemMachines.value.filter((item) => item.id !== id);
};

const openEditDrawer = (item) => {
  console.log("Edit:", item);
};

// --- Export PDF ---
const exportPDF = () => {
  const doc = new jsPDF("l", "mm", "a4");
  doc.setFontSize(14);
  doc.text("Schedule Maintenance", 14, 15);

  // Ambil headers (judul kolom)
  const head = [headers.value.map((h) => h.title)];
  // Ambil body data
  const body = itemMachines.value.map((item) =>
    headers.value.map((h) => item[h.key] || "")
  );

  autoTable(doc, {
    head,
    body,
    startY: 25,
    styles: { fontSize: 8, halign: "center", valign: "middle" },
    headStyles: { fillColor: [41, 128, 185] },
  });

  doc.save("schedule.pdf");
};

const currentMonth = new Date().toISOString().slice(0, 7);
onMounted(() => {
  fetchScheduleData(currentMonth); // default ambil bulan sekarang
});
</script>

<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Filters</VCardTitle>
    </VCardItem>
    <VCardText>
      <VRow>
        <VCol cols="12" sm="4">
          <VSelect
            v-model="selectedScopeOfWork"
            label="Select Scope of Work"
            placeholder="Select Scope of Work"
            :items="scope_of_work"
            clearable
            clear-icon="ri-close-line"
          />
        </VCol>
      </VRow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4 align-center">
      <VBtn
        variant="outlined"
        color="secondary"
        prepend-icon="ri-upload-2-line"
        @click="exportPDF"
      >
        Export
      </VBtn>
      <VSpacer />
      <div class="d-flex align-center gap-4 flex-wrap">
        <div class="app-user-search-filter" style="min-width: 250px; flex: 1">
          <VTextField
            v-model="searchQuery"
            placeholder="Search Machine"
            density="compact"
            variant="outlined"
            hide-details
          />
        </div>
        <VBtn @click="isAddNewItemMachinesDrawerVisible = true">
          Add New Schedule
        </VBtn>
      </div>
    </VCardText>

    <VDataTable
      v-model:page="page"
      :headers="headers"
      :items="filteredItemMachines"
      :loading="isLoading"
      class="text-no-wrap rounded-0"
      :items-per-page="itemsPerPage"
    >
      <template
        v-for="header in headers"
        v-slot:[`item.${header.key}`]="{ item }"
      >
        <span>{{ item[header.key] }}</span>
      </template>

      <template #item.actions="{ item }">
        <VBtn
          size="small"
          color="error"
          variant="text"
          @click="deleteItemMachine(item.id)"
        >
          Delete
        </VBtn>
        <VBtn size="small" variant="text" @click="openEditDrawer(item)">
          Edit
        </VBtn>
      </template>

      <template #bottom>
        <VDivider />
        <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
          <div
            class="d-flex align-center gap-x-2 text-medium-emphasis text-base"
          >
            Rows Per Page:
            <VSelect
              v-model="itemsPerPage"
              class="per-page-select"
              variant="plain"
              :items="[10, 20, 25, 50, 100]"
            />
          </div>
          <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
            {{ page }} / {{ Math.ceil(totalItemMachines / itemsPerPage) }}
          </p>
          <div class="d-flex gap-x-2 align-center me-2">
            <VBtn
              icon="ri-arrow-left-s-line"
              variant="text"
              density="comfortable"
              color="high-emphasis"
              :disabled="page <= 1"
              @click="page <= 1 ? (page = 1) : page--"
            />
            <VBtn
              icon="ri-arrow-right-s-line"
              density="comfortable"
              variant="text"
              color="high-emphasis"
              :disabled="page >= Math.ceil(totalItemMachines / itemsPerPage)"
              @click="
                page >= Math.ceil(totalItemMachines / itemsPerPage)
                  ? (page = Math.ceil(totalItemMachines / itemsPerPage))
                  : page++
              "
            />
          </div>
        </div>
      </template>
    </VDataTable>
  </VCard>
</template>
