<script setup>
import { ENDPOINTS } from "@/config/api";
import { useActivityStore } from "@/stores/useActivityStore";
import axios from "axios";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

//get pinia
const activityStore = useActivityStore();
const currentItem = computed(() => activityStore.currentItem);

// Inject global loading
const globalLoading = inject("globalLoading");

const code = ref("");
const location = ref("");
const scopeOfWork = ref("");

const route = useRoute();
const router = useRouter();

const activityId = route.params.id ?? null; // kalau ada ID berarti mode edit
const isEditMode = ref(!!activityId);

const selectedMaintenanceTypesCleaningCritical = ref([]);
const selectedMaintenanceTypesJustCleaning = ref([]);
const selectedMaintenanceTypesReplacementPart = ref([]);
const selectedMaintenanceTypesPreventivePM = ref([]);
const birthDate = ref("");

// file foto
const cleaningCriticalBeforeFiles = ref([]);
const cleaningCriticalAfterFiles = ref([]);
const justCleaningBeforeFiles = ref([]);
const justCleaningAfterFiles = ref([]);
const replacementPartBeforeFiles = ref([]);
const replacementPartAfterFiles = ref([]);
const preventivePmBeforeFiles = ref([]);
const preventivePmAfterFiles = ref([]);
const cleaningCriticalJsa = ref(null);
const justCleaningJsa = ref(null);
const replacementJsa = ref(null);
const preventiveJsa = ref(null);

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

const itemMachines = ref([]);
const totalItemMachines = ref(0);
const selectedItemMachine = ref(null);

console.log("tester ", currentItem.value);
if (currentItem.value != null) {
  console.log("date:", currentItem.value);
  location.value = currentItem.value.item_machine.location;
  code.value = currentItem.value.item_machine.code;
  scopeOfWork.value = currentItem.value.item_machine.scope_of_work;
  birthDate.value = currentItem.value.date;
}

// Ambil item machines
const fetchItemMachines = async () => {
  try {
    const res = await axios.get(ENDPOINTS.itemMachines);
    const result = res.data.data ?? res.data;
    itemMachines.value = result;

    totalItemMachines.value = Array.isArray(result) ? result.length : 0;

    // Kalau mode edit, langsung set value yang sesuai
    if (currentItem.value) {
      selectedItemMachine.value = currentItem.value.item_machine_id;
    }
  } catch (error) {
    console.error("Error fetching item machines:", error);
  }
};

const fetchActivityDetail = async () => {
  if (!activityId) return;
  try {
    const res = await axios.get(`${ENDPOINTS.activityTms}/${activityId}`);
    const data = res.data.data ?? res.data;

    // pastikan sudah ada itemMachines sebelum set value
    if (!itemMachines.value.length) {
      await fetchItemMachines();
    }

    selectedItemMachine.value = Number(data.item_machine_id);
    code.value = data.code ?? "";
    location.value = data.location ?? "";
    scopeOfWork.value = data.scope_of_work ?? "";
    birthDate.value = data.date ? data.date : "";

    if (data.cleaning_critical) {
      selectedMaintenanceTypesCleaningCritical.value = ["cleaning_critical"];
    }
    if (data.just_cleaning) {
      selectedMaintenanceTypesJustCleaning.value = ["just_cleaning"];
    }
    if (data.replacement_part) {
      selectedMaintenanceTypesReplacementPart.value = ["replacement_part"];
    }
    if (data.preventive_pm) {
      selectedMaintenanceTypesPreventivePM.value = ["preventive_pm"];
    }
  } catch (error) {
    console.error("Error fetching activity detail:", error);
  }
};

// Sync code/location/scope kalau ganti item machine
watch(selectedItemMachine, (newVal) => {
  if (newVal) {
    const selected = itemMachines.value.find((item) => item.id === newVal);
    if (selected) {
      code.value = selected.code ?? "";
      location.value = selected.location ?? "";
      scopeOfWork.value = selected.scope_of_work ?? "";
    }
  } else {
    code.value = "";
    location.value = "";
    scopeOfWork.value = "";
  }
});

// submit add/update
const submitForm = async () => {
  globalLoading?.show();
  const formData = new FormData();

  formData.append("item_machine_id", selectedItemMachine.value);
  formData.append("code", code.value);
  formData.append("location", location.value);
  formData.append("scope_of_work", scopeOfWork.value);
  formData.append("date", birthDate.value);

  // Foto
  cleaningCriticalBeforeFiles.value.forEach((file) => {
    formData.append("cleaning_criticals_foto_before[]", file);
  });
  cleaningCriticalAfterFiles.value.forEach((file) => {
    formData.append("cleaning_criticals_foto_after[]", file);
  });
  justCleaningBeforeFiles.value.forEach((file) => {
    formData.append("just_cleaning_foto_before[]", file);
  });
  justCleaningAfterFiles.value.forEach((file) => {
    formData.append("just_cleaning_foto_after[]", file);
  });
  replacementPartBeforeFiles.value.forEach((file) => {
    formData.append("replacement_part_foto_before[]", file);
  });
  replacementPartAfterFiles.value.forEach((file) => {
    formData.append("replacement_part_foto_after[]", file);
  });
  preventivePmBeforeFiles.value.forEach((file) => {
    formData.append("preventive_foto_before[]", file);
  });
  preventivePmAfterFiles.value.forEach((file) => {
    formData.append("preventive_foto_after[]", file);
  });

  // JSA
  if (replacementJsa.value) {
    formData.append("replacement_jsa", replacementJsa.value);
  }
  if (cleaningCriticalJsa.value) {
    formData.append("cleaning_critical_jsa", cleaningCriticalJsa.value);
  }
  if (justCleaningJsa.value) {
    formData.append("just_cleaning_jsa", justCleaningJsa.value);
  }
  if (preventiveJsa.value) {
    formData.append("preventive_pm_jsa", preventiveJsa.value);
  }

  try {
    let res;
    if (isEditMode.value) {
      // Update → sesuai route kamu POST /activity-tms-update/{id}
      res = await axios.post(
        `${ENDPOINTS.updateActivityTms}/${activityId}`,
        formData,
        {
          headers: { "Content-Type": "multipart/form-data" },
        }
      );
      snackbarMessage.value = "Activity TMS Updated Successfully!";
    } else {
      // Tambah
      res = await axios.post(ENDPOINTS.addactivityTms, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      snackbarMessage.value = "Add New Activity TMS Success!";
    }

    // Redirect setelah sukses (opsional)

    isSnackbarTopEndVisible.value = true;
    router.push("/activitytms");
  } catch (error) {
    console.error("Gagal kirim data:", error);
  } finally {
    globalLoading?.hide();
  }
};

onMounted(() => {
  fetchItemMachines();
  fetchActivityDetail();
});
</script>

<template>
  <div>
    <div class="d-flex flex-wrap justify-space-between gap-4 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 mb-1">Add a new Activity TMS</h4>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn variant="outlined" color="secondary"> Discard </VBtn>
        <VBtn variant="outlined" color="primary"> Save Draft </VBtn>
        <VBtn @click="submitForm">Publish Activity TMS</VBtn>
      </div>
    </div>

    <VRow>
      <VCol md="12">
        <!-- item machine -->
        <VCard class="mb-6">
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <VSelect
                  v-model="selectedItemMachine"
                  :items="itemMachines"
                  item-title="name"
                  item-value="id"
                  placeholder="Item Machine"
                  label="Item Machine"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="code"
                  label="Code"
                  readonly
                  placeholder="FXSK123U"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="location"
                  label="Location"
                  readonly
                  placeholder="Tower 1"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="scopeOfWork"
                  label="Scope_of_work"
                  readonly
                  placeholder="Safety"
                />
              </VCol>

              <VCol>
                <AppDateTimePicker
                  v-model="birthDate"
                  label="Date"
                  placeholder="Select Date"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- 👉 Product Image -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title> Maintenance Type </template>

            <!-- Checkbox -->
            <div class="d-flex flex-column mt-2">
              <!-- Cleaning Critical -->
              <VCheckbox
                label="Cleaning Critical"
                value="cleaning_critical"
                v-model="selectedMaintenanceTypesCleaningCritical"
              />
              <template v-if="selectedMaintenanceTypesCleaningCritical.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone
                      label="BEFORE"
                      v-model="cleaningCriticalBeforeFiles"
                    />
                  </div>
                  <div style="flex: 1">
                    <DropZone
                      label="AFTER"
                      v-model="cleaningCriticalAfterFiles"
                    />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput
                    v-model="cleaningCriticalJsa"
                    label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    prepend-icon="ri-upload-2-line"
                    show-size
                  />
                </VCardText>
              </template>

              <!-- Just Cleaning -->
              <VCheckbox
                label="Just Cleaning"
                value="just_cleaning"
                v-model="selectedMaintenanceTypesJustCleaning"
              />
              <template v-if="selectedMaintenanceTypesJustCleaning.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone
                      label="BEFORE"
                      v-model="justCleaningBeforeFiles"
                    />
                  </div>
                  <div style="flex: 1">
                    <DropZone label="AFTER" v-model="justCleaningAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput
                    v-model="justCleaningJsa"
                    label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    prepend-icon="ri-upload-2-line"
                    show-size
                  />
                </VCardText>
              </template>

              <!-- Replacement Part -->
              <VCheckbox
                label="Replacement Part"
                value="replacement_part"
                v-model="selectedMaintenanceTypesReplacementPart"
              />
              <template v-if="selectedMaintenanceTypesReplacementPart.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone
                      label="BEFORE"
                      v-model="replacementPartBeforeFiles"
                    />
                  </div>
                  <div style="flex: 1">
                    <DropZone
                      label="AFTER"
                      v-model="replacementPartAfterFiles"
                    />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput
                    v-model="replacementJsa"
                    label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    prepend-icon="ri-upload-2-line"
                    show-size
                  />
                </VCardText>
              </template>

              <!-- Preventive PM -->
              <VCheckbox
                label="Preventive PM"
                value="preventive_pm"
                v-model="selectedMaintenanceTypesPreventivePM"
              />
              <template v-if="selectedMaintenanceTypesPreventivePM.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone
                      label="BEFORE"
                      v-model="preventivePmBeforeFiles"
                    />
                  </div>
                  <div style="flex: 1">
                    <DropZone label="AFTER" v-model="preventivePmAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput
                    v-model="preventiveJsa"
                    label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    prepend-icon="ri-upload-2-line"
                    show-size
                  />
                </VCardText>
              </template>
            </div>
          </VCardItem>
        </VCard>
      </VCol>

      <VCol md="4" cols="12"> </VCol>
    </VRow>

    <!-- Snackbar -->
    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      :timeout="3000"
      location="top end"
      color="success"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </div>
</template>

<style lang="scss" scoped>
.drop-zone {
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
}
</style>

<style lang="scss">
.inventory-card {
  .v-radio-group,
  .v-checkbox {
    .v-selection-control {
      align-items: start !important;
    }

    .v-label.custom-input {
      border: none !important;
    }
  }
}

.ProseMirror {
  p {
    margin-block-end: 0;
  }

  padding: 0.5rem;
  outline: none;

  p.is-editor-empty:first-child::before {
    block-size: 0;
    color: #adb5bd;
    content: attr(data-placeholder);
    float: inline-start;
    pointer-events: none;
  }
}
</style>
