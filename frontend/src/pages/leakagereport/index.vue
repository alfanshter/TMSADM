<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewleakagereportDrawer from "@/views/apps/leakagereport/AddNewleakagereportDrawer.vue";
import axios from "axios";
import { computed, onMounted, ref } from "vue";

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

// Drawer
const isDrawerVisible = ref(false);
const editedLeakageReport = ref(null); // untuk edit

const openDrawer = () => {
  editedLeakageReport.value = null;
  isDrawerVisible.value = true;
};

// State
const leakageReports = ref([]);
const searchQuery = ref("");
const page = ref(1);
const itemsPerPage = ref(10);
const isLoading = ref(false);

// Table headers
const headers = [
  { title: "Location", key: "lokasi" },
  { title: "Date", key: "date" },
  { title: "Files", key: "file_scan" },
  { title: "Actions", key: "actions", sortable: false },
];

// Fetch data
const fetchLeakageReports = async () => {
  try {
    isLoading.value = true;
    const res = await axios.get(ENDPOINTS.leakageReports);
    leakageReports.value = res.data.data.map((r) => ({
      ...r,
      files: r.file_scan
        ? [
            {
              name: r.file_scan.split("/").pop(),
              url: `http://127.0.0.1:8000/storage/${r.file_scan}`,
            },
          ]
        : [],
    }));
  } catch (err) {
    console.error(err);
    snackbarMessage.value = "Failed to fetch data";
    isSnackbarTopEndVisible.value = true;
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchLeakageReports);

// Delete report
const deleteLeakageReport = async (id) => {
  if (!confirm("Yakin ingin menghapus report ini?")) return;
  try {
    await axios.delete(ENDPOINTS.deleteLeakageReport(id));
    snackbarMessage.value = "Delete Leakage Report Completed!";
    isSnackbarTopEndVisible.value = true;
    fetchLeakageReports();
  } catch (err) {
    console.error(err);
    snackbarMessage.value = "Failed to delete report";
    isSnackbarTopEndVisible.value = true;
  }
};

// Open drawer untuk edit
const openEditDrawer = (report) => {
  editedLeakageReport.value = { ...report };
  isDrawerVisible.value = true;
};

// Filter data
const filteredLeakageReports = computed(() => {
  return leakageReports.value.filter((item) => {
    if (!searchQuery.value) return true;
    return (
      item.lokasi?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.date?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  });
});

// Fungsi ikon file
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
        <VBtn @click="openDrawer"> Add New Leakage Report </VBtn>
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
        <template #item.lokasi="{ item }">
          <span>{{ item.lokasi }}</span>
        </template>

        <!-- Date -->
        <template #item.date="{ item }">
          <span>{{ item.date }}</span>
        </template>

        <!-- Files -->
        <template #item.file_scan="{ item }">
          <div v-if="item.files.length">
            <div
              v-for="file in item.files"
              :key="file.name"
              class="d-flex align-center my-1"
            >
              <VIcon
                :icon="getFileIcon(file.name).icon"
                :color="getFileIcon(file.name).color"
                start
              />
              <a :href="file.url" target="_blank">{{ file.name }}</a>
            </div>
          </div>
          <span v-else>No Files</span>
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

    <!-- Drawer -->
    <AddNewleakagereportDrawer
      v-show="isDrawerVisible"
      v-model:isDrawerOpen="isDrawerVisible"
      :edited-report="editedLeakageReport"
      @report-data="fetchLeakageReports"
    />
  </section>
</template>
