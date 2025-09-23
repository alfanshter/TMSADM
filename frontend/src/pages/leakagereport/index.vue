<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewleakagereportDrawer from "@/views/apps/leakagereport/AddNewleakagereportDrawer.vue";
import axios from "axios";
import { computed, onMounted, ref, inject } from "vue";
import Cookies from "js-cookie";

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

// Table state
const leakageReports = ref([]);
const searchQuery = ref("");
const page = ref(1);
const itemsPerPage = ref(10);
const isLoading = ref(false);

// Ambil role dari cookie
const userData = Cookies.get("userData") ? JSON.parse(Cookies.get("userData")) : null;
const role = userData?.user?.role;

// Global loading
const globalLoading = inject("globalLoading");

// Table headers (Actions hanya untuk admin/team_leader)
const headers = computed(() => {
  const baseHeaders = [
    { title: "Location", key: "lokasi" },
    { title: "Date", key: "date" },
    { title: "Files", key: "file_scan" },
  ];
  if (role === "admin" || role === "team_leader" || role === "supervisor") {
    baseHeaders.push({ title: "Actions", key: "actions", sortable: false });
  }
  return baseHeaders;
});

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
              url: `https://backtmsadm.bimasaktiluhur.com/storage/${r.file_scan}`,
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

// Delete report
const deleteLeakageReport = async (id) => {
  if (!confirm("Yakin ingin menghapus report ini?")) return;
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.leakageReports}/${id}`);
    snackbarMessage.value = "Delete Leakage Report Completed!";
    isSnackbarTopEndVisible.value = true;
    fetchLeakageReports();
  } catch (err) {
    console.error(err);
    snackbarMessage.value = "Failed to delete report";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

// Open drawer untuk edit
const openEditDrawer = (report) => {
  editedLeakageReport.value = { ...report };
  isDrawerVisible.value = true;
};

// Filter data
const filteredLeakageReports = computed(() =>
  leakageReports.value.filter((item) => {
    if (!searchQuery.value) return true;
    return (
      item.lokasi?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.date?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  })
);

// Fungsi ikon file
const getFileIcon = (fileName) => {
  const ext = fileName.split(".").pop().toLowerCase();
  if (ext === "pdf") return { icon: "mdi-file-pdf-box", color: "red" };
  if (["xls", "xlsx"].includes(ext)) return { icon: "mdi-file-excel-box", color: "green" };
  if (["doc", "docx"].includes(ext)) return { icon: "mdi-file-word-box", color: "blue" };
  return { icon: "mdi-file", color: "grey" };
};

onMounted(fetchLeakageReports);
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
        <VTextField v-model="searchQuery" placeholder="Search Report" density="compact" />
        <VSpacer />
        <VBtn v-if="role === 'admin' || role === 'team_leader'" @click="openDrawer">
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
            <div v-for="file in item.files" :key="file.name" class="d-flex align-center my-1">
              <VIcon :icon="getFileIcon(file.name).icon" :color="getFileIcon(file.name).color" start />
              <a :href="file.url" target="_blank">{{ file.name }}</a>
            </div>
          </div>
          <span v-else>No Files</span>
        </template>

        <!-- Actions (hanya admin/team_leader) -->
        <template v-if="role === 'admin' || role === 'team_leader'" #item.actions="{ item }">
          <IconBtn size="small" @click="deleteLeakageReport(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <IconBtn size="small">
            <VIcon icon="ri-eye-line" />
          </IconBtn>

          <IconBtn size="small" @click="openEditDrawer(item)">
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>
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