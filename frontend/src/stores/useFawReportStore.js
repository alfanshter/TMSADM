import { defineStore } from "pinia";

export const useFawReportStore = defineStore("fawReport", {
  state: () => ({
    currentItem: null,
  }),
  actions: {
    setCurrentItem(item) {
      this.currentItem = item;
    },
    clearCurrentItem() {
      this.currentItem = null;
    },
  },
});
