<script setup>
import { ENDPOINTS } from "@/config/api";
import { useActivityStore } from "@/stores/useActivityStore";
import axios from "axios";
import { computed, inject, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import Cookies from "js-cookie";


// Pinia store — filter + current item
const activityStore = useActivityStore();

// message snackbar
const isSnackbarTopEndVisible = ref(false);

// Inject global loading
const globalLoading = inject("globalLoading");

// filter
const selectedScopeOfWork = ref(null);

// --- Filter Tahun & Bulan — baca dari store agar persisten ---
const currentYear = new Date().getFullYear();
const currentMonth = String(new Date().getMonth() + 1).padStart(2, "0");

const selectedYear = ref(activityStore.selectedYear ?? currentYear);
const selectedMonth = ref(activityStore.selectedMonth ?? currentMonth);

// List tahun misalnya 5 tahun ke belakang & depan
const years = ref(Array.from({ length: 10 }, (_, i) => currentYear - 5 + i));
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

// baseURL untuk gambar & file
const baseURL = `${import.meta.env.VITE_FILE_BASE_URL}/`;

// State untuk dialog foto/JSA
const isDialogVisible = ref(false);
const selectedType = ref("");
const selectedData = ref([]);

const jsaFile = ref(null);

// catatan
const isNotesDialogVisible = ref(false);
const selectedActivity = ref(null);

// field catatan untuk semua maintenance types
const catatanTeamleaderCleaningCritical = ref("");
const catatanSupervisorCleaningCritical = ref("");
const catatanTeknisiCleaningCritical = ref("");

const catatanTeamleaderJustCleaning = ref("");
const catatanSupervisorJustCleaning = ref("");
const catatanTeknisiJustCleaning = ref("");

const catatanTeamleaderReplacementPart = ref("");
const catatanSupervisorReplacementPart = ref("");
const catatanTeknisiReplacementPart = ref("");

const catatanTeamleaderPreventivePm = ref("");
const catatanSupervisorPreventivePm = ref("");
const catatanTeknisiPreventivePm = ref("");

// track yang active
const hasCleaningCritical = ref(false);
const hasJustCleaning = ref(false);
const hasReplacementPart = ref(false);
const hasPreventivePm = ref(false);

function openNotes(activity) {
  selectedActivity.value = activity;

  // Reset semua catatan
  catatanTeamleaderCleaningCritical.value = activity.catatan_teamleader_cleaning_criticals || "";
  catatanSupervisorCleaningCritical.value = activity.catatan_supervisor_cleaning_criticals || "";
  catatanTeknisiCleaningCritical.value = activity.catatan_teknisi_cleaning_criticals || "";

  catatanTeamleaderJustCleaning.value = activity.catatan_teamleader_just_cleaning || "";
  catatanSupervisorJustCleaning.value = activity.catatan_supervisor_justcleaning || "";
  catatanTeknisiJustCleaning.value = activity.catatan_teknisi_just_cleaning || "";

  catatanTeamleaderReplacementPart.value = activity.catatan_teamleader_replacement_part || "";
  catatanSupervisorReplacementPart.value = activity.catatan_supervisor_replacement_part || "";
  catatanTeknisiReplacementPart.value = activity.catatan_teknisi_replacement_part || "";

  catatanTeamleaderPreventivePm.value = activity.catatan_teamleader_preventive_pm || "";
  catatanSupervisorPreventivePm.value = activity.catatan_supervisor_preventive_pm || "";
  catatanTeknisiPreventivePm.value = activity.catatan_teknisi_preventive_pm || "";

  // Tentukan yang aktif
  hasCleaningCritical.value = activity.cleaning_criticals?.length > 0;
  hasJustCleaning.value = activity.just_cleaning?.length > 0;
  hasReplacementPart.value = activity.replacement_part?.length > 0;
  hasPreventivePm.value = activity.preventive?.length > 0;

  isNotesDialogVisible.value = true;
}

async function saveNote() {
  try {
    globalLoading?.show();

    const payload = {
      catatan_teamleader_cleaning_criticals: catatanTeamleaderCleaningCritical.value,
      catatan_supervisor_cleaning_criticals: catatanSupervisorCleaningCritical.value,
      catatan_teknisi_cleaning_criticals: catatanTeknisiCleaningCritical.value,
      catatan_teamleader_just_cleaning: catatanTeamleaderJustCleaning.value,
      catatan_supervisor_justcleaning: catatanSupervisorJustCleaning.value,
      catatan_teknisi_just_cleaning: catatanTeknisiJustCleaning.value,
      catatan_teamleader_replacement_part: catatanTeamleaderReplacementPart.value,
      catatan_supervisor_replacement_part: catatanSupervisorReplacementPart.value,
      catatan_teknisi_replacement_part: catatanTeknisiReplacementPart.value,
      catatan_teamleader_preventive_pm: catatanTeamleaderPreventivePm.value,
      catatan_supervisor_preventive_pm: catatanSupervisorPreventivePm.value,
      catatan_teknisi_preventive_pm: catatanTeknisiPreventivePm.value,
    };

    const response = await axios.put(
      ENDPOINTS.updateSupervisorNote(selectedActivity.value.id),
      payload,
    );

    // Update selectedActivity dengan data terbaru dari API
    if (response.data.data) {
      selectedActivity.value = response.data.data;
      catatanTeamleaderCleaningCritical.value = response.data.data.catatan_teamleader_cleaning_criticals || "";
      catatanSupervisorCleaningCritical.value = response.data.data.catatan_supervisor_cleaning_criticals || "";
      catatanTeknisiCleaningCritical.value = response.data.data.catatan_teknisi_cleaning_criticals || "";
      catatanTeamleaderJustCleaning.value = response.data.data.catatan_teamleader_just_cleaning || "";
      catatanSupervisorJustCleaning.value = response.data.data.catatan_supervisor_justcleaning || "";
      catatanTeknisiJustCleaning.value = response.data.data.catatan_teknisi_just_cleaning || "";
      catatanTeamleaderReplacementPart.value = response.data.data.catatan_teamleader_replacement_part || "";
      catatanSupervisorReplacementPart.value = response.data.data.catatan_supervisor_replacement_part || "";
      catatanTeknisiReplacementPart.value = response.data.data.catatan_teknisi_replacement_part || "";
      catatanTeamleaderPreventivePm.value = response.data.data.catatan_teamleader_preventive_pm || "";
      catatanSupervisorPreventivePm.value = response.data.data.catatan_supervisor_preventive_pm || "";
      catatanTeknisiPreventivePm.value = response.data.data.catatan_teknisi_preventive_pm || "";
    }

    // Update activity dalam list
    const updatedActivityIndex = activityTms.value.findIndex(
      (item) => item.id === selectedActivity.value.id
    );
    if (updatedActivityIndex !== -1) {
      activityTms.value[updatedActivityIndex] = response.data.data;
    }

    snackbarMessage.value = "Catatan berhasil disimpan!";
    isSnackbarTopEndVisible.value = true;
    isNotesDialogVisible.value = false;
  } catch (error) {
    console.error("Error save note:", error);
    snackbarMessage.value = "Gagal menyimpan catatan!";
    snackbarColor.value = "error";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
}

//message snackbar
const snackbarMessage = ref("Add New Item Machine Success!");
const snackbarColor = ref("success");

const typeLabels = {
  cleaning_criticals: "Cleaning Critical",
  just_cleaning: "Just Cleaning",
  preventive: "Preventive",
  replacement_part: "Replacement Part",
};

// Foto before
const beforePhotos = computed(() =>
  selectedData.value.filter((item) => item.status === "before"),
);

// Foto after
const afterPhotos = computed(() =>
  selectedData.value.filter((item) => item.status === "after"),
);

// Fungsi buka dialog
function openDialog(type, data, activity) {
  selectedType.value = type;
  selectedData.value = data;

  if (type === "cleaning_criticals") {
    jsaFile.value = activity.jsa_file_cleaning_criticals
      ? baseURL + activity.jsa_file_cleaning_criticals
      : null;
  } else if (type === "just_cleaning") {
    jsaFile.value = activity.jsa_file_just_cleaning
      ? baseURL + activity.jsa_file_just_cleaning
      : null;
  } else if (type === "preventive") {
    jsaFile.value = activity.jsa_file_preventive
      ? baseURL + activity.jsa_file_preventive
      : null;
  } else if (type === "replacement_part") {
    jsaFile.value = activity.jsa_file_replacement_part
      ? baseURL + activity.jsa_file_replacement_part
      : null;
  }

  isDialogVisible.value = true;
}

// State tabel
const activityTms = ref([]);
const totalActivityTms = ref(0);
const searchQuery = ref("");
const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

// Ambil role dari cookie
const userData = Cookies.get("userData")
  ? JSON.parse(Cookies.get("userData"))
  : null;
const role = userData?.user?.role;

// Headers table (Actions untuk semua role)
const headers = computed(() => {
  return [
    { title: "Nama Mesin", key: "name" },
    { title: "Code", key: "code" },
    { title: "Lokasi", key: "location" },
    { title: "Scope of Work", key: "scope_of_work" },
    { title: "Date", key: "date" },
    { title: "Actions", key: "actions", sortable: false },
  ];
});

const scope_of_work = [
  { title: "Safety", value: "safety" },
  { title: "Production", value: "production" },
];

// Ambil data dari backend Laravel
const fetchActivityTms = async () => {
  try {
    isLoading.value = true;
    const res = await axios.get(
      `${ENDPOINTS.activityTms}?month=${selectedYear.value}-${selectedMonth.value}`,
    );
    const result = res.data.data ?? res.data;
    activityTms.value = result;
    totalActivityTms.value = Array.isArray(result) ? result.length : 0;
  } catch (error) {
    console.error("Error fetching activity TMS:", error);
    activityTms.value = [];
    totalActivityTms.value = 0;
  } finally {
    isLoading.value = false;
  }
};

const router = useRouter();

function handleEdit(item) {
  activityStore.setCurrentItem(item);
  router.push(`/activitytms/edit?id=${item.id}`);
}

function handleDetail(item) {
  activityStore.setCurrentItem(item);
  router.push(`/activitytms/detail?id=${item.id}`);
}

const exportToExcel = async () => {
  try {
    const res = await axios.get(
      ENDPOINTS.activityTmsExport(selectedYear.value, selectedMonth.value),
    );
    window.open(res.data.data.download_link, "_blank");
  } catch (err) {
    console.error("Gagal export excel:", err);
  }
};

// --- Simpan filter ke store saat berubah agar persisten ---
watch([selectedYear, selectedMonth], ([year, month]) => {
  activityStore.setFilter(year, month);
  fetchActivityTms();
});

onMounted(() => {
  fetchActivityTms();
});

// Delete activity
const deleteActivityTms = async (id) => {
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.addactivityTms}/${id}`);
    activityTms.value = activityTms.value.filter((item) => item.id !== id);
    snackbarMessage.value = "Delete Activity TMS Completed!";
    snackbarColor.value = "success";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Error deleting activity TMS:", error);
    snackbarMessage.value = "Gagal menghapus Activity TMS!";
    snackbarColor.value = "error";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

// CONFIRM DELETE DIALOG
const isConfirmDeleteDialogVisible = ref(false);
const itemToDeleteId = ref(null);

const openDeleteConfirm = (id) => {
  itemToDeleteId.value = id;
  isConfirmDeleteDialogVisible.value = true;
};

const handleConfirmDelete = async () => {
  if (!itemToDeleteId.value) return;
  isConfirmDeleteDialogVisible.value = false;
  await deleteActivityTms(itemToDeleteId.value);
  itemToDeleteId.value = null;
};

const resolveUserStatusVariant = (status) => {
  return status ? "success" : "error";
};

// Filter
const filteredActivityTms = computed(() => {
  return activityTms.value.filter((item) => {
    const machine = item.item_machine || {};
    const matchesScope = selectedScopeOfWork.value
      ? machine.scope_of_work === selectedScopeOfWork.value
      : true;
    const matchesSearch = searchQuery.value
      ? machine.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        machine.code?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        machine.location
          ?.toLowerCase()
          .includes(searchQuery.value.toLowerCase())
      : true;
    return matchesScope && matchesSearch;
  });
});

watch(selectedScopeOfWork, () => {
  page.value = 1;
});
</script>

<template>
  <section>
    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
      :color="snackbarColor"
      timeout="3000"
    >
      {{ snackbarMessage }}
    </VSnackbar>
    <VCard>
      <VCardTitle>Filters</VCardTitle>
      <VCardText>
        <VRow dense justify="space-between" align="center">
          <VCol cols="12" sm="4" md="3">
            <VSelect
              v-model="selectedScopeOfWork"
              label="Select Scope of Work"
              :items="scope_of_work"
              clearable
              density="compact"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardText>
        <VRow>
          <!-- Pilih Tahun -->
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedYear"
              label="Select Year"
              :items="years"
            />
          </VCol>

          <!-- Pilih Bulan -->
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedMonth"
              label="Select Month"
              :items="months"
              item-title="label"
              item-value="value"
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
          @click="exportToExcel"
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
        </div>
      </VCardText>

      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredActivityTms"
        :loading="isLoading"
        :items-per-page="itemsPerPage"
        class="text-no-wrap"
      >
        <!-- nama mesin -->
        <template #item.name="{ item }">
          {{ item.item_machine?.name }}
        </template>

        <!-- Code -->
        <template #item.code="{ item }">
          {{ item.item_machine?.code }}
        </template>

        <!-- Lokasi -->
        <template #item.location="{ item }">
          {{ item.item_machine?.location }}
        </template>

        <!-- Scope of Work -->
        <template #item.scope_of_work="{ item }">
          {{ item.item_machine?.scope_of_work }}
        </template>

        <!-- date -->
        <template #item.date="{ item }">
          {{ item.date }}
        </template>

        <!-- Actions — semua role -->
        <template #item.actions="{ item }">
          <IconBtn size="small" @click="handleEdit(item)">
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>

          <IconBtn size="small" @click="handleDetail(item)">
            <VIcon icon="ri-eye-line" />
          </IconBtn>

          <IconBtn size="small" @click="openDeleteConfirm(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <!-- Catatan -->
          <IconBtn size="small" @click="openNotes(item)">
            <VIcon icon="ri-chat-4-line" />
          </IconBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Dialog Foto Before/After & JSA -->
    <VDialog v-model="isDialogVisible" max-width="800px">
      <VCard>
        <VCardTitle class="bg-primary text-white">
          {{ typeLabels[selectedType] }}
        </VCardTitle>

        <VCardText>
          <!-- BEFORE -->
          <VChip color="error" text-color="red" class="mb-2 font-weight-bold">
            BEFORE
          </VChip>
          <VRow v-if="beforePhotos.length">
            <VCol
              v-for="(photo, i) in beforePhotos"
              :key="'before-' + i"
              cols="6"
            >
              <VImg
                :src="baseURL + photo.foto"
                aspect-ratio="1"
                class="rounded border"
                cover
              />
            </VCol>
          </VRow>
          <div v-else class="text-grey">Tidak ada foto sebelum</div>

          <!-- AFTER -->
          <VChip
            color="success"
            text-color="white"
            class="font-weight-bold mt-2 mb-2"
          >
            AFTER
          </VChip>
          <VRow v-if="afterPhotos.length">
            <VCol
              v-for="(photo, i) in afterPhotos"
              :key="'after-' + i"
              cols="6"
            >
              <VImg
                :src="baseURL + photo.foto"
                aspect-ratio="1"
                class="rounded border"
                cover
              />
            </VCol>
          </VRow>
          <div v-else class="text-grey">Tidak ada foto sesudah</div>

          <!-- FILE JSA -->
          <div class="mt-6">
            <h4 class="mb-2">JSA File</h4>
            <div v-if="jsaFile">
              <VBtn :href="jsaFile" target="_blank" color="primary">
                📄 Download JSA
              </VBtn>
            </div>
            <div v-else class="text-grey">Tidak ada file JSA</div>
          </div>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" @click="isDialogVisible = false">Tutup</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog Notes — tampilkan semua maintenance types -->
    <VDialog v-model="isNotesDialogVisible" max-width="700px">
      <VCard>
        <VCardTitle class="bg-primary text-white">
          Catatan Activity
        </VCardTitle>
        <VCardText>
          <div v-if="selectedActivity" class="mb-3">
            <p><strong>Mesin:</strong> {{ selectedActivity.item_machine?.name }}</p>
            <p><strong>Kode:</strong> {{ selectedActivity.item_machine?.code }}</p>
          </div>

          <VDivider class="mb-4" />

          <!-- Cleaning Critical -->
          <div v-if="hasCleaningCritical">
            <h4 class="text-h6 mb-3 text-primary font-weight-bold">Cleaning Critical</h4>
            <VLabel class="mb-1 font-weight-bold">Catatan Team Leader</VLabel>
            <VTextarea
              v-model="catatanTeamleaderCleaningCritical"
              placeholder="Tulis catatan Team Leader..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Supervisor</VLabel>
            <VTextarea
              v-model="catatanSupervisorCleaningCritical"
              placeholder="Tulis catatan Supervisor..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Teknisi</VLabel>
            <VTextarea
              v-model="catatanTeknisiCleaningCritical"
              placeholder="Tulis catatan Teknisi..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-4"
            />

            <VDivider class="mb-4" />
          </div>

          <!-- Just Cleaning -->
          <div v-if="hasJustCleaning">
            <h4 class="text-h6 mb-3 text-primary font-weight-bold">Just Cleaning</h4>
            <VLabel class="mb-1 font-weight-bold">Catatan Team Leader</VLabel>
            <VTextarea
              v-model="catatanTeamleaderJustCleaning"
              placeholder="Tulis catatan Team Leader..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Supervisor</VLabel>
            <VTextarea
              v-model="catatanSupervisorJustCleaning"
              placeholder="Tulis catatan Supervisor..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Teknisi</VLabel>
            <VTextarea
              v-model="catatanTeknisiJustCleaning"
              placeholder="Tulis catatan Teknisi..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-4"
            />

            <VDivider class="mb-4" />
          </div>

          <!-- Replacement Part -->
          <div v-if="hasReplacementPart">
            <h4 class="text-h6 mb-3 text-primary font-weight-bold">Replacement Part</h4>
            <VLabel class="mb-1 font-weight-bold">Catatan Team Leader</VLabel>
            <VTextarea
              v-model="catatanTeamleaderReplacementPart"
              placeholder="Tulis catatan Team Leader..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Supervisor</VLabel>
            <VTextarea
              v-model="catatanSupervisorReplacementPart"
              placeholder="Tulis catatan Supervisor..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Teknisi</VLabel>
            <VTextarea
              v-model="catatanTeknisiReplacementPart"
              placeholder="Tulis catatan Teknisi..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-4"
            />

            <VDivider class="mb-4" />
          </div>

          <!-- Preventive PM -->
          <div v-if="hasPreventivePm">
            <h4 class="text-h6 mb-3 text-primary font-weight-bold">Preventive PM</h4>
            <VLabel class="mb-1 font-weight-bold">Catatan Team Leader</VLabel>
            <VTextarea
              v-model="catatanTeamleaderPreventivePm"
              placeholder="Tulis catatan Team Leader..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Supervisor</VLabel>
            <VTextarea
              v-model="catatanSupervisorPreventivePm"
              placeholder="Tulis catatan Supervisor..."
              rows="2"
              auto-grow
              variant="outlined"
              class="mb-3"
            />

            <VLabel class="mb-1 font-weight-bold">Catatan Teknisi</VLabel>
            <VTextarea
              v-model="catatanTeknisiPreventivePm"
              placeholder="Tulis catatan Teknisi..."
              rows="2"
              auto-grow
              variant="outlined"
            />
          </div>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="outlined" @click="isNotesDialogVisible = false">
            Tutup
          </VBtn>
          <VBtn color="primary" variant="flat" rounded="lg" @click="saveNote">
            Simpan
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Confirm Delete Dialog -->
    <VDialog v-model="isConfirmDeleteDialogVisible" max-width="450px">
      <VCard>
        <VCardTitle class="text-h5 text-center pa-6">
          Hapus Activity TMS?
        </VCardTitle>
        <VCardText class="text-center pb-6">
          Apakah Anda yakin ingin menghapus data Activity TMS ini?
        </VCardText>
        <VCardActions class="d-flex justify-center pb-6 gap-3">
          <VBtn color="secondary" variant="outlined" @click="isConfirmDeleteDialogVisible = false">
            Batal
          </VBtn>
          <VBtn color="error" variant="flat" @click="handleConfirmDelete">
            Ya, Hapus
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
