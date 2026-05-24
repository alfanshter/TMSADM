import { defineStore } from "pinia";

const currentYear = new Date().getFullYear();
const currentMonth = String(new Date().getMonth() + 1).padStart(2, "0");

export const useActivityStore = defineStore("activity", {
  state: () => ({
    currentItem: null,
    // Filter yang persisten saat navigasi ke detail dan kembali
    selectedYear: currentYear,
    selectedMonth: currentMonth,
  }),
  actions: {
    setCurrentItem(item) {
      this.currentItem = item;
    },
    clearCurrentItem() {
      this.currentItem = null;
    },
    setFilter(year, month) {
      this.selectedYear = year;
      this.selectedMonth = month;
    },
  },
});
