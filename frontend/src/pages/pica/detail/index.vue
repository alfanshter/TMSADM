<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const problem = ref("");
const cause = ref("");
const corrective_action = ref("");
const date = ref("");
const pic = ref("");
const status = ref("");

const isLoading = ref(false);

const fetchPicaDetail = async () => {
  const id = route.query.id;
  if (!id) {
    router.push("/pica");
    return;
  }

  isLoading.value = true;
  try {
    const res = await axios.get(ENDPOINTS.getPica(id));
    const d = res.data.data;

    problem.value = d.problem || "";
    cause.value = d.cause || "";
    corrective_action.value = d.corrective_action || "";
    date.value = d.date || "";
    pic.value = d.pic || "";
    status.value = d.status || "";
  } catch (err) {
    console.error("Gagal ambil detail PICA:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchPicaDetail);
</script>

<template>
  <section>
    <h4 class="text-h4 mb-4">PICA Detail</h4>

    <VProgressCircular
      v-if="isLoading"
      indeterminate
      color="primary"
      class="mb-4"
    />

    <VRow v-else>
      <VCol cols="12" md="6">
        <VCard class="mb-6 mt-4">
          <VCardText>
            <div class="mb-2"><strong>Problem:</strong> {{ problem }}</div>
            <div class="mb-2"><strong>Cause:</strong> {{ cause }}</div>
            <div class="mb-2"><strong>Corrective Action:</strong> {{ corrective_action }}</div>
            <div class="mb-2"><strong>Date:</strong> {{ date }}</div>
            <div class="mb-2"><strong>PIC:</strong> {{ pic }}</div>
            <div class="mb-2"><strong>Status:</strong> {{ status }}</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>
