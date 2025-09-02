<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { ref, onMounted, inject } from "vue";
import { useRoute } from "vue-router";

// Global loading
const globalLoading = inject("globalLoading");

// Ambil ID dari route
const route = useRoute();
const fawId = route.query.id ?? null;

// State untuk FAW report detail
const description = ref("");
const result = ref("");
const date = ref("");
const photos = ref([]);

// Fungsi untuk ambil detail FAW report
const baseUrl = import.meta.env.VITE_API_URL;

// Fungsi untuk hilangkan tag HTML
const stripHtml = (html) => {
  if (!html) return "";
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = html;
  return tempDiv.textContent || tempDiv.innerText || "";
};

const fetchFawDetail = async () => {
  if (!fawId) return;
  try {
    globalLoading?.show();
    const res = await axios.get(`${ENDPOINTS.fawreport}/${fawId}`);
    const data = res.data.data ?? res.data;

    description.value = stripHtml(data.description ?? "");
    result.value = stripHtml(data.result ?? "");
    date.value = data.date ?? "";
    photos.value = data.photos?.length
      ? data.photos.map((p) => `${baseUrl}/storage/${p.photo_path}`)
      : [];
  } catch (err) {
    console.error("Gagal fetch detail FAW report:", err);
  } finally {
    globalLoading?.hide();
  }
};

onMounted(() => {
  fetchFawDetail();
});
</script>

<template>
  <section>
    <h4 class="text-h4 mb-4">FAW Report Detail</h4>

    <VRow>
      <VCol cols="12" md="6">
        <VCard class="mb-6">
          <VCardText>
            <div class="mb-2">
              <strong>Description:</strong> {{ description }}
            </div>
            <div class="mb-2"><strong>Result:</strong> {{ result }}</div>
            <div class="mb-2"><strong>Date:</strong> {{ date }}</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard v-if="photos.length" class="mb-6 mt-4">
      <VCardItem>
        <template #title>Photos</template>
        <div class="d-flex flex-wrap gap-3 mt-2">
          <VImg
            v-for="(img, idx) in photos"
            :key="idx"
            :src="img"
            max-width="250"
            class="rounded"
          />
        </div>
      </VCardItem>
    </VCard>
    <VCard v-else>
      <VCardText>Tidak ada foto pada laporan ini.</VCardText>
    </VCard>
  </section>
</template>