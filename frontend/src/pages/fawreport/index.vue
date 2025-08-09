<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { computed, onMounted, ref } from "vue";

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

// State
const fawReports = ref([]);
const totalFawReports = ref(0);
const searchQuery = ref("");
const page = ref(1);
const itemsPerPage = ref(10);
const isLoading = ref(false);

const isAddNewFawReportDrawerVisible = ref(false);
const isEditFawReportDrawerVisible = ref(false);
const editedFawReport = ref(null);

// Table headers
const headers = [
  { title: "Description", key: "description" },
  { title: "Result", key: "result" },
  { title: "Date", key: "date" },
  { title: "Report Image", key: "image" },
  { title: "Actions", key: "actions", sortable: false },
];

// Fungsi untuk hilangkan tag HTML
const stripHtml = (html) => {
  if (!html) return "";
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = html;
  return tempDiv.textContent || tempDiv.innerText || "";
};

// Ambil data dari backend
const fetchFawReports = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(ENDPOINTS.fawreport);
    console.log("Data dari backend:", res.data);
    fawReports.value = res.data.data.map((r) => ({
      id: r.id,
      description: stripHtml(r.description),
      result: stripHtml(r.result),
      date: r.date,
      image: r.photos?.[0] ? `/storage/${r.photos[0].photo_path}` : "",
    }));
    totalFawReports.value = fawReports.value.length;
  } catch (err) {
    console.error("Gagal fetch FAW reports:", err);
  } finally {
    isLoading.value = false;
  }
};

// Add report ke backend
const addNewFawReport = async (formData) => {
  try {
    const res = await axios.post(ENDPOINTS.fawreport, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    console.log("FAW Report berhasil ditambahkan:", res.data);

    const newReport = res.data.data;
    fawReports.value.unshift({
      id: newReport.id,
      description: stripHtml(newReport.description),
      result: stripHtml(newReport.result),
      date: newReport.date,
      image: newReport.photos?.[0]
        ? `/storage/${newReport.photos[0].photo_path}`
        : "",
    });

    totalFawReports.value++;
    snackbarMessage.value = "FAW Report berhasil dipublish!";
    isSnackbarTopEndVisible.value = true;
  } catch (err) {
    console.error("Gagal tambah FAW report:", err);
  }
};

// Edit report
const openEditDrawer = (report) => {
  editedFawReport.value = { ...report };
  isEditFawReportDrawerVisible.value = true;
};

// Delete report
const deleteFawReport = async (id) => {
  try {
    await axios.delete(`${ENDPOINTS.fawreport}/${id}`);
    fawReports.value = fawReports.value.filter((r) => r.id !== id);
    totalFawReports.value = fawReports.value.length;
    snackbarMessage.value = "Delete FAW Report Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (err) {
    console.error("Gagal hapus FAW report:", err);
  }
};

// Filter data
const filteredFawReports = computed(() => {
  return fawReports.value.filter((item) => {
    if (!searchQuery.value) return true;
    return (
      item.description
        ?.toLowerCase()
        .includes(searchQuery.value.toLowerCase()) ||
      item.result?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.date?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  });
});

onMounted(() => {
  fetchFawReports();
});
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
        <VCardTitle>FAW Reports</VCardTitle>
      </VCardItem>

      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <VTextField
          v-model="searchQuery"
          placeholder="Search Report"
          density="compact"
        />
        <VSpacer />
        <VBtn @click="$router.push('/fawreport/form')">
          Add New FAW Report
        </VBtn>
      </VCardText>

      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredFawReports"
        :loading="isLoading"
        class="text-no-wrap rounded-0"
        :items-per-page="itemsPerPage"
      >
        <!-- Description -->
        <template #item.description="{ item }">
          <span>{{ item.description }}</span>
        </template>

        <!-- Result -->
        <template #item.result="{ item }">
          <span>{{ item.result }}</span>
        </template>

        <!-- Date -->
        <template #item.date="{ item }">
          <span>{{ item.date }}</span>
        </template>

        <!-- Image -->
        <template #item.image="{ item }">
          <VImg
            v-if="item.image"
            :src="item.image"
            max-width="80"
            max-height="80"
            class="rounded my-2"
          />
          <span v-else>No Image</span>
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
            @click="deleteFawReport(item.id)"
          >
            Delete
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </section>
</template>
