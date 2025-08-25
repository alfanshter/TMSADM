<script setup>
import { ENDPOINTS } from "@/config/api";
import { useActivityStore } from "@/stores/useActivityStore";
import axios from "axios";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { VCardItem, VRow } from "vuetify/components";

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

const activityId = route.query.id ?? activityStore.currentItem?.id ?? null;
console.log("Activity ID:", activityId);

const selectedMaintenanceTypesCleaningCritical = ref([]);
const selectedMaintenanceTypesJustCleaning = ref([]);
const selectedMaintenanceTypesReplacementPart = ref([]);
const selectedMaintenanceTypesPreventivePM = ref([]);
const birthDate = ref("");
const production_downtime = ref("");

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
const safety_scan = ref(null);
const production_scan = ref(null);


// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");
const snackbarColor = ref("success"); // default


const itemMachines = ref([]);
const totalItemMachines = ref(0);
const selectedItemMachine = ref(null);

//sparepart
const itemSparepart = ref([]);
const selectedItemSparepart = ref([]);
const requiredQty = ref(1);
const sparepartList = ref([]);

// Headers untuk datatable
const sparepartHeaders = [
  { title: "Nama Sparepart", key: "nama_sparepart" },
  { title: "Jumlah", key: "qty" },
  { title: "Spec", key: "spec" },
  { title: "Loc", key: "loc" },
  { title: "Type", key: "type" },
  { title: "Aksi", key: "actions" },
];


// Dapatkan object sparepart yang dipilih
const selectedItemSparepartObj = computed(() =>
  itemSparepart.value.find((item) => item.id === selectedItemSparepart.value)
);

// Event saat pilih sparepart
const onSparepartSelect = () => {
  requiredQty.value = 1; // reset qty saat ganti sparepart
};

// Tambah ke list
const addSparepart = () => {
  if (!selectedItemSparepartObj.value) return;
  if (requiredQty.value < 1 || requiredQty.value > selectedItemSparepartObj.value.usages_sum_qty) {
    alert("Jumlah tidak valid!");
    return;
  }

  // cek jika sudah ada di list
  const existIndex = sparepartList.value.findIndex(
    (s) => s.id === selectedItemSparepartObj.value.id
  );
  if (existIndex !== -1) {
    sparepartList.value[existIndex].qty += requiredQty.value;
  } else {
    sparepartList.value.push({
      id: selectedItemSparepartObj.value.id,
      nama_sparepart: selectedItemSparepartObj.value.nama_sparepart,
      qty: requiredQty.value,
      loc: selectedItemSparepartObj.value.loc,
      spec: selectedItemSparepartObj.value.spec,
      type: selectedItemSparepartObj.value.type,
    });
  }

  // reset input
  selectedItemSparepart.value = null;
  requiredQty.value = 1;
};


// hapus sparepart
const removeSparepart = (index) => {
  sparepartList.value.splice(index, 1);
};



//scope of work
const incomingRs = ref("");
const incomingRt = ref("");
const incomingSt = ref("");
const outgoingRs = ref("");
const outgoingRt = ref("");
const outgoingSt = ref("");
const temp = ref("");
const deviation = ref("");

if (currentItem.value != null) {
  console.log("date:", currentItem.value);
  location.value = currentItem.value.item_machine.location;
  code.value = currentItem.value.item_machine.code;
  scopeOfWork.value = currentItem.value.item_machine.scope_of_work;
  birthDate.value = currentItem.value.date;
  production_downtime.value = currentItem.value.production_downtime;
}

// Ambil item machines
const fetchItemMachines = async () => {
  try {
    const res = await axios.get(ENDPOINTS.itemMachines);
    const result = res.data.data ?? res.data;
    itemMachines.value = result;

    totalItemMachines.value = Array.isArray(result) ? result.length : 0;
  } catch (error) {
    console.error("Error fetching item machines:", error);
  }
};

const fetchItemSparepart = async () => {
  try {
    const res = await axios.get(ENDPOINTS.spareparts);
    const result = res.data.data ?? res.data;
    itemSparepart.value = result;

  }
  catch (error) {
    console.error("Error fetching item sparepart", error);
  }
}

const headers = [
  {
    title: 'Nama',
    key: 'nama_sparepart',
  },
  {
    title: 'Spec',
    key: 'spec',
  },
  {
    title: 'Loc',
    key: 'loc',
    sortable: false,
  },
  {
    title: 'Category',
    key: 'category',
  },
  {
    title: 'QTY',
    key: 'qtc',
  },

  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
]


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
  formData.append("production_downtime", production_downtime.value);

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
    formData.append("jsa_file_replacement_part", replacementJsa.value);
  }
  if (cleaningCriticalJsa.value) {
    formData.append("jsa_file_cleaning_criticals", cleaningCriticalJsa.value);
  }
  if (justCleaningJsa.value) {
    formData.append("jsa_file_just_cleaning", justCleaningJsa.value);
  }
  if (preventiveJsa.value) {
    console.log("dinda oke");
    formData.append("jsa_file_preventive", preventiveJsa.value);
  }

  //Scope of work
  if (safety_scan.value) {
    formData.append("safety_scan", safety_scan.value);
  }
  if (production_scan.value) {
    formData.append("production_scan", production_scan.value);
  }

  formData.append("incoming_rs", incomingRs.value ?? "");
  formData.append("incoming_rt", incomingRt.value ?? "");
  formData.append("incoming_st", incomingSt.value ?? "");
  formData.append("outgoing_rs", outgoingRs.value ?? "");
  formData.append("outgoing_rt", outgoingRt.value ?? "");
  formData.append("outgoing_st", outgoingSt.value ?? "");
  formData.append("temp", temp.value ?? "");
  formData.append("deviation", deviation.value ?? "");

  // Tambah Sparepart List
  sparepartList.value.forEach((item, index) => {
    formData.append(`spareparts[${index}][id]`, item.id);
    formData.append(`spareparts[${index}][qty]`, item.qty);
  });
  try {
    let res;
    // Tambah
    res = await axios.post(ENDPOINTS.addactivityTms, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    snackbarMessage.value = "Add New Activity TMS Success!";
    snackbarColor.value = "success"; // warna hijau


    // Redirect setelah sukses (opsional)

    isSnackbarTopEndVisible.value = true;
    router.push("/activitytms");
  } catch (error) {
    // Cek jika ada response dari server
    if (error.response) {
      if (error.response.status === 422) {
        snackbarMessage.value = error.response.data.message;
        snackbarColor.value = "error"; // warna hijau
      } else {
        snackbarMessage.value = `Gagal:  ${error.response.statusText}`;
      }
    } else {
      // Error lain (network, timeout, dll)
      snackbarMessage.value = "Gagal: Terjadi kesalahan jaringan atau server!";
    }

    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

onMounted(() => {
  fetchItemMachines();
  fetchItemSparepart();
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
                <VAutocomplete v-model="selectedItemMachine" :items="itemMachines" item-title="name" item-value="id"
                  placeholder="Item Machine" label="Item Machine" />

              </VCol>
              <VCol cols="12" md="6">
                <VTextField v-model="code" label="Code" readonly placeholder="FXSK123U" />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField v-model="location" label="Location" readonly placeholder="Tower 1" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="scopeOfWork" label="Scope_of_work" readonly placeholder="Safety" />
              </VCol>

              <VCol>
                <AppDateTimePicker v-model="birthDate" label="Date" placeholder="Select Date" />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- production or safety -->
        <VCard class="mb-6" v-if="scopeOfWork == 'safety'">
          <VCardItem>
            <template #title> Scope of Work </template>
            <div class="d-flex flex-column mt-2">
              <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
              <VFileInput v-model="safety_scan" label="Pilih file dokumen"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp"
                prepend-icon="ri-upload-2-line" show-size />

            </div>
            <!-- Checkbox -->

          </VCardItem>

          <VCardItem>
            <VLabel class="mb-1">Incoming</VLabel>
            <VRow>
              <VCol cols="12" md="4">
                <VTextField v-model="incomingRs" label="Incoming R-S" placeholder="Incoming R-S" type="number" />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField v-model="incomingRt" label="Incoming R-T" placeholder="Incoming R-T" type="number" />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField v-model="incomingSt" label="Incoming S-T" placeholder="Incoming S-T" type="number" />
              </VCol>
            </VRow>
          </VCardItem>

          <VCardItem>
            <VLabel class="mb-1">Outgoing</VLabel>
            <VRow>
              <VCol cols="12" md="4">
                <VTextField v-model="outgoingRs" label="Outgoing R-S" placeholder="Outgoing R-S" type="number" />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField v-model="outgoingRt" label="Outgoing R-T" placeholder="Outgoing R-T" type="number" />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField v-model="outgoingSt" label="Outgoing S-T" placeholder="Outgoing S-T" type="number" />
              </VCol>
            </VRow>
          </VCardItem>

          <VCardItem>
            <VRow class="mt-1">
              <VCol cols="12" md="6">
                <VTextField v-model="temp" label="Temp in der C" placeholder="Temp in der C" type="number" />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField v-model="deviation" label="Deviation Status" placeholder="Deviation Status" />
              </VCol>
            </VRow>
          </VCardItem>
        </VCard>

        <!-- production or safety -->
        <VCard class="mb-6" v-if="scopeOfWork == 'production'">
          <VCardItem>
            <template #title> Scope of Work </template>
            <div class="d-flex flex-column mt-2">
              <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
              <VFileInput v-model="production_scan" label="Pilih file dokumen"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size />

            </div>
            <!-- Checkbox -->

          </VCardItem>

          <VCardItem>
            <div class="d-flex flex-column">
              <VLabel class=" mb-1">Downtime Production</VLabel>
              <VCol cols="12" md="6">
                <VTextField v-model="production_downtime" label="Downtime Production"  placeholder="minute" type="number" />
              </VCol>
           
            </div>
            <!-- Checkbox -->

          </VCardItem>
        </VCard>
        <!-- 👉 Product Image -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title> Maintenance Types </template>

            <!-- Checkbox -->
            <div class="d-flex flex-column mt-2">
              <!-- Cleaning Critical -->
              <VCheckbox label="Cleaning Critical" value="cleaning_critical"
                v-model="selectedMaintenanceTypesCleaningCritical" />
              <template v-if="selectedMaintenanceTypesCleaningCritical.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone label="BEFORE" v-model="cleaningCriticalBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <DropZone label="AFTER" v-model="cleaningCriticalAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput v-model="cleaningCriticalJsa" label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size />
                </VCardText>
              </template>

              <!-- Just Cleaning -->
              <VCheckbox label="Just Cleaning" value="just_cleaning" v-model="selectedMaintenanceTypesJustCleaning" />
              <template v-if="selectedMaintenanceTypesJustCleaning.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone label="BEFORE" v-model="justCleaningBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <DropZone label="AFTER" v-model="justCleaningAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput v-model="justCleaningJsa" label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size />
                </VCardText>
              </template>

              <!-- Replacement Part -->
              <VCheckbox label="Replacement Part" value="replacement_part"
                v-model="selectedMaintenanceTypesReplacementPart" />

              <template v-if="selectedMaintenanceTypesReplacementPart.length">
                <!-- Card utama Replacement Part -->
                <VCard class="pa-4 my-4" variant="outlined">
                  <!-- Pilih Item Machine & Input Jumlah -->
                  <VRow dense>
                    <VCol cols="12" md="6">
                      <VAutocomplete v-model="selectedItemSparepart" :items="itemSparepart" item-title="nama_sparepart"
                        item-value="id" label="Sparepart" placeholder="Cari / pilih sparepart" clearable
                        density="comfortable" @change="onSparepartSelect" />
                    </VCol>

                    <VCol cols="12" md="6" v-if="selectedItemSparepartObj">
                      <VTextField v-model.number="requiredQty" type="number"
                        :label="`Butuh berapa? (Stok tersedia: ${selectedItemSparepartObj.usages_sum_qty})`"
                        :max="selectedItemSparepartObj.usages_sum_qty" min="1" />
                      <VBtn color="primary" class="mt-2" @click="addSparepart">Add</VBtn>
                    </VCol>
                  </VRow>

                  <!-- Upload Foto Before & After -->
                  <VRow class="mt-4" dense>
                    <VCol cols="12" md="6">
                      <DropZone label="BEFORE" v-model="replacementPartBeforeFiles" />
                    </VCol>
                    <VCol cols="12" md="6">
                      <DropZone label="AFTER" v-model="replacementPartAfterFiles" />
                    </VCol>
                  </VRow>

                  <!-- Upload JSA -->
                  <VRow class="mt-4" dense>
                    <VCol cols="12">
                      <VLabel class="mb-2">Upload JSA File</VLabel>
                      <VFileInput v-model="replacementJsa" label="Pilih file dokumen"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size
                        clearable />
                    </VCol>
                  </VRow>
                </VCard>

                <!-- 👉 Datatable  -->
                <!-- Datatable Sparepart -->
                <VDataTableServer v-if="sparepartList.length" v-model:model-value="sparepartList"
                  :headers="sparepartHeaders" :items="sparepartList" class="text-no-wrap rounded-0">
                  <!-- Nama Sparepart -->
                  <!-- Nama Sparepart -->
                  <template #item.nama_sparepart="{ item }">
                    <span>{{ item.nama_sparepart }}</span>
                  </template>

                  <!-- Jumlah -->
                  <template #item.qty="{ item }">
                    <span>{{ item.qty }}</span>
                  </template>

                  <!-- Spec -->
                  <template #item.spec="{ item }">
                    <span>{{ item.spec || '-' }}</span>
                  </template>

                  <!-- Loc -->
                  <template #item.loc="{ item }">
                    <span>{{ item.loc || '-' }}</span>
                  </template>

                  <!-- Type -->
                  <template #item.type="{ item }">
                    <span>{{ item.type || '-' }}</span>
                  </template>

                  <!-- Aksi Hapus -->
                  <template #item.actions="{ index }">
                    <VBtn icon color="red" @click="removeSparepart(index)">
                      <VIcon icon="ri-delete-bin-7-line" />
                    </VBtn>


                  </template>
                </VDataTableServer>
              </template>


              <!-- Preventive PM -->
              <VCheckbox label="Preventive PM" value="preventive_pm" v-model="selectedMaintenanceTypesPreventivePM" />
              <template v-if="selectedMaintenanceTypesPreventivePM.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <DropZone label="BEFORE" v-model="preventivePmBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <DropZone label="AFTER" v-model="preventivePmAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <VFileInput v-model="preventiveJsa" label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size />
                </VCardText>
              </template>
            </div>
          </VCardItem>
        </VCard>
      </VCol>

      <VCol md="4" cols="12"> </VCol>
    </VRow>

    <!-- Snackbar -->
    <VSnackbar v-model="isSnackbarTopEndVisible" :timeout="3000" location="top end" :color="snackbarColor">
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
