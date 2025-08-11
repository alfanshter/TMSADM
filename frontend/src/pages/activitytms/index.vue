<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { computed, inject, onMounted, ref, watch } from "vue";

// Inject global loading
const globalLoading = inject("globalLoading");

// filter
const selectedScopeOfWork = ref(null);

// baseURL untuk gambar & file
const baseURL = "http://localhost:8000/storage/";

// State untuk dialog foto/JSA
const isDialogVisible = ref(false);
const selectedType = ref("");
const selectedData = ref([]);

//message snackbar
const snackbarMessage = ref("Add New Item Machine Success!");

const typeLabels = {
  cleaning_criticals: "Cleaning Critical",
  just_cleaning: "Just Cleaning",
  preventive: "Preventive",
  replacement_part: "Replacement Part",
};

// Foto before
const beforePhotos = computed(() =>
  selectedData.value.filter((item) => item.status === "before")
);

// Foto after
const afterPhotos = computed(() =>
  selectedData.value.filter((item) => item.status === "after")
);

// File JSA
const jsaFile = computed(() => {
  const found = selectedData.value.find((d) => d.jsa_file);
  return found ? baseURL + found.jsa_file : null;
});

// Fungsi buka dialog
function openDialog(type, data) {
  console.log("DATA DARI BACKEND:", data);
  selectedType.value = type;
  selectedData.value = data;
  isDialogVisible.value = true;
}

// State tabel
const activityTms = ref([]);
const totalActivityTms = ref(0);
const searchQuery = ref("");
const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

const headers = [
  { title: "Nama Mesin", key: "name" },
  { title: "Code", key: "code" },
  { title: "Lokasi", key: "location" },
  { title: "Scope of Work", key: "scope_of_work" },
  { title: "Maintenance Type", key: "maintenance_type" },
  { title: "Date", key: "date" },
  { title: "Actions", key: "actions", sortable: false },
];

const scope_of_work = [
  { title: "Safety", value: "safety" },
  { title: "Production", value: "production" },
];

// Ambil data dari backend Laravel
const fetchActivityTms = async () => {
  try {
    isLoading.value = true;
    const res = await axios.get(ENDPOINTS.activityTms);
    const result = res.data.data ?? res.data;
    activityTms.value = result;
    totalActivityTms.value = Array.isArray(result) ? result.length : 0;
  } catch (error) {
    console.error("Error fetching activity TMS:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchActivityTms();
});

// Delete activity
const deleteActivityTms = async (id) => {
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.addactivityTms}/${id}`);
    await fetchActivityTms();

    // Tampilkan snackbar
    snackbarMessage.value = "Delete Activity TMS Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Error deleting activity TMS:", error);
  } finally {
    globalLoading?.hide();
  }
};

// Dummy resolve status
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
      :color="snackbarMessage.includes('Delete') ? 'error' : 'success'"
      timeout="3000"
    >
      {{ snackbarMessage }}
    </VSnackbar>
    <VCard>
      <VCardTitle>Filters</VCardTitle>
      <VCardText>
        <VRow dense>
          <VCol cols="12" sm="6">
            <VSelect
              v-model="selectedScopeOfWork"
              label="Select Scope of Work"
              :items="scope_of_work"
              clearable
              density="compact"
            />
          </VCol>
          <VCol cols="12" sm="6">
            <VTextField
              v-model="searchQuery"
              placeholder="Search Item Machine"
              density="compact"
            />
          </VCol>
        </VRow>
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

        <!-- Maintenance Type -->
        <template #item.maintenance_type="{ item }">
          <div class="d-flex flex-column gap-1">
            <VBtn
              v-if="item.cleaning_criticals?.length"
              variant="text"
              @click="openDialog('cleaning_criticals', item.cleaning_criticals)"
            >
              Cleaning Critical
            </VBtn>

            <VBtn
              v-if="item.just_cleaning?.length"
              variant="text"
              @click="openDialog('just_cleaning', item.just_cleaning)"
            >
              Just Cleaning
            </VBtn>

            <VBtn
              v-if="item.preventive?.length"
              variant="text"
              @click="openDialog('preventive', item.preventive)"
            >
              Preventive
            </VBtn>

            <VBtn
              v-if="item.replacement_part?.length"
              variant="text"
              @click="openDialog('replacement_part', item.replacement_part)"
            >
              Replacement Part
            </VBtn>
          </div>
        </template>

        <!-- date -->
        <template #item.date="{ item }">
          {{ item.date }}
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="resolveUserStatusVariant(item.status === 1)"
            size="small"
          >
            {{ item.status === 1 ? "Aktif" : "Tidak Aktif" }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <!-- Tombol Edit -->
          <IconBtn size="small" @click="editActivityTms(item)">
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>

          <!-- Tombol Delete -->
          <IconBtn size="small" @click="deleteActivityTms(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Dialog Foto Before/After & JSA -->
    <VDialog v-model="isDialogVisible" max-width="800px">
      <VCard>
        <!-- Judul Dialog -->
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

        <!-- Tombol Close -->
        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" @click="isDialogVisible = false">Tutup</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
