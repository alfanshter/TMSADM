<script setup>
import { ENDPOINTS } from "@/config/api";
import { useFawReportStore } from "@/stores/useFawReportStore";
import axios from "axios";
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

//Pinia send another activity
const FawReportStore = useFawReportStore();

// Inject global loading
const globalLoading = inject("globalLoading");

// State
const fawReports = ref([]);
const totalFawReports = ref(0);
const searchQuery = ref("");
const page = ref(1);
const itemsPerPage = ref(10);
const isLoading = ref(false);

const isEditFawReportDrawerVisible = ref(false);
const editedFawReport = ref(null);

const baseUrl = import.meta.env.VITE_API_URL;

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
    fawReports.value = res.data.data.map((r) => {
      return {
        id: r.id,
        description: stripHtml(r.description),
        result: stripHtml(r.result),
        date: r.date,
        image: r.photos?.length
          ? r.photos.map((p) => `${baseUrl}/storage/${p.photo_path}`)
          : [],
      };
    });
    totalFawReports.value = fawReports.value.length;
  } catch (err) {
    console.error("Gagal fetch FAW reports:", err);
  } finally {
    isLoading.value = false;
  }
};

// Tambahan: fungsi untuk update data di tabel setelah edit
// Update report
const updateFawReportInList = (updatedReport) => {
  const index = fawReports.value.findIndex((r) => r.id === updatedReport.id);
  if (index !== -1) {
    fawReports.value[index] = {
      id: updatedReport.id,
      description: stripHtml(updatedReport.description),
      result: stripHtml(updatedReport.result),
      date: updatedReport.date,
      image: updatedReport.photos?.[0]
        ? `${baseUrl}/storage/${updatedReport.photos[0].photo_path}`
        : "",
    };
  }
};

// Tambah report baru
const addNewFawReport = async (formData) => {
  try {
    const res = await axios.post(ENDPOINTS.fawreport, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    const newReport = res.data.data;

    fawReports.value.unshift({
      id: newReport.id,
      description: stripHtml(newReport.description),
      result: stripHtml(newReport.result),
      date: newReport.date,
      image: newReport.photos?.[0]
        ? `${baseUrl}/storage/${newReport.photos[0].photo_path}`
        : "",
    });

    totalFawReports.value++;
    snackbarMessage.value = "FAW Report berhasil dipublish!";
    isSnackbarTopEndVisible.value = true;
  } catch (err) {
    console.error("Error submit FAW Report:", err.response?.data || err);
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
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.fawreport}/${id}`);
    fawReports.value = fawReports.value.filter((r) => r.id !== id);
    totalFawReports.value = fawReports.value.length;
    snackbarMessage.value = "Delete FAW Report Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (err) {
    console.error("Gagal hapus FAW report:", err);
  } finally {
    globalLoading?.hide();
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

const router = useRouter();

function handleEdit(item) {
  FawReportStore.setCurrentItem(item); // simpan data di store
  console.log("Item yang dipilih:", item);
  console.log("ID yang dipilih:", item.id);
  router.push(`/fawreport/form?id=${item.id}`);
}

// Tambahan: dengarkan event dari store setelah update
FawReportStore.$subscribe((mutation, state) => {
  if (state.updatedItem) {
    updateFawReportInList(state.updatedItem);
  }
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
          <span class="d-inline-block-text-truncate" style="max-width: 200px">{{
            item.description
          }}</span>
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
          <div v-if="item.image && item.image.length">
            <VDialog max-width="500">
              <template #activator="{ props }">
                <VBtn v-bind="props" variant="text" size="small">
                  Lihat Gambar ({{ item.image.length }})
                </VBtn>
              </template>

              <template #default="{ isActive }">
                <VCard>
                  <VCardTitle>Gambar Laporan</VCardTitle>
                  <VCardText>
                    <div class="d-flex flex-wrap gap-2">
                      <VImg
                        v-for="(img, idx) in item.image"
                        :key="idx"
                        :src="img"
                        max-width="200"
                        class="rounded"
                      />
                    </div>
                  </VCardText>
                  <VCardActions>
                    <VSpacer />
                    <VBtn text="Tutup" @click="isActive.value = false" />
                  </VCardActions>
                </VCard>
              </template>
            </VDialog>
          </div>
          <span v-else>No Image</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn size="small" color="primary" @click="handleEdit(item)">
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
