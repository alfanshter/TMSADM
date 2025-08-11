<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { ref } from "vue";

const content = ref(""); // Description
const result = ref(""); // Result
const birthDate = ref(null); // Date
const images = ref([]); // Dari DropZone

// Inject global loading
const globalLoading = inject("globalLoading");

const isLoading = ref(false);
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

const resetForm = () => {
  content.value = "";
  result.value = "";
  birthDate.value = "";
  images.value = [];
};

// Simpan FAW Report
const submitFawReport = async () => {
  try {
    globalLoading?.show();
    const formData = new FormData();
    formData.append("description", content.value);
    formData.append("result", result.value);
    formData.append("date", birthDate.value);

    // Kalau ada upload gambar
    images.value.forEach((file, index) => {
      formData.append("photos[]", file);
    });

    await axios.post(ENDPOINTS.fawreport, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    resetForm();

    snackbarMessage.value = "FAW Report berhasil dipublish!";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Error submit FAW Report:", error);
    alert("Gagal mengirim FAW Report");
  } finally {
    globalLoading?.hide();
  }
};
</script>

<template>
  <VCol md="8" class="mx-auto">
    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
      timeout="3000"
      color="success"
    >
      {{ snackbarMessage }}
    </VSnackbar>
    <!-- 👉 FAW Report -->
    <VCard class="mb-6" title="Faw Report">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VCol>
              <VLabel class="mb-1"> Description (Optional) </VLabel>
              <TiptapEditor v-model="content" class="border rounded-lg" />
            </VCol>
          </VCol>
          <VCol cols="12" md="6">
            <VTextField v-model="result" label="Result" placeholder="Done" />
          </VCol>
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="birthDate"
              label="Date"
              placeholder="Select Date"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- 👉 report Image -->
    <VCard class="mb-6">
      <VCardItem>
        <template #title> Report Image </template>
        <template #append>
          <h6 class="text-h6 text-primary cursor-pointer">
            Add Media from Computer
          </h6>
        </template>
      </VCardItem>

      <VCardText>
        <DropZone v-model="images" />
      </VCardText>
    </VCard>

    <!-- Tombol Aksi -->
    <div class="d-flex flex-wrap justify-end gap-4 mb-6">
      <div class="d-flex gap-4 align-center-end flex-wrap">
        <VBtn variant="outlined" color="secondary"> Discard </VBtn>
        <VBtn variant="outlined" color="primary"> Save Draft </VBtn>
        <VBtn color="primary" @click="submitFawReport">Publish Report</VBtn>
      </div>
    </div>
  </VCol>
</template>
