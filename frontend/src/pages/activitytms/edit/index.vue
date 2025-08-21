<script setup>
import UpdateDropZone from "@/@core/components/UpdateDropZone.vue";
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
const isEditMode = ref(!!activityId);
console.log("Edit Mode:", isEditMode.value);
console.log("Activity ID:", activityId);

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
  { title: "Aksi", key: "actions" },
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
    const res = await axios.get(`${ENDPOINTS.activityTmsDetail}/${activityId}`);
    const data = res.data.data ?? res.data;

    console.log("dinda", data);

    // pastikan sudah ada itemMachines sebelum set value
    if (!itemMachines.value.length) {
      await fetchItemMachines();
    }

    selectedItemMachine.value = Number(data.item_machine_id);
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

    activity_id = data.id;
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

// Tambah ke list
// Tambah ke list + simpan ke server
// Tambah sparepart
const addSparepart = async (activity_id) => {
  if (!selectedItemSparepartObj.value) return;

  // Validasi qty
  if (requiredQty.value < 1 || requiredQty.value > selectedItemSparepartObj.value.usages_sum_qty) {
    alert("Jumlah tidak valid!");
    return;
  }

  try {
    globalLoading?.show();

    // Data yang dikirim ke server
    const payload = {
      activity_tms_id: activity_id,
      stock_sparepart_id: selectedItemSparepartObj.value.id,
      qty: requiredQty.value,
    };

    // Simpan ke server
    const res = await axios.post(ENDPOINTS.addTmsSparepart, payload);

    // Update array lokal (konsisten sama removeSparepart → pakai spareparts)
    const existIndex = spareparts.value.findIndex(
      (s) => s.id === selectedItemSparepartObj.value.id
    );

    if (existIndex !== -1) {
      spareparts.value[existIndex].qty += requiredQty.value;
    } else {
      spareparts.value.push({
        id: selectedItemSparepartObj.value.id,
        nama_sparepart: selectedItemSparepartObj.value.nama_sparepart,
        pivot: {
          qty: requiredQty.value,   // 👈 taruh di pivot
        },
        loc: selectedItemSparepartObj.value.loc,
        spec: selectedItemSparepartObj.value.spec,
        type: selectedItemSparepartObj.value.type,
      });

    }

    snackbarMessage.value = "Sparepart berhasil ditambahkan!";
    snackbarColor.value = "success";
    isSnackbarTopEndVisible.value = true;

    // Reset input
    selectedItemSparepart.value = null;
    requiredQty.value = 1;

  } catch (error) {
    console.error("Error adding data:", error);
    snackbarMessage.value = "Gagal menambahkan sparepart!";
    snackbarColor.value = "error";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};



// hapus sparepart
const removeSparepart = async (index, idbaris) => {

  try {
    globalLoading?.show();

    // Hapus sparepart di backend
    await axios.delete(`${ENDPOINTS.deleteTmsSparepart(index)}`);
    // Hapus dari array lokal jika sukses
    spareparts.value.splice(idbaris, 1);

    snackbarMessage.value = "Sparepart berhasil dihapus!";
    snackbarColor.value = "success";
    isSnackbarTopEndVisible.value = true;

  } catch (error) {
    console.error("Gagal menghapus sparepart:", error);
    snackbarMessage.value = "Gagal menghapus sparepart!";
    snackbarColor.value = "error";
    isSnackbarTopEndVisible.value = true;

  } finally {
    globalLoading?.hide();
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

  // =========PREVENTIVE================
  // ==========BEFORE=========
  // Foto lama → kirim ID saja
  preventivePmBeforeFiles.value
    .filter(f => !f.isNew)
    .forEach((f, i) => {
      formData.append(`preventive_foto_before_old[${i}]`, f.id);
    });

  // Foto lama → kirim File
  preventivePmBeforeFiles.value
    .filter(f => f.isNew)
    .forEach((f, i) => {
      formData.append(`preventive_foto_before_new[${i}]`, f.file);
    });
  // ==========AFTER=========
  preventivePmAfterFiles.value.filter(f => !f.isNew).forEach((f, i) => {
    formData.append(`preventive_foto_after_old[${i}]`, f.id);
  });

  preventivePmAfterFiles.value.filter(f => f.isNew).forEach((f, i) => {
    formData.append(`preventive_foto_after_new[${i}]`, f.file);
  });

  // =========REPLACEMENT PART================
  // ==========BEFORE=========
  // Foto lama → kirim ID saja  
  replacementPartBeforeFiles.value
    .filter(f => !f.isNew)
    .forEach((f, i) => {
      formData.append(`replacement_part_foto_before_old[${i}]`, f.id);
    });

  // Foto lama → kirim File
  replacementPartBeforeFiles.value
    .filter(f => f.isNew)
    .forEach((f, i) => {
      formData.append(`replacement_part_foto_before_new[${i}]`, f.file);
    });
  // ==========AFTER=========
  replacementPartAfterFiles.value.filter(f => !f.isNew).forEach((f, i) => {
    formData.append(`replacement_part_foto_after_old[${i}]`, f.id);
  });

  replacementPartAfterFiles.value.filter(f => f.isNew).forEach((f, i) => {
    formData.append(`replacement_part_foto_after_new[${i}]`, f.file);
  });

  // =========JUST CLEANING================
  // ==========BEFORE=========
  // Foto lama → kirim ID saja  
  justCleaningBeforeFiles.value
    .filter(f => !f.isNew)
    .forEach((f, i) => {
      formData.append(`just_cleaning_foto_before_old[${i}]`, f.id);
    });

  // Foto lama → kirim File
  justCleaningBeforeFiles.value
    .filter(f => f.isNew)
    .forEach((f, i) => {
      formData.append(`just_cleaning_foto_before_new[${i}]`, f.file);
    });
  // ==========AFTER=========
  justCleaningAfterFiles.value.filter(f => !f.isNew).forEach((f, i) => {
    formData.append(`just_cleaning_foto_after_old[${i}]`, f.id);
  });

  justCleaningAfterFiles.value.filter(f => f.isNew).forEach((f, i) => {
    formData.append(`just_cleaning_foto_after_new[${i}]`, f.file);
  });

  // =========CLEANING CRITICAL================
  // ==========BEFORE=========
  // Foto lama → kirim ID saja  
  cleaningCriticalBeforeFiles.value
    .filter(f => !f.isNew)
    .forEach((f, i) => {
      formData.append(`cleaning_cricital_foto_before_old[${i}]`, f.id);
    });

  // Foto lama → kirim File
  cleaningCriticalBeforeFiles.value
    .filter(f => f.isNew)
    .forEach((f, i) => {
      formData.append(`cleaning_cricital_foto_before_new[${i}]`, f.file);
    });
  // ==========AFTER=========
  cleaningCriticalAfterFiles.value.filter(f => !f.isNew).forEach((f, i) => {
    formData.append(`cleaning_cricital_foto_after_old[${i}]`, f.id);
  });

  cleaningCriticalAfterFiles.value.filter(f => f.isNew).forEach((f, i) => {
    formData.append(`cleaning_cricital_foto_after_new[${i}]`, f.file);
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
  for (let [key, value] of formData.entries()) {
    console.log(key, value);
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
  fetchItemMachines();
  fetchItemSparepart();
  fetchActivityDetail();
});
</script>

<template>
  <div>
    <div class="d-flex flex-wrap justify-space-between gap-4 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 mb-1">Edit Activity TMS</h4>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn variant="outlined" color="secondary" @click="goBack()"> Discard </VBtn>
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

        <!-- safety -->
        <VCard class="mb-6" v-if="scopeOfWork == 'safety'">
          <VCardItem>
            <template #title> Scope of Work </template>
            <div class="d-flex flex-column mt-2">
              <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
              <!-- Tampilkan file lama jika ada -->
              <div v-if="safety_filename" class="mb-2">
                <a :href="getFileUrl(safety_old)" target="_blank">
                  {{ getFileName(safety_filename) }}
                </a>
              </div>

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

        <!-- production -->
        <VCard class="mb-6" v-if="scopeOfWork == 'production'">
          <VCardItem>
            <template #title> Scope of Work </template>
            <div class="d-flex flex-column mt-2">
              <VLabel class="mt-2 mb-1">Upload JSA file (Production)</VLabel>

              <!-- Tampilkan file lama jika ada -->
              <div v-if="production_scan_filename" class="mb-2">
                <a :href="getFileUrl(production_scan_old)" target="_blank">
                  {{ getFileName(production_scan_filename) }}
                </a>
              </div>

              <!-- File input baru -->
              <VFileInput v-model="production_scan" label="Pilih file dokumen"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" prepend-icon="ri-upload-2-line"
                show-size />
            </div>
            <!-- Checkbox -->

          </VCardItem>
        </VCard>
        <!-- 👉 Product Image -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title> Maintenance Type </template>

            <!-- Checkbox -->
            <div class="d-flex flex-column mt-2">
              <!-- Cleaning Critical -->
              <VCheckbox label="Cleaning Critical" value="cleaning_critical"
                v-model="selectedMaintenanceTypesCleaningCritical" />
              <template v-if="selectedMaintenanceTypesCleaningCritical.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <UpdateDropZone label="BEFORE" v-model="cleaningCriticalBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <UpdateDropZone label="AFTER" v-model="cleaningCriticalAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file (Cleaning Critical)</VLabel>

                  <!-- Tampilkan file lama jika ada -->
                  <div v-if="cleaningCriticalJsa_filename" class="mb-2">
                    <a :href="getFileUrl(cleaningCriticalJsa_old)" target="_blank">
                      {{ getFileName(cleaningCriticalJsa_filename) }}
                    </a>
                  </div>

                  <!-- File input baru -->
                  <VFileInput v-model="cleaningCriticalJsa" label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" prepend-icon="ri-upload-2-line"
                    show-size />
                </VCardText>
              </template>

              <!-- Just Cleaning -->
              <VCheckbox label="Just Cleaning" value="just_cleaning" v-model="selectedMaintenanceTypesJustCleaning" />
              <template v-if="selectedMaintenanceTypesJustCleaning.length">
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <UpdateDropZone label="BEFORE" v-model="justCleaningBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <UpdateDropZone label="AFTER" v-model="justCleaningAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>

                  <!-- Tampilkan file lama jika ada -->
                  <div v-if="justCleaningJsa_filename" class="mb-2">
                    <a :href="getFileUrl(justCleaningJsa_old)" target="_blank">
                      {{ getFileName(justCleaningJsa_filename) }}
                    </a>
                  </div>


                  <VFileInput v-model="justCleaningJsa" label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size />
                </VCardText>
              </template>

              <!-- Replacement Part -->
              <VCheckbox label="Replacement Part" value="replacement_part"
                v-model="selectedMaintenanceTypesReplacementPart" />

              <template v-if="selectedMaintenanceTypesReplacementPart.length">
                <VRow dense>
                  <!-- Pilih Sparepart -->
                  <VCol cols="12" md="6">
                    <VAutocomplete v-model="selectedItemSparepart" :items="itemSparepart" item-title="nama_sparepart"
                      item-value="id" label="Sparepart" placeholder="Cari / pilih sparepart" clearable
                      density="comfortable" @change="onSparepartSelect" />
                  </VCol>

                  <!-- Jumlah yang dibutuhkan -->
                  <VCol cols="12" md="6" v-if="selectedItemSparepartObj">
                    <VTextField v-model.number="requiredQty" type="number"
                      :label="`Butuh berapa? (Stok tersedia: ${selectedItemSparepartObj.usages_sum_qty})`"
                      :max="selectedItemSparepartObj.usages_sum_qty" min="1" />
                    <VBtn color="primary" class="mt-2" @click="addSparepart(activityId)">Add</VBtn>
                  </VCol>
                </VRow>

                <!-- Upload Foto BEFORE & AFTER -->
                <VCardText class="d-flex gap-4">
                  <div style="flex: 1">
                    <UpdateDropZone label="BEFORE" v-model="replacementPartBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <UpdateDropZone label="AFTER" v-model="replacementPartAfterFiles" />
                  </div>
                </VCardText>

                <!-- Upload JSA -->
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>

                  <!-- Tampilkan file lama jika ada -->
                  <div v-if="replacementJsa_filename" class="mb-2">
                    <a :href="getFileUrl(replacementJsa_old)" target="_blank">
                      {{ getFileName(replacementJsa_filename) }}
                    </a>
                  </div>

                  <VFileInput v-model="replacementJsa" label="Pilih file dokumen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" prepend-icon="ri-upload-2-line" show-size />
                </VCardText>

                <!-- Datatable Sparepart -->
                <VDataTableServer v-if="spareparts.length" v-model:model-value="spareparts" :headers="sparepartHeaders"
                  :items="spareparts" class="text-no-wrap rounded-0">
                  <!-- Nama Sparepart -->
                  <template #item.nama_sparepart="{ item }">
                    <span>{{ item.nama_sparepart }}</span>
                  </template>

                  <!-- Jumlah -->
                  <template #item.qty="{ item }">
                    <span>{{ item.pivot.qty }}</span>
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
                  <template #item.actions="{ item, index }">
                    <VBtn icon color="red" @click="removeSparepart(item.pivot.id, index)">
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
                    <UpdateDropZone label="BEFORE" v-model="preventivePmBeforeFiles" />
                  </div>
                  <div style="flex: 1">
                    <UpdateDropZone label="AFTER" v-model="preventivePmAfterFiles" />
                  </div>
                </VCardText>
                <VCardText>
                  <VLabel class="mt-2 mb-1">Upload JSA file</VLabel>
                  <div v-if="preventiveJsa_filename" class="mb-2">
                    <a :href="getFileUrl(preventiveJsa_old)" target="_blank">
                      {{ getFileName(preventiveJsa_filename) }}
                    </a>
                  </div>

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
