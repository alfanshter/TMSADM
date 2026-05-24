<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { computed, onMounted, ref, watch } from "vue";

// STATE
const logs = ref([]);
const spareparts = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const selectedSparepartId = ref(null);
const selectedAction = ref(null);
const itemsPerPage = ref(15);
const page = ref(1);

// SNACKBAR
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");
const snackbarColor = ref("success");

// ACTION OPTIONS
const actionOptions = [
  { title: "Semua", value: null },
  { title: "Tambah Stok", value: "add_stock" },
  { title: "Tambah Incoming", value: "add_incoming" },
  { title: "Penggunaan (Usage)", value: "usage" },
  { title: "Batal Penggunaan", value: "usage_cancelled" },
  { title: "Hapus Sparepart", value: "delete" },
];

// ACTION LABELS & COLORS
const actionLabel = {
  add_stock:        { text: "Tambah Stok",         color: "success" },
  add_incoming:     { text: "Tambah Incoming",     color: "info" },
  usage:            { text: "Penggunaan",          color: "warning" },
  usage_cancelled:  { text: "Batal Penggunaan",    color: "secondary" },
  delete:           { text: "Dihapus",             color: "error" },
};

// HEADERS
const headers = [
  { title: "Tanggal", key: "created_at", width: "160px" },
  { title: "Sparepart", key: "sparepart" },
  { title: "Lokasi", key: "sparepart_loc" },
  { title: "Kategori", key: "sparepart_cat" },
  { title: "Aktivitas", key: "action" },
  { title: "Jumlah", key: "qty", align: "center" },
  { title: "Keterangan", key: "keterangan" },
  { title: "User", key: "user" },
];

// FETCH SPAREPART LIST untuk dropdown filter
const fetchSpareparts = async () => {
  try {
    const res = await axios.get(ENDPOINTS.spareparts);
    const result = res.data.data ?? res.data;
    spareparts.value = Array.isArray(result) ? result : [];
  } catch (err) {
    console.error("Error fetching spareparts:", err);
  }
};

// FETCH LOGS
const fetchLogs = async () => {
  isLoading.value = true;
  try {
    const params = {};
    if (selectedSparepartId.value) params.sparepart_id = selectedSparepartId.value;
    if (selectedAction.value) params.action = selectedAction.value;

    const res = await axios.get(ENDPOINTS.allSparepartLogs, { params });
    logs.value = res.data.data ?? [];
  } catch (err) {
    console.error("Error fetching logs:", err);
    logs.value = [];
  } finally {
    isLoading.value = false;
  }
};

// COMPUTED — FILTER & SEARCH
const filteredLogs = computed(() => {
  return logs.value.filter((log) => {
    const matchSearch = searchQuery.value
      ? log.sparepart?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        log.keterangan?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        log.user?.toLowerCase().includes(searchQuery.value.toLowerCase())
      : true;
    return matchSearch;
  });
});

// SUMMARY STATS
const totalAddStock = computed(() =>
  logs.value.filter(l => l.action === "add_stock").reduce((s, l) => s + (l.qty || 0), 0)
);
const totalIncoming = computed(() =>
  logs.value.filter(l => l.action === "add_incoming").reduce((s, l) => s + (l.qty || 0), 0)
);
const totalUsage = computed(() =>
  logs.value.filter(l => l.action === "usage").reduce((s, l) => s + (l.qty || 0), 0)
);
const totalDeleted = computed(() =>
  logs.value.filter(l => l.action === "delete").length
);

watch([selectedSparepartId, selectedAction], () => {
  page.value = 1;
  fetchLogs();
});

onMounted(() => {
  fetchSpareparts();
  fetchLogs();
});
</script>

<template>
  <section>
    <VSnackbar v-model="isSnackbarTopEndVisible" location="top end" :color="snackbarColor" timeout="3000">
      {{ snackbarMessage }}
    </VSnackbar>

    <!-- Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-4 mb-6">
      <div>
        <h4 class="text-h4 mb-1">Riwayat Sparepart</h4>
        <p class="text-medium-emphasis mb-0">Audit log seluruh aktivitas sparepart</p>
      </div>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="6" sm="3">
        <VCard variant="tonal" color="success">
          <VCardText class="text-center py-4">
            <VIcon icon="ri-add-circle-line" size="28" class="mb-2" />
            <div class="text-h5 font-weight-bold">{{ totalAddStock }}</div>
            <div class="text-caption">Total Stok Ditambah</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard variant="tonal" color="info">
          <VCardText class="text-center py-4">
            <VIcon icon="ri-arrow-down-circle-line" size="28" class="mb-2" />
            <div class="text-h5 font-weight-bold">{{ totalIncoming }}</div>
            <div class="text-caption">Total Incoming</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard variant="tonal" color="warning">
          <VCardText class="text-center py-4">
            <VIcon icon="ri-tools-line" size="28" class="mb-2" />
            <div class="text-h5 font-weight-bold">{{ totalUsage }}</div>
            <div class="text-caption">Total Pemakaian</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard variant="tonal" color="error">
          <VCardText class="text-center py-4">
            <VIcon icon="ri-delete-bin-line" size="28" class="mb-2" />
            <div class="text-h5 font-weight-bold">{{ totalDeleted }}</div>
            <div class="text-caption">Item Dihapus</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filter Card -->
    <VCard>
      <VCardItem class="pb-0">
        <VCardTitle>Filter Riwayat</VCardTitle>
      </VCardItem>
      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <VAutocomplete
              v-model="selectedSparepartId"
              :items="spareparts"
              item-title="nama_sparepart"
              item-value="id"
              label="Filter Sparepart"
              placeholder="Semua sparepart"
              clearable
              density="compact"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedAction"
              :items="actionOptions"
              item-title="title"
              item-value="value"
              label="Filter Aktivitas"
              density="compact"
              clearable
            />
          </VCol>
          <VCol cols="12" sm="4">
            <VTextField
              v-model="searchQuery"
              placeholder="Cari sparepart, keterangan, user..."
              density="compact"
              prepend-inner-icon="ri-search-line"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <!-- TABLE -->
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredLogs"
        :loading="isLoading"
        :items-per-page="itemsPerPage"
        class="text-no-wrap"
      >
        <!-- Aktivitas chip -->
        <template #item.action="{ item }">
          <VChip
            :color="actionLabel[item.action]?.color ?? 'secondary'"
            size="small"
            label
          >
            {{ actionLabel[item.action]?.text ?? item.action }}
          </VChip>
        </template>

        <!-- Qty -->
        <template #item.qty="{ item }">
          <span :class="item.action === 'usage' ? 'text-warning' : (item.action === 'delete' ? 'text-error' : 'text-success')">
            {{ item.action === 'usage' ? '-' : '+' }}{{ item.qty }}
          </span>
        </template>

        <!-- Keterangan (truncate) -->
        <template #item.keterangan="{ item }">
          <span class="text-truncate" style="max-width:220px;display:block;">
            {{ item.keterangan || '-' }}
          </span>
        </template>

        <!-- User chip -->
        <template #item.user="{ item }">
          <VChip size="small" prepend-icon="ri-user-line">
            {{ item.user }}
          </VChip>
        </template>

        <template #bottom>
          <div class="d-flex align-center justify-end gap-3 pa-4">
            <span class="text-caption text-medium-emphasis">
              Total {{ filteredLogs.length }} log
            </span>
            <VPagination
              v-model="page"
              :length="Math.ceil(filteredLogs.length / itemsPerPage)"
              :total-visible="5"
              density="compact"
            />
          </div>
        </template>
      </VDataTable>
    </VCard>
  </section>
</template>
