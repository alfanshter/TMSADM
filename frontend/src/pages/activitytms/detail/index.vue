<script setup>
import PreviewDropZone from "@/@core/components/PreviewDropZone.vue";
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

const selectedMaintenanceTypesCleaningCritical = ref([]);
const selectedMaintenanceTypesJustCleaning = ref([]);
const selectedMaintenanceTypesReplacementPart = ref([]);
const selectedMaintenanceTypesPreventivePM = ref([]);
const birthDate = ref("");

//sparepart
const spareparts = ref([]);
// Headers untuk datatable
const sparepartHeaders = [
  { title: "Nama Sparepart", key: "nama_sparepart" },
  { title: "Jumlah", key: "qty" },
  { title: "Spec", key: "spec" },
  { title: "Loc", key: "loc" },
  { title: "Type", key: "type" },
];


// Dapatkan object spa

// file foto
const cleaningCriticalBeforeFiles = ref([]);
const cleaningCriticalAfterFiles = ref([]);
const justCleaningBeforeFiles = ref([]);
const justCleaningAfterFiles = ref([]);
const replacementPartBeforeFiles = ref([]);
const replacementPartAfterFiles = ref([]);
const preventivePmBeforeFiles = ref([]);
const preventivePmAfterFiles = ref([]);
//cleaning critical JSA
const cleaningCriticalJsa = ref(null);
const cleaningCriticalJsa_old = ref(null);
const cleaningCriticalJsa_filename = ref(null);
//Just Cleaning
const justCleaningJsa = ref(null);
const justCleaningJsa_old = ref(null);
const justCleaningJsa_filename = ref(null);
//replacement
const replacementJsa = ref(null);
const replacementJsa_old = ref(null);
const replacementJsa_filename = ref(null);

//preventive
const preventiveJsa = ref(null);
const preventiveJsa_old = ref(null);
const preventiveJsa_filename = ref(null);

//safety
const safety_scan = ref(null);
const safety_old = ref(null);
const safety_filename = ref(null);

//production scan
const production_scan = ref(null);
const production_scan_filename = ref(null); // dari backend
const production_scan_old = ref(null); // dari backend


// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");
const snackbarColor = ref("success"); // default


const itemMachines = ref([]);
const totalItemMachines = ref(0);
const selectedItemMachine = ref(null);

//scope of work
const incomingRs = ref("");
const incomingRt = ref("");
const incomingSt = ref("");
const outgoingRs = ref("");
const outgoingRt = ref("");
const outgoingSt = ref("");
const temp = ref("");
const deviation = ref("");

//sparepart
const itemSparepart = ref([]);
const selectedItemSparepart = ref([]);
const requiredQty = ref(1);
const sparepartList = ref([]);

const activity_id = ref("");


if (currentItem.value != null) {
  console.log("date:", currentItem.value);
  location.value = currentItem.value.item_machine.location;
  code.value = currentItem.value.item_machine.code;
  scopeOfWork.value = currentItem.value.item_machine.scope_of_work;
  birthDate.value = currentItem.value.date;
}


const fetchActivityDetail = async () => {
  if (!activityId) return;
  try {
    const res = await axios.get(`${ENDPOINTS.activityTmsDetail}/${activityId}`);
    const data = res.data.data ?? res.data;

    console.log("dinda", data);


    selectedItemMachine.value = data.item_machine.name;
    code.value = data.item_machine.code ?? "";
    location.value = data.item_machine.location ?? "";
    scopeOfWork.value = data.item_machine.scope_of_work ?? "";
    birthDate.value = data.date ? data.date : "";
    incomingRs.value = data.incoming_rs ?? "";
    incomingRt.value = data.incoming_rt ?? "";
    incomingSt.value = data.incoming_st ?? "";
    outgoingRs.value = data.outgoing_rs ?? "";
    outgoingRt.value = data.outgoing_rt ?? "";
    outgoingSt.value = data.outgoing_st ?? "";
    temp.value = data.temp ?? "";
    deviation.value = data.deviation ?? "";

    //Production file Scan
    production_scan_filename.value = data.production_scan_filename ?? "";
    production_scan_old.value = data.production_scan ?? "";

    //safety file scan
    safety_filename.value = data.safety_scan_filename ?? "";
    safety_old.value = data.safety_scan ?? "";

    //cleaning critical 
    cleaningCriticalJsa_filename.value = data.jsa_filename_cleaning_criticals ?? "";
    cleaningCriticalJsa_old.value = data.jsa_file_cleaning_criticals ?? "";
    //just cleaning
    justCleaningJsa_filename.value = data.jsa_filename_just_cleaning ?? "";
    justCleaningJsa_old.value = data.jsa_file_just_cleaning ?? "";
    //replacement
    replacementJsa_filename.value = data.jsa_filename_replacement_part ?? "";
    replacementJsa_old.value = data.jsa_file_replacement_part ?? "";
    //preventive
    preventiveJsa_filename.value = data.jsa_filename_preventive ?? "";
    preventiveJsa_old.value = data.jsa_file_preventive ?? "";


    if (data.cleaning_criticals && data.cleaning_criticals.length > 0) {
      cleaningCriticalBeforeFiles.value = data.cleaning_criticals.filter(
        (item) => item.status === "before"
      );

      cleaningCriticalAfterFiles.value = data.cleaning_criticals.filter(
        (item) => item.status === "after"
      );
      selectedMaintenanceTypesCleaningCritical.value = ["cleaning_critical"];
    }
    if (data.just_cleaning && data.just_cleaning.length > 0) {

      justCleaningBeforeFiles.value = data.just_cleaning.filter(
        (item) => item.status === "before"
      );

      justCleaningAfterFiles.value = data.just_cleaning.filter(
        (item) => item.status === "after"
      );
      selectedMaintenanceTypesJustCleaning.value = ["just_cleaning"];
    }

    if (data.replacement_part && data.replacement_part.length > 0 || data.spareparts && data.spareparts.length > 0) {
      replacementPartBeforeFiles.value = data.replacement_part.filter(
        (item) => item.status === "before"
      );

      replacementPartAfterFiles.value = data.replacement_part.filter(
        (item) => item.status === "after"
      );

      spareparts.value = data.spareparts;
      selectedMaintenanceTypesReplacementPart.value = ["replacement_part"];
    }
    //=========Preventife BEFORE==========
    if (data.preventive && data.preventive.length > 0) {
      preventivePmBeforeFiles.value = data.preventive.filter(
        (item) => item.status === "before"
      );

      preventivePmAfterFiles.value = data.preventive.filter(
        (item) => item.status === "after"
      );

      selectedMaintenanceTypesPreventivePM.value = ["preventive_pm"];
    }

    activity_id.value = data.id;
  } catch (error) {
    console.error("Error fetching activity detail:", error);
  }
};


// Dapatkan object sparepart yang dipilih
const selectedItemSparepartObj = computed(() =>
  itemSparepart.value.find((item) => item.id === selectedItemSparepart.value)
);

// Event saat pilih sparepart
const onSparepartSelect = () => {
  requiredQty.value = 1; // reset qty saat ganti sparepart
};

const getFileUrl = (path) => {
  return `http://127.0.0.1:8000/storage/${path}`; // sesuaikan URL file
};

const getFileName = (path) => {
  return path.split("/").pop();
};


function goBack() {
  window.history.back()
}

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
onMounted(() => {

  fetchItemSparepart();
  fetchActivityDetail();
});
</script>

<template>
  <div>
    <div class="d-flex flex-wrap justify-space-between gap-4 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 mb-1">Activity TMS</h4>
      </div>


    </div>

    <VRow>
      <VCol md="12">
        <!-- item machine -->
        <!-- Item Machine & Scope -->
        <VRow align="stretch">
          <!-- Card Kiri: Info Mesin -->
          <VCol cols="12" md="6">
            <VCard class="h-100">
              <VCardText>
                <div class="mb-2"><strong>Item Machine:</strong> {{ selectedItemMachine }}</div>
                <div class="mb-2"><strong>Code:</strong> {{ code }}</div>
                <div class="mb-2"><strong>Location:</strong> {{ location }}</div>
                <div class="mb-2"><strong>Scope of Work:</strong> {{ scopeOfWork }}</div>
                <div class="mb-2"><strong>Date:</strong> {{ birthDate }}</div>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Card Kanan: Safety -->
          <VCol cols="12" md="6" v-if="scopeOfWork == 'safety'">
            <VCard class="h-100">
              <VCardText class="d-flex flex-column justify-space-between h-100">
                <div>
                  <div class="mb-3">
                    <VLabel class="mb-1">JSA File</VLabel>
                    <div v-if="safety_filename">
                      <a :href="getFileUrl(safety_old)" target="_blank">
                        {{ getFileName(safety_filename) }}
                      </a>
                    </div>
                  </div>

                  <div class="mb-3">
                    <VLabel class="mb-1">Incoming</VLabel>
                    <div>R-S: {{ incomingRs }} | R-T: {{ incomingRt }} | S-T: {{ incomingSt }}</div>
                  </div>

                  <div class="mb-3">
                    <VLabel class="mb-1">Outgoing</VLabel>
                    <div>R-S: {{ outgoingRs }} | R-T: {{ outgoingRt }} | S-T: {{ outgoingSt }}</div>
                  </div>
                </div>

                <!-- Condition taruh bawah -->
                <div>
                  <VLabel class="mb-1">Condition</VLabel>
                  <div>Temp: {{ temp }} °C | Deviation: {{ deviation }}</div>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>




        <!-- 👉 Product Image -->
        <VCard class="mb-6 mt-4">
          <VCardItem>
            <template #title> Maintenance Types</template>

            <div class="d-flex flex-column mt-2">
              <!-- Cleaning Critical -->
              <template v-if="selectedMaintenanceTypesCleaningCritical.length">
                <h3 class="text-h6 mt-4">Cleaning Critical</h3>

                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <PreviewDropZone label="BEFORE" v-model="cleaningCriticalBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <PreviewDropZone label="AFTER" v-model="cleaningCriticalAfterFiles" />
                  </div>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File (Cleaning Critical)</VLabel>
                  <div v-if="cleaningCriticalJsa_filename">
                    <a :href="getFileUrl(cleaningCriticalJsa_old)" target="_blank">
                      {{ getFileName(cleaningCriticalJsa_filename) }}
                    </a>
                  </div>
                </VCardText>
              </template>

              <!-- Just Cleaning -->
              <template v-if="selectedMaintenanceTypesJustCleaning.length">
                <h3 class="text-h6 mt-4">Just Cleaning</h3>

                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <PreviewDropZone label="BEFORE" v-model="justCleaningBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <PreviewDropZone label="AFTER" v-model="justCleaningAfterFiles" />
                  </div>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File</VLabel>
                  <div v-if="justCleaningJsa_filename">
                    <a :href="getFileUrl(justCleaningJsa_old)" target="_blank">
                      {{ getFileName(justCleaningJsa_filename) }}
                    </a>
                  </div>
                </VCardText>
              </template>

              <!-- Replacement Part -->
              <template v-if="selectedMaintenanceTypesReplacementPart.length">
                <h3 class="text-h6 mt-4">Replacement Part</h3>

                <!-- Foto BEFORE & AFTER -->
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <PreviewDropZone label="BEFORE" v-model="replacementPartBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <PreviewDropZone label="AFTER" v-model="replacementPartAfterFiles" />
                  </div>
                </VCardText>

                <!-- JSA -->
                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File</VLabel>
                  <div v-if="replacementJsa_filename">
                    <a :href="getFileUrl(replacementJsa_old)" target="_blank">
                      {{ getFileName(replacementJsa_filename) }}
                    </a>
                  </div>
                </VCardText>

                <!-- Datatable Sparepart -->
                <VDataTableServer v-if="spareparts.length" v-model:model-value="spareparts" :headers="sparepartHeaders"
                  :items="spareparts" class="text-no-wrap rounded-0">
                  <template #item.nama_sparepart="{ item }">
                    <span>{{ item.nama_sparepart }}</span>
                  </template>

                  <template #item.qty="{ item }">
                    <span>{{ item.pivot.qty }}</span>
                  </template>

                  <template #item.spec="{ item }">
                    <span>{{ item.spec || '-' }}</span>
                  </template>

                  <template #item.loc="{ item }">
                    <span>{{ item.loc || '-' }}</span>
                  </template>

                  <template #item.type="{ item }">
                    <span>{{ item.type || '-' }}</span>
                  </template>
                </VDataTableServer>
              </template>

              <!-- Preventive PM -->
              <template v-if="selectedMaintenanceTypesPreventivePM.length">
                <h3 class="text-h6 mt-4">Preventive PM</h3>

                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <PreviewDropZone label="BEFORE" v-model="preventivePmBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <PreviewDropZone label="AFTER" v-model="preventivePmAfterFiles" />
                  </div>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File</VLabel>
                  <div v-if="preventiveJsa_filename">
                    <a :href="getFileUrl(preventiveJsa_old)" target="_blank">
                      {{ getFileName(preventiveJsa_filename) }}
                    </a>
                  </div>
                </VCardText>
              </template>
            </div>
          </VCardItem>
        </VCard>

      </VCol>

      <VCol md="4" cols="12"> </VCol>
    </VRow>

    <!-- Snackbar -->
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
