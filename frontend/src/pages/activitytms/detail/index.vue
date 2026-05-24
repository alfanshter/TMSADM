<script setup>
import PreviewDropZone from "@/@core/components/PreviewDropZone.vue";
import { ENDPOINTS } from "@/config/api";
import { useActivityStore } from "@/stores/useActivityStore";
import axios from "axios";
import { computed, inject, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import Cookies from "js-cookie";

import { VCardItem, VRow } from "vuetify/components";

//get pinia
const activityStore = useActivityStore();
const currentItem = computed(() => activityStore.currentItem);

// Inject global loading
const globalLoading = inject("globalLoading");

const userData = Cookies.get("userData")
  ? JSON.parse(Cookies.get("userData"))
  : null;
const role = userData?.user?.role;

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
const sparepartHeaders = [
  { title: "Nama Sparepart", key: "nama_sparepart" },
  { title: "Jumlah", key: "qty" },
  { title: "Spec", key: "spec" },
  { title: "Loc", key: "loc" },
  { title: "Type", key: "type" },
];

// catatan — semua role bisa edit semua
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

// file foto
const cleaningCriticalBeforeFiles = ref([]);
const cleaningCriticalAfterFiles = ref([]);
const justCleaningBeforeFiles = ref([]);
const justCleaningAfterFiles = ref([]);
const replacementPartBeforeFiles = ref([]);
const replacementPartAfterFiles = ref([]);
const preventivePmBeforeFiles = ref([]);
const preventivePmAfterFiles = ref([]);

//JSA files
const cleaningCriticalJsa = ref(null);
const cleaningCriticalJsa_old = ref(null);
const cleaningCriticalJsa_filename = ref(null);
const justCleaningJsa = ref(null);
const justCleaningJsa_old = ref(null);
const justCleaningJsa_filename = ref(null);
const replacementJsa = ref(null);
const replacementJsa_old = ref(null);
const replacementJsa_filename = ref(null);
const preventiveJsa = ref(null);
const preventiveJsa_old = ref(null);
const preventiveJsa_filename = ref(null);

//safety
const safety_scan = ref(null);
const safety_old = ref(null);
const safety_filename = ref(null);

//production scan
const production_scan = ref(null);
const production_scan_filename = ref(null);
const production_scan_old = ref(null);

// Snackbar
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");
const snackbarColor = ref("success");

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
const production_downtime = ref(0);
const start_downtime = ref(null);
const end_downtime = ref(null);

//sparepart
const itemSparepart = ref([]);
const selectedItemSparepart = ref([]);
const requiredQty = ref(1);
const sparepartList = ref([]);

const activity_id = ref("");

if (currentItem.value != null) {
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
    production_downtime.value = data.production_downtime ?? 0;
    start_downtime.value = data.start_downtime ?? null;
    end_downtime.value = data.end_downtime ?? null;

    production_scan_filename.value = data.production_scan_filename ?? "";
    production_scan.value = data.production_scan ?? "";
    safety_filename.value = data.safety_scan_filename ?? "";
    safety_old.value = data.safety_scan ?? "";

    cleaningCriticalJsa_filename.value = data.jsa_filename_cleaning_criticals ?? "";
    cleaningCriticalJsa_old.value = data.jsa_file_cleaning_criticals ?? "";
    justCleaningJsa_filename.value = data.jsa_filename_just_cleaning ?? "";
    justCleaningJsa_old.value = data.jsa_file_just_cleaning ?? "";
    replacementJsa_filename.value = data.jsa_filename_replacement_part ?? "";
    replacementJsa_old.value = data.jsa_file_replacement_part ?? "";
    preventiveJsa_filename.value = data.jsa_filename_preventive ?? "";
    preventiveJsa_old.value = data.jsa_file_preventive ?? "";

    if (data.cleaning_criticals && data.cleaning_criticals.length > 0) {
      cleaningCriticalBeforeFiles.value = data.cleaning_criticals.filter(i => i.status === "before");
      cleaningCriticalAfterFiles.value = data.cleaning_criticals.filter(i => i.status === "after");
      selectedMaintenanceTypesCleaningCritical.value = ["cleaning_critical"];
    }
    if (data.just_cleaning && data.just_cleaning.length > 0) {
      justCleaningBeforeFiles.value = data.just_cleaning.filter(i => i.status === "before");
      justCleaningAfterFiles.value = data.just_cleaning.filter(i => i.status === "after");
      selectedMaintenanceTypesJustCleaning.value = ["just_cleaning"];
    }
    if ((data.replacement_part && data.replacement_part.length > 0) || (data.spareparts && data.spareparts.length > 0)) {
      replacementPartBeforeFiles.value = data.replacement_part.filter(i => i.status === "before");
      replacementPartAfterFiles.value = data.replacement_part.filter(i => i.status === "after");
      spareparts.value = data.spareparts;
      selectedMaintenanceTypesReplacementPart.value = ["replacement_part"];
    }
    if (data.preventive && data.preventive.length > 0) {
      preventivePmBeforeFiles.value = data.preventive.filter(i => i.status === "before");
      preventivePmAfterFiles.value = data.preventive.filter(i => i.status === "after");
      selectedMaintenanceTypesPreventivePM.value = ["preventive_pm"];
    }

    // Catatan dari backend
    catatanTeamleaderCleaningCritical.value = data.catatan_teamleader_cleaning_criticals ?? "";
    catatanSupervisorCleaningCritical.value  = data.catatan_supervisor_cleaning_criticals ?? "";
    catatanTeknisiCleaningCritical.value     = data.catatan_teknisi_cleaning_criticals ?? "";
    catatanTeamleaderJustCleaning.value      = data.catatan_teamleader_just_cleaning ?? "";
    catatanSupervisorJustCleaning.value      = data.catatan_supervisor_justcleaning ?? "";
    catatanTeknisiJustCleaning.value         = data.catatan_teknisi_just_cleaning ?? "";
    catatanTeamleaderReplacementPart.value   = data.catatan_teamleader_replacement_part ?? "";
    catatanSupervisorReplacementPart.value   = data.catatan_supervisor_replacement_part ?? "";
    catatanTeknisiReplacementPart.value      = data.catatan_teknisi_replacement_part ?? "";
    catatanTeamleaderPreventivePm.value      = data.catatan_teamleader_preventive_pm ?? "";
    catatanSupervisorPreventivePm.value      = data.catatan_supervisor_preventive_pm ?? "";
    catatanTeknisiPreventivePm.value         = data.catatan_teknisi_preventive_pm ?? "";

    activity_id.value = data.id;
  } catch (error) {
    console.error("Error fetching activity detail:", error);
  }
};

const selectedItemSparepartObj = computed(() =>
  itemSparepart.value.find((item) => item.id === selectedItemSparepart.value)
);

const onSparepartSelect = () => {
  requiredQty.value = 1;
};

const getFileUrl = (path) => `${import.meta.env.VITE_FILE_BASE_URL}/${path}`;
const getFileName = (path) => path.split("/").pop();

function goBack() {
  window.history.back();
}

const fetchItemSparepart = async () => {
  try {
    const res = await axios.get(ENDPOINTS.spareparts);
    const result = res.data.data ?? res.data;
    itemSparepart.value = result;
  } catch (error) {
    console.error("Error fetching item sparepart", error);
  }
};

// Simpan catatan — semua role bisa simpan semua catatan
const saveSupervisorNote = async (tipe) => {
  try {
    globalLoading?.show();

    const payload = {};

    switch (tipe) {
      case "cleaning_critical":
        payload.catatan_teamleader_cleaning_criticals = catatanTeamleaderCleaningCritical.value;
        payload.catatan_supervisor_cleaning_criticals = catatanSupervisorCleaningCritical.value;
        payload.catatan_teknisi_cleaning_criticals    = catatanTeknisiCleaningCritical.value;
        break;
      case "just_cleaning":
        payload.catatan_teamleader_just_cleaning = catatanTeamleaderJustCleaning.value;
        payload.catatan_supervisor_justcleaning  = catatanSupervisorJustCleaning.value;
        payload.catatan_teknisi_just_cleaning    = catatanTeknisiJustCleaning.value;
        break;
      case "replacement_part":
        payload.catatan_teamleader_replacement_part = catatanTeamleaderReplacementPart.value;
        payload.catatan_supervisor_replacement_part = catatanSupervisorReplacementPart.value;
        payload.catatan_teknisi_replacement_part    = catatanTeknisiReplacementPart.value;
        break;
      case "preventive_pm":
        payload.catatan_teamleader_preventive_pm = catatanTeamleaderPreventivePm.value;
        payload.catatan_supervisor_preventive_pm = catatanSupervisorPreventivePm.value;
        payload.catatan_teknisi_preventive_pm    = catatanTeknisiPreventivePm.value;
        break;
    }

    await axios.put(ENDPOINTS.updateSupervisorNote(activity_id.value), payload);

    snackbarMessage.value = "Catatan berhasil disimpan!";
    snackbarColor.value = "success";
    isSnackbarTopEndVisible.value = true;
    fetchActivityDetail();
  } catch (error) {
    console.error("Error simpan catatan:", error);
    snackbarMessage.value = "Gagal simpan catatan!";
    snackbarColor.value = "error";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

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
                <div class="mb-2">
                  <strong>Job Safety Analysis:</strong>
                  <div v-if="production_scan_filename">
                    <a :href="getFileUrl(production_scan)" target="_blank">
                      {{ getFileName(production_scan_filename) }}
                    </a>
                  </div>
                </div>
                <div class="mb-2"><strong>Date:</strong> {{ birthDate }}</div>
                <div class="mb-2"><strong>Start Time Downtime:</strong> {{ start_downtime }}</div>
                <div class="mb-2"><strong>End Time Downtime:</strong> {{ end_downtime }}</div>
                <div class="mb-2"><strong>Downtime:</strong> {{ production_downtime }} minutes</div>
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
                <div>
                  <VLabel class="mb-1">Condition</VLabel>
                  <div>Temp: {{ temp }} °C | Deviation: {{ deviation }}</div>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

        <!-- Maintenance Types -->
        <VCard class="mb-6 mt-4">
          <VCardItem>
            <template #title>Maintenance Types</template>

            <div class="d-flex flex-column mt-2">

              <!-- Cleaning Critical -->
              <template v-if="selectedMaintenanceTypesCleaningCritical.length">
                <h3 class="text-h6 mt-4">Cleaning Critical</h3>

                <VCardText>
                  <VRow>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="BEFORE" v-model="cleaningCriticalBeforeFiles" />
                    </VCol>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="AFTER" v-model="cleaningCriticalAfterFiles" />
                    </VCol>
                  </VRow>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File (Cleaning Critical)</VLabel>
                  <div v-if="cleaningCriticalJsa_filename">
                    <a :href="getFileUrl(cleaningCriticalJsa_old)" target="_blank">
                      {{ getFileName(cleaningCriticalJsa_filename) }}
                    </a>
                  </div>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1 font-weight-bold">Catatan Team Leader</VLabel>
                  <VTextarea v-model="catatanTeamleaderCleaningCritical" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Supervisor</VLabel>
                  <VTextarea v-model="catatanSupervisorCleaningCritical" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Teknisi</VLabel>
                  <VTextarea v-model="catatanTeknisiCleaningCritical" rows="3" auto-grow />

                  <VBtn color="primary" class="mt-3" @click="saveSupervisorNote('cleaning_critical')">
                    Simpan Catatan
                  </VBtn>
                </VCardText>
              </template>

              <!-- Just Cleaning -->
              <template v-if="selectedMaintenanceTypesJustCleaning.length">
                <h3 class="text-h6 mt-4">Just Cleaning</h3>

                <VCardText>
                  <VRow>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="BEFORE" v-model="justCleaningBeforeFiles" />
                    </VCol>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="AFTER" v-model="justCleaningAfterFiles" />
                    </VCol>
                  </VRow>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File</VLabel>
                  <div v-if="justCleaningJsa_filename">
                    <a :href="getFileUrl(justCleaningJsa_old)" target="_blank">
                      {{ getFileName(justCleaningJsa_filename) }}
                    </a>
                  </div>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1 font-weight-bold">Catatan Team Leader</VLabel>
                  <VTextarea v-model="catatanTeamleaderJustCleaning" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Supervisor</VLabel>
                  <VTextarea v-model="catatanSupervisorJustCleaning" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Teknisi</VLabel>
                  <VTextarea v-model="catatanTeknisiJustCleaning" rows="3" auto-grow />

                  <VBtn color="primary" class="mt-3" @click="saveSupervisorNote('just_cleaning')">
                    Simpan Catatan
                  </VBtn>
                </VCardText>
              </template>

              <!-- Replacement Part -->
              <template v-if="selectedMaintenanceTypesReplacementPart.length">
                <h3 class="text-h6 mt-4">Replacement Part</h3>

                <VCardText>
                  <VRow>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="BEFORE" v-model="replacementPartBeforeFiles" />
                    </VCol>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="AFTER" v-model="replacementPartAfterFiles" />
                    </VCol>
                  </VRow>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File</VLabel>
                  <div v-if="replacementJsa_filename">
                    <a :href="getFileUrl(replacementJsa_old)" target="_blank">
                      {{ getFileName(replacementJsa_filename) }}
                    </a>
                  </div>
                </VCardText>

                <VDataTableServer
                  v-if="spareparts.length"
                  v-model:model-value="spareparts"
                  :headers="sparepartHeaders"
                  :items="spareparts"
                  class="text-no-wrap rounded-0"
                >
                  <template #item.nama_sparepart="{ item }"><span>{{ item.nama_sparepart }}</span></template>
                  <template #item.qty="{ item }"><span>{{ item.pivot.qty }}</span></template>
                  <template #item.spec="{ item }"><span>{{ item.spec || "-" }}</span></template>
                  <template #item.loc="{ item }"><span>{{ item.loc || "-" }}</span></template>
                  <template #item.type="{ item }"><span>{{ item.type || "-" }}</span></template>
                </VDataTableServer>

                <VCardText>
                  <VLabel class="mt-2 mb-1 font-weight-bold">Catatan Team Leader</VLabel>
                  <VTextarea v-model="catatanTeamleaderReplacementPart" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Supervisor</VLabel>
                  <VTextarea v-model="catatanSupervisorReplacementPart" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Teknisi</VLabel>
                  <VTextarea v-model="catatanTeknisiReplacementPart" rows="3" auto-grow />

                  <VBtn color="primary" class="mt-3" @click="saveSupervisorNote('replacement_part')">
                    Simpan Catatan
                  </VBtn>
                </VCardText>
              </template>

              <!-- Preventive PM -->
              <template v-if="selectedMaintenanceTypesPreventivePM.length">
                <h3 class="text-h6 mt-4">Preventive PM</h3>

                <VCardText>
                  <VRow>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="BEFORE" v-model="preventivePmBeforeFiles" />
                    </VCol>
                    <VCol cols="12" md="6">
                      <PreviewDropZone label="AFTER" v-model="preventivePmAfterFiles" />
                    </VCol>
                  </VRow>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1">JSA File</VLabel>
                  <div v-if="preventiveJsa_filename">
                    <a :href="getFileUrl(preventiveJsa_old)" target="_blank">
                      {{ getFileName(preventiveJsa_filename) }}
                    </a>
                  </div>
                </VCardText>

                <VCardText>
                  <VLabel class="mt-2 mb-1 font-weight-bold">Catatan Team Leader</VLabel>
                  <VTextarea v-model="catatanTeamleaderPreventivePm" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Supervisor</VLabel>
                  <VTextarea v-model="catatanSupervisorPreventivePm" rows="3" auto-grow />

                  <VLabel class="mt-3 mb-1 font-weight-bold">Catatan Teknisi</VLabel>
                  <VTextarea v-model="catatanTeknisiPreventivePm" rows="3" auto-grow />

                  <VBtn color="primary" class="mt-3" @click="saveSupervisorNote('preventive_pm')">
                    Simpan Catatan
                  </VBtn>
                </VCardText>
              </template>

            </div>
          </VCardItem>
        </VCard>
      </VCol>
    </VRow>

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