<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { onMounted, ref } from "vue";

const props = defineProps({
  report: { type: Object, default: null }, // Kalau edit, pass report
});
const emit = defineEmits(["saved", "close"]);

const lokasi = ref("");
const date = ref("");
const files = ref([]);
const isEditMode = ref(false);
const reportId = ref(null);

onMounted(() => {
  if (props.report) {
    isEditMode.value = true;
    reportId.value = props.report.id;
    lokasi.value = props.report.lokasi;
    date.value = props.report.date;
    // Kalau mau tampilkan file lama, bisa tambahkan di files
  }
});

const handleFileUpload = (e) => {
  const uploaded = Array.from(e.target.files);
  files.value.push(...uploaded);
  e.target.value = "";
};

const removeFile = (index) => {
  files.value.splice(index, 1);
};

const submitReport = async () => {
  try {
    const formData = new FormData();
    formData.append("lokasi", lokasi.value);
    formData.append("date", date.value);

    // backend kamu hanya menerima satu file 'file_scan', jadi kirim satu-satu
    files.value.forEach((file) => {
      formData.append("file_scan", file);
    });

    console.log("Lokasi:", lokasi.value);
    console.log("Date:", date.value);
    console.log("Files:", files.value);

    let res;
    if (isEditMode.value && reportId.value) {
      res = await axios.post(
        ENDPOINTS.updateLeakageReport(reportId.value) + "?_method=PUT",
        formData,
        { headers: { "Content-Type": "multipart/form-data" } }
      );
    } else {
      res = await axios.post(ENDPOINTS.leakageReports, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    }

    emit("saved");

    console.log("Response:", res.data);

    // reset form
    lokasi.value = "";
    date.value = "";
    files.value = [];
    isEditMode.value = false;
    reportId.value = null;
  } catch (err) {
    console.error(err.response.data);
    alert("Terjadi kesalahan: " + JSON.stringify(err.response.data.message));
  }
};
</script>
<template>
  <VCol md="8" class="mx-auto">
    <VCard class="mb-6" title="Leakage Report">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VCard class="mb-6">
              <VCardItem>
                <template #title> Upload Files </template>
                <template #append>
                  <VBtn
                    variant="outlined"
                    color="primary"
                    @click="$refs.fileInput.click()"
                  >
                    Add Files
                  </VBtn>
                  <input
                    type="file"
                    ref="fileInput"
                    multiple
                    hidden
                    @change="handleFileUpload"
                  />
                </template>
              </VCardItem>

              <VCardText>
                <div v-if="files.length" class="d-flex flex-column gap-4">
                  <VCard
                    v-for="(file, index) in files"
                    :key="index"
                    class="pa-3"
                    outlined
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div>
                        <strong>{{ file.name }}</strong>
                        <div class="text-caption text-medium-emphasis">
                          {{ (file.size / 1024).toFixed(2) }} KB
                        </div>
                      </div>
                      <VBtn
                        color="error"
                        variant="text"
                        @click="removeFile(index)"
                      >
                        Delete
                      </VBtn>
                    </div>
                  </VCard>
                </div>

                <div v-else class="text-medium-emphasis">
                  Belum ada file yang diunggah.
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" md="6">
            <VTextField
              v-model="lokasi"
              label="Location"
              placeholder="Enter location"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="date"
              label="Date"
              placeholder="Select Date"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <div class="d-flex flex-wrap justify-end gap-4 mb-6">
      <div class="d-flex gap-4 align-center-end flex-wrap">
        <VBtn
          variant="outlined"
          color="secondary"
          @click="
            () => {
              lokasi = '';
              date = '';
              files = [];
            }
          "
          >Discard</VBtn
        >
        <VBtn variant="outlined" color="primary">Save Draft</VBtn>
        <VBtn color="primary" @click="submitReport">{{
          isEditMode ? "Update Report" : "Publish Report"
        }}</VBtn>
      </div>
    </div>
  </VCol>
</template>
