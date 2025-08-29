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

onMounted(async () => {
  try {
    const now = new Date();
    const year = now.getFullYear();
    const monthNum = String(now.getMonth() + 1).padStart(2, "0");
    const month = `${year}-${monthNum}`;
    const [activityRes, fawRes, leakageRes, scheduleRes] = await Promise.all([
      axios.get(ENDPOINTS.activityTms),
      axios.get(ENDPOINTS.fawreport),
      axios.get(ENDPOINTS.leakageReports),
      axios.get(`${ENDPOINTS.ACTIVITY_SUMMARY}?month=${month}`),
      
    ]);

    // helper untuk hitung jumlah data (array langsung atau data.data)
    const getCount = (res) => {
      if (!res || !res.data) return 0;
      // Kalau res.data langsung array
      if (Array.isArray(res.data)) return res.data.length;
      // Kalau res.data.data array (untuk endpoint lain)
      if (Array.isArray(res.data.data)) return res.data.data.length;
      return 0;
    };
    statisticsWithImages.value = [
      {
        title: "Activity TMS",
        subtitle: "Year of 2025",
        stats: getCount(activityRes).toString(),
        image: illustration1,
        imgWidth: 99,
        color: "primary",
        url: "/activitytms"
      },
      {
        title: "FAW Report",
        subtitle: "Year of 2025",
        stats: getCount(fawRes).toString(),
        image: illustration2,
        imgWidth: 85,
        color: "success",
        url: "/fawreport"
      },
      {
        title: "Leakage Report",
        subtitle: "Year of 2025",
        stats: getCount(leakageRes).toString(),
        image: illustration3,
        imgWidth: 85,
        color: "success",
        url: "/leakagereport"
      },
      {
        title: "Schedule",
        subtitle: "Year of 2025",
        stats: getCount(scheduleRes).toString(),
        image: illustration4,
        imgWidth: 85,
        color: "success",
        url: "/schedule"
      },
    ];
  } catch (error) {
    console.error("Gagal memuat statistik dashboard:", error);
  }
});
</script>

<template>
  <VRow class="match-height">
    <!-- 👉 Sales Overview -->
    <VCol cols="12" md="6">
      <EcommerceSalesOverview />
    </VCol>

    <!-- 👉 Images Cards -->
    <VCol
      v-for="statistics in statisticsWithImages"
      :key="statistics.title"
      cols="12"
      sm="6"
      md="3"
    >
      <router-link :to="statistics.url" style="text-decoration:none; color:inherit;">
    <CardStatisticsWithImages v-bind="statistics" />
  </router-link>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core/scss/template/libs/apex-chart.scss";
</style>
