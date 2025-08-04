<script setup>
import ScrollToTop from "@core/components/ScrollToTop.vue";
import initCore from "@core/initCore";
import { initConfigStore, useConfigStore } from "@core/stores/config";
import { hexToRgb } from "@core/utils/colorConverter";
import { provide, ref } from "vue";
import { useTheme } from "vuetify";

// Import vue-loading-overlay
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";

const { global } = useTheme();

// Sync current theme with initial loader theme
initCore();
initConfigStore();
const configStore = useConfigStore();

// Global loading state
const isLoading = ref(false);

// Provide global loading functions
provide("globalLoading", {
  show: () => (isLoading.value = true),
  hide: () => (isLoading.value = false),
});
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- Background color based on active global theme -->
    <VApp
      :style="`--v-global-theme-primary: ${hexToRgb(
        global.current.value.colors.primary
      )}`"
    >
      <RouterView />

      <ScrollToTop />

      <!-- Global Loading Overlay -->
      <Loading v-model:active="isLoading" :is-full-page="true" />
    </VApp>
  </VLocaleProvider>
</template>
