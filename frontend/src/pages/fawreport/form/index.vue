<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { inject, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

// Ambil id dari query ?id=... atau dari route param /:id
const id = route.query.id || route.params.id;
const isEditMode = ref(false);

// Field form
const content = ref(""); // Description
const result = ref(""); // Result
const birthDate = ref(null); // Date
const image = ref([]); // Dari DropZone

const baseUrl = import.meta.env.VITE_API_URL;

// Inject global loading
const globalLoading = inject("globalLoading");

const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

// Reset form
const resetForm = () => {
  content.value = "";
  result.value = "";
  birthDate.value = "";
  images.value = [];
};

// Fetch data kalau edit
const fetchFawReportDetail = async () => {
  try {
    globalLoading?.show();
    const res = await axios.get(`${ENDPOINTS.fawreport}/${id}`);
    const data = res.data.data;

    // Tetap ambil dari API, tidak fallback
    content.value = data.description || "";
    result.value = data.result ? stripHtml(data.result) : "";
    birthDate.value = data.date || null;

    // Preview foto lama
    if (data.photos && data.photos.length > 0) {
      image.value = data.photos.map(
        (photo) => `${baseUrl}/storage/${photo.photo_path}`
      );
    }
  } catch (error) {
    console.error("Gagal ambil detail FAW Report:", error);
  } finally {
    globalLoading?.hide();
  }
};

// Submit
const submitFawReport = async () => {
  try {
    globalLoading?.show();
    const formData = new FormData();
    formData.append("description", content.value);
    formData.append("result", result.value);
    formData.append("date", birthDate.value);

    // Append image baru kalau ada
    image.value.forEach((file) => {
      if (file instanceof File) {
        formData.append("photos[]", file);
      }
    });

    if (isEditMode.value) {
      formData.append("_method", "PUT");
      await axios.post(`${ENDPOINTS.fawreport}/${id}`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      snackbarMessage.value = "FAW Report berhasil diupdate!";
    } else {
      await axios.post(ENDPOINTS.fawreport, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      snackbarMessage.value = "FAW Report berhasil dipublish!";
    }

    isSnackbarTopEndVisible.value = true;
    router.push("/fawreport");
  } catch (error) {
    console.error("Error submit FAW Report:", error);
    alert("Gagal mengirim FAW Report");
  } finally {
    globalLoading?.hide();
  }
};

// Fungsi untuk hilangkan tag HTML
const stripHtml = (html) => {
  if (!html) return "";
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = html;
  return tempDiv.textContent || tempDiv.innerText || "";
};

onMounted(() => {
  if (id) {
    isEditMode.value = true;
    fetchFawReportDetail();
  }
});
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
        <DropZone v-model="image" />
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
