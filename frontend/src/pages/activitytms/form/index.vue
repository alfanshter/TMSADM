<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { ref, watch } from "vue";

const selectedMaintenanceTypesCleaningCritical = ref([]);
const selectedMaintenanceTypesJustCleaning = ref([]);
const selectedMaintenanceTypesReplacementPart = ref([]);
const selectedMaintenanceTypesPreventivePM = ref([]);
const birthDate = ref("");

//file foto
const cleaningCriticalBeforeFiles = ref([]);
const cleaningCriticalAfterFiles = ref([]);
const justCleaningBeforeFiles = ref([]);
const justCleaningAfterFiles = ref([]);
const replacementPartBeforeFiles = ref([]);
const replacementPartAfterFiles = ref([]);
const preventivePmBeforeFiles = ref([]);
const preventivePmAfterFiles = ref([]);
const cleaningCriticalJsa = ref([]);
const justCleaningJsa = ref([]);
const replacementJsa = ref(null);
const preventiveJsa = ref(null);

const code = ref("");
const location = ref("");
const scopeOfWork = ref("");

const itemMachines = ref([]);
const totalItemMachines = ref(0);
const selectedItemMachine = ref(null);
const maintenanceOptions = [
  { label: "Cleaning Critical", value: "cleaning_critical" },
  { label: "Just Cleaning", value: "just_cleaning" },
  { label: "Replacement Part", value: "replacement_part" },
  { label: "Preventive PM", value: "preventive_pm" },
];

// Fetch item machines from API
const fetchItemMachines = async () => {
  try {
    const res = await axios.get(ENDPOINTS.itemMachines);
    const result = res.data.data ?? res.data;
    itemMachines.value = result;
    totalItemMachines.value = Array.isArray(result) ? result.length : 0;
    console.log("Data itemMachines:", itemMachines.value);
  } catch (error) {
    console.error("Error fetching item machines:", error);
  }
};

watch(selectedItemMachine, (newVal) => {
  if (newVal) {
    // Jika kamu simpan ID saja di selectedItemMachine:
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

//fungsi untuk menambah data ke activity tms API

const submitForm = async () => {
  const formData = new FormData();

  formData.append("item_machine_id", selectedItemMachine.value);
  formData.append("code", code.value);
  formData.append("location", location.value);
  formData.append("scope_of_work", scopeOfWork.value);
  formData.append("date", birthDate.value);

  // Format yang diminta backend: prefix_foto_status[]
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

  // JSA files (tidak perlu diubah)
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
    const res = await axios.post(ENDPOINTS.addactivityTms, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    console.log("Sukses:", res.data);
  } catch (error) {
    console.error("Gagal kirim data:", error);
  }
};

onMounted(() => {
  fetchItemMachines();
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
