<script setup>
import { computed, ref } from "vue";

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("Add New Leakage Report Success!");

// State (dummy data)
const leakageReports = ref([
  {
    id: 1,
    location: "Lantai 1",
    date: "2025-08-01",
    files: [
      {
        name: "manual-mesin-a.pdf",
        size: 204800,
        url: new URL("@/assets/file/manual-mesin-a.pdf", import.meta.url).href,
      },
    ],
    status: "Pending",
  },
  {
    id: 2,
    location: "Lantai 3",
    date: "2025-08-03",
    files: [
      {
        name: "laporan-perawatan.xlsx",
        size: 198400,
        url: new URL("@/assets/file/laporan-perawatan.xlsx", import.meta.url)
          .href,
      },
    ],
    status: "Done",
  },
  {
    id: 3,
    location: "Cust.blend",
    date: "2025-08-05",
    files: [],
    status: "Pending",
  },
]);

const totalLeakageReports = ref(leakageReports.value.length);
const searchQuery = ref("");
const page = ref(1);
const itemsPerPage = ref(10);
const isLoading = ref(false);

const isAddNewLeakageReportDrawerVisible = ref(false);
const isEditLeakageReportDrawerVisible = ref(false);
const editedLeakageReport = ref(null);

// Table headers
const headers = [
  { title: "Location", key: "location" },
  { title: "Date", key: "date" },
  { title: "Files", key: "files" },
  { title: "Status", key: "status" },
  { title: "Actions", key: "actions", sortable: false },
];

// Add report
const addNewLeakageReport = (newReport) => {
  newReport.id = Date.now();
  leakageReports.value.unshift(newReport);
  totalLeakageReports.value++;
  snackbarMessage.value = "Add New Leakage Report Success!";
  isSnackbarTopEndVisible.value = true;
};

// Edit report
const openEditDrawer = (report) => {
  editedLeakageReport.value = { ...report };
  isEditLeakageReportDrawerVisible.value = true;
};

const updateLeakageReport = (updatedReport) => {
  const index = leakageReports.value.findIndex(
    (u) => u.id === updatedReport.id
  );
  if (index !== -1) leakageReports.value[index] = updatedReport;
  snackbarMessage.value = "Update Leakage Report Completed!";
  isSnackbarTopEndVisible.value = true;
};

// Delete report
const deleteLeakageReport = (id) => {
  leakageReports.value = leakageReports.value.filter((r) => r.id !== id);
  totalLeakageReports.value = leakageReports.value.length;
  snackbarMessage.value = "Delete Leakage Report Completed!";
  isSnackbarTopEndVisible.value = true;
};

// Filter data
const filteredLeakageReports = computed(() => {
  return leakageReports.value.filter((item) => {
    if (!searchQuery.value) return true;
    return (
      item.location?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.status?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.date?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  });
});

// Function untuk dapatkan ikon berdasarkan ekstensi
const getFileIcon = (fileName) => {
  const ext = fileName.split(".").pop().toLowerCase();
  if (ext === "pdf") return { icon: "mdi-file-pdf-box", color: "red" };
  if (["xls", "xlsx"].includes(ext))
    return { icon: "mdi-file-excel-box", color: "green" };
  if (["doc", "docx"].includes(ext))
    return { icon: "mdi-file-word-box", color: "blue" };
  return { icon: "mdi-file", color: "grey" };
};
</script>

<template>
  <section>
    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
      :color="snackbarMessage.includes('Delete') ? 'error' : 'success'"
      timeout="3000"
    >
      {{ snackbarMessage }}
    </VSnackbar>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Leakage Reports</VCardTitle>
      </VCardItem>

      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <VTextField
          v-model="searchQuery"
          placeholder="Search Report"
          density="compact"
        />
        <VSpacer />
        <VBtn @click="isAddNewLeakageReportDrawerVisible = true">
          Add New Leakage Report
        </VBtn>
      </VCardText>

      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredLeakageReports"
        :loading="isLoading"
        class="text-no-wrap rounded-0"
        :items-per-page="itemsPerPage"
      >
        <!-- Location -->
        <template #item.location="{ item }">
          <span>{{ item.location }}</span>
        </template>

        <!-- Date -->
        <template #item.date="{ item }">
          <span>{{ item.date }}</span>
        </template>

        <!-- Files -->
        <template #item.files="{ item }">
          <div v-if="item.files.length">
            <div
              v-for="(file, idx) in item.files"
              :key="idx"
              class="d-flex align-center my-1"
            >
              <VIcon
                :icon="getFileIcon(file.name).icon"
                :color="getFileIcon(file.name).color"
                start
              />
              <a
                :href="file.url"
                target="_blank"
                class="text-primary text-decoration-none"
              >
                {{ file.name }}
              </a>
            </div>
          </div>
          <span v-else>No Files</span>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip :color="item.status === 'Done' ? 'success' : 'warning'" small>
            {{ item.status }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn size="small" color="primary" @click="openEditDrawer(item)">
            Edit
          </VBtn>
          <VBtn
            class="ml-2"
            size="small"
            color="error"
            @click="deleteLeakageReport(item.id)"
          >
            Delete
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </section>
</template>
