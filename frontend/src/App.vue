<script setup>
import ScrollToTop from "@core/components/ScrollToTop.vue";
import initCore from "@core/initCore";
import { initConfigStore, useConfigStore } from "@core/stores/config";
import { hexToRgb } from "@core/utils/colorConverter";
import { provide, ref } from "vue";
import { useTheme } from "vuetify";

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
      <!-- Router View -->
      <RouterView />

      <!-- Scroll to top button -->
      <ScrollToTop />

      <!-- Custom Global Loading Overlay -->
      <VOverlay
        v-if="isLoading"
        :model-value="true"
        class="d-flex align-center justify-center"
        persistent
      >
        <VProgressCircular :size="60" color="primary" indeterminate />
      </VOverlay>
    </VApp>
  </VLocaleProvider>
</template>
