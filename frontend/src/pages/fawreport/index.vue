<script setup>
import mesin1 from "@/assets/images/mesin/mesin1.jpg";
import mesin2 from "@/assets/images/mesin/mesin2.jpg";
import { computed, ref } from "vue";

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("Add New FAW Report Success!");

// State
const fawReports = ref([
  {
    id: 1,
    description: "Maintenance check completed successfully",
    result: "Done",
    date: "2025-08-01",
    image: mesin1,
  },
  {
    id: 2,
    description: "Machine inspection revealed minor issues",
    result: "Pending",
    date: "2025-08-05",
    image: mesin2,
  },

  {
    id: 3,
    description: "Machine inspection revealed leakage issues",
    result: "Pending",
    date: "2025-08-05",
    image: "",
  },

  {
    id: 4,
    description: "Machine replacement part revealed leakage issues",
    result: "Pending",
    date: "2025-08-05",
    image: "",
  },
]);
const totalFawReports = ref(fawReports.value.length);
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

// Add report
const addNewFawReport = (newReport) => {
  newReport.id = Date.now();
  fawReports.value.unshift(newReport);
  totalFawReports.value++;
  snackbarMessage.value = "Add New FAW Report Success!";
  isSnackbarTopEndVisible.value = true;
};

// Edit report
const openEditDrawer = (report) => {
  editedFawReport.value = { ...report };
  isEditFawReportDrawerVisible.value = true;
};

const updateFawReport = (updatedReport) => {
  const index = fawReports.value.findIndex((u) => u.id === updatedReport.id);
  if (index !== -1) fawReports.value[index] = updatedReport;
  snackbarMessage.value = "Update FAW Report Completed!";
  isSnackbarTopEndVisible.value = true;
};

// Delete report
const deleteFawReport = (id) => {
  fawReports.value = fawReports.value.filter((r) => r.id !== id);
  totalFawReports.value = fawReports.value.length;
  snackbarMessage.value = "Delete FAW Report Completed!";
  isSnackbarTopEndVisible.value = true;
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
        <VBtn @click="isAddNewFawReportDrawerVisible = true">
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
          <span v-html="item.description"></span>
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
