<script setup>
import { ENDPOINTS } from "@/config/api";
import { useFawReportStore } from "@/stores/useFawReportStore";
import axios from "axios";
import { computed, onMounted, ref, inject } from "vue";
import { useRouter } from "vue-router";
import Cookies from "js-cookie";

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


// Ambil role dari cookie
const userData = Cookies.get("userData")
  ? JSON.parse(Cookies.get("userData"))
  : null;
const role = userData?.user?.role;

// Headers (Actions hanya untuk admin/team_leader)
const headers = computed(() => {
  const baseHeaders = [
    { title: "Description", key: "description" },
    { title: "Result", key: "result" },
    { title: "Date", key: "date" },
    { title: "Report Image", key: "image" },
  ];
  if (role === "admin" || role === "team_leader") {
    baseHeaders.push({ title: "Actions", key: "actions", sortable: false });
  }
  return baseHeaders;
});

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

    const res = await axios.get(`${ENDPOINTS.fawreport}?month=${selectedYear.value}-${selectedMonth.value}`);
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

const exportFawReports = async () => {
  try {

    globalLoading?.show();
    const response = await axios.get(ENDPOINTS.fawReportExport(selectedYear.value, selectedMonth.value), {
      responseType: "blob", // penting agar file terbaca sebagai file
    });

    // Buat URL blob & trigger download
    const blob = new Blob([response.data], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `faw-reports-export-${
      new Date().toISOString().split("T")[0]
    }.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    snackbarMessage.value = "Export Excel berhasil!";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Gagal export FAW reports:", error);
    snackbarMessage.value = "Export Excel gagal!";
    isSnackbarTopEndVisible.value = true;
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
  // Navigasi ke form edit dengan query id
  router.push(`/fawreport/update?id=${item.id}`);
}

// Tambahan: dengarkan event dari store setelah update
FawReportStore.$subscribe((mutation, state) => {
  if (state.updatedItem) {
    updateFawReportInList(state.updatedItem);
  }
});

watch([selectedYear, selectedMonth], () => {
  fetchFawReports();
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

      <VCardText>
        <VRow>


          <!-- Pilih Tahun -->
          <VCol cols="12" sm="4">
            <VSelect v-model="selectedYear" label="Select Year" :items="years" />
          </VCol>

          <!-- Pilih Bulan -->
          <VCol cols="12" sm="4">
            <VSelect v-model="selectedMonth" label="Select Month" :items="months" item-title="label"
              item-value="value" />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <VTextField
          v-model="searchQuery"
          placeholder="Search Report"
          density="compact"
        />

        <VBtn
          variant="outlined"
          color="secondary"
          prepend-icon="ri-upload-2-line"
          @click="exportFawReports"
        >
          Export
        </VBtn>
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
          <IconBtn size="small" @click="deleteFawReport(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <IconBtn
            size="small"
            @click="$router.push(`/fawreport/detail?id=${item.id}`)"
          >
            <VIcon icon="ri-eye-line" />
          </IconBtn>

          <IconBtn size="small" @click="handleEdit(item)">
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>
        </template>
      </VDataTable>
    </VCard>
  </section>
</template>