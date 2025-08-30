<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { ENDPOINTS } from "@/config/api";

import EcommerceSalesOverview from "@/views/dashboards/ecommerce/EcommerceSalesOverview.vue";
import illustration1 from "@images/cards/illustration-1.png";
import illustration2 from "@images/cards/illustration-2.png";
import illustration3 from "@images/cards/illustration-3.png";
import illustration4 from "@images/cards/illustration-4.png";

const statisticsWithImages = ref([]);
const isLoading = ref(true); // loader lokal

onMounted(async () => {
  try {
    const now = new Date();
    const year = now.getFullYear();
    const monthNum = String(now.getMonth() + 1).padStart(2, "0");
    const month = `${year}-${monthNum}`;

    // Ambil semua data statistik dari 1 endpoint
    const res = await axios.get(`${ENDPOINTS.dashboardStatistics}?month=${month}`);
    const data = res.data?.data || {};

    statisticsWithImages.value = [
      {
        title: "Activity TMS",
        subtitle: `Year of ${year}`,
        stats: (data.activity_tms_count || 0).toString(),
        image: illustration1,
        imgWidth: 99,
        color: "primary",
        url: "/activitytms"
      },
      {
        title: "FAW Report",
        subtitle: `Year of ${year}`,
        stats: (data.faw_report_count || 0).toString(),
        image: illustration2,
        imgWidth: 85,
        color: "success",
        url: "/fawreport"
      },
      {
        title: "Leakage Report",
        subtitle: `Year of ${year}`,
        stats: (data.leakage_report_count || 0).toString(),
        image: illustration3,
        imgWidth: 85,
        color: "success",
        url: "/leakagereport"
      },
      {
        title: "Schedule",
        subtitle: `Year of ${year}`,
        stats: (data.schedule_count || 0).toString(),
        image: illustration4,
        imgWidth: 85,
        color: "success",
        url: "/schedule"
      },
    ];
  } catch (error) {
    console.error("Gagal memuat statistik dashboard:", error);
  } finally {
    isLoading.value = false; // matikan loader
  }
});
</script>

<template>
  <VRow class="match-height">
    <!-- 👉 Sales Overview -->
    <VCol cols="12" md="6">
      <EcommerceSalesOverview />
    </VCol>

    <!-- Skeleton loader saat data belum ada -->
    <VCol v-if="isLoading" v-for="n in 4" :key="n" cols="12" sm="6" md="3">
      <VSkeletonLoader type="card" />
    </VCol>

    <!-- 👉 Images Cards muncul setelah selesai loading -->
    <VCol v-else v-for="statistics in statisticsWithImages" :key="statistics.title" cols="12" sm="6" md="3">
      <router-link :to="statistics.url" style="text-decoration:none; color:inherit;">
        <CardStatisticsWithImages v-bind="statistics" />
      </router-link>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core/scss/template/libs/apex-chart.scss";
</style>
