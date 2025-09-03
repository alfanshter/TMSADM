<script setup>
import { ref, onMounted, inject } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { ENDPOINTS } from "@/config/api";

const route = useRoute();
const router = useRouter();
const globalLoading = inject("globalLoading");

const id = route.query.id || route.params.id; // ambil id
const isEditMode = ref(false);

// Form fields
const content = ref("");
const result = ref("");
const birthDate = ref(null);
const images = ref([]); // DropZone

const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

// Fungsi strip HTML
const stripHtml = (html) => {
  if (!html) return "";
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = html;
  return tempDiv.textContent || tempDiv.innerText || "";
};

// Reset form
const resetForm = () => {
  content.value = "";
  result.value = "";
  birthDate.value = "";
  images.value = [];
};

// Fetch detail FAW Report
const fetchFawReportDetail = async () => {
  if (!id) return;

  isEditMode.value = true;

  try {
    globalLoading?.show();
    const res = await axios.get(ENDPOINTS.fawReportDetail(id)); // <- perbaikan

    const data = res.data.data;

    
    content.value = data.description || "";
    result.value = data.result ? stripHtml(data.result) : "";
    birthDate.value = data.date || null;

    // Preview image lama
    if (data.photos && data.photos.length > 0) {
  images.value = data.photos.map((photo) => ({
    id: photo.id,
    foto: photo.photo_path, // sesuai yang dipakai UpdateDropZone
  }));
}

    
  } catch (err) {
    console.error("Gagal ambil detail FAW Report:", err);
    alert("Gagal mengambil detail FAW Report");
  } finally {
    globalLoading?.hide();
  }
};

// Submit form (create/update)
const submitFawReport = async () => {
  try {
    globalLoading?.show();

    const formData = new FormData();
    formData.append("description", content.value);
    formData.append("result", result.value);
    formData.append("date", birthDate.value);

    // ✅ Bedakan file lama & baru
    images.value
      .filter((f) => !f.isNew) // file lama → kirim ID
      .forEach((f, i) => {
        formData.append(`photos_old[${i}]`, f.id);
      });

    images.value
      .filter((f) => f.isNew) // file baru → kirim file
      .forEach((f, i) => {
        formData.append(`photos_new[${i}]`, f.file);
      });

   
      
 
      
    let res;
    console.log([...formData.entries()]);
    if (isEditMode.value) {
      res = await axios.post(`${ENDPOINTS.fawReportUpdate}/${id}`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      snackbarMessage.value = "FAW Report berhasil diupdate!";
    } else {
      res = await axios.post(ENDPOINTS.fawreport, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      snackbarMessage.value = "FAW Report berhasil dipublish!";
    }

    isSnackbarTopEndVisible.value = true;
    router.push("/fawreport");
  } catch (err) {
    console.error("Gagal submit FAW Report:", err);
    alert("Gagal mengirim FAW Report");
  } finally {
    globalLoading?.hide();
  }
};


// On mounted, fetch data jika edit mode
onMounted(() => {
  if (id) fetchFawReportDetail();
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
        <UpdateDropZone label="AFTER" v-model="images" />
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
