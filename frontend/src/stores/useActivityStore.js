import { defineStore } from "pinia";

export const useActivityStore = defineStore("activity", {
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
