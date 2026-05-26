<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewSparepartDrawer from "@/views/apps/sparepart/AddNewSparepartDrawer.vue";
import AddNewStokDrawer from "@/views/apps/sparepart/AddStokDrawer.vue";
import EditSparepartDrawer from "@/views/apps/sparepart/EditSparepartDrawer.vue";
import axios from "axios";
import { computed, inject, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";

const globalLoading = inject("globalLoading");
const router = useRouter();

// STATE
const spareparts = ref([]);
const totalSpareparts = ref(0);
const searchQuery = ref("");
const selectedCategory = ref(null);
const selectedYear = ref(null);   // <-- state baru untuk tahun
const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

// Ambil data userData dari localStorage
const userData = JSON.parse(localStorage.getItem("userData"))

// Ambil role
const role = userData?.user?.role


// Dropdown tahun (5 tahun terakhir)
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => ({
  title: String(currentYear - i),
  value: currentYear - i,
}));

// SNACKBAR
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("");

// HEADERS
const headers = [
  { title: "Nama Sparepart", key: "nama_sparepart" },
  { title: "Spesifikasi", key: "spec" },
  { title: "Lokasi", key: "loc" },
  { title: "Kategori", key: "category" },
  { title: "Stok", key: "stok" },
  { title: "Incoming", key: "incoming" },
  { title: "Usage", key: "usages_sum_qty" },
  { title: "Stok Akhir", key: "end_month_stock" },
  { title: "Remark", key: "remark" },
  { title: "Actions", key: "actions", sortable: false },
];

// FILTER CATEGORIES
const categories = [
  { title: "Belting & House", value: "Belting & House" },
  { title: "Safety", value: "Safety" },
  { title: "Tools", value: "Tools" },
  { title: "Spare part & Cons", value: "Spare part & Cons" },
];

// FETCH DATA
const fetchSpareparts = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(ENDPOINTS.spareparts);
    const result = res.data.data ?? res.data;
    spareparts.value = Array.isArray(result) ? result : [];
    totalSpareparts.value = spareparts.value.length;
  } catch (err) {
    console.error("Error fetching spareparts:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchSpareparts());

// EXPORT TO EXCEL
const exportToExcel = async (year) => {
  if (!year) {
    console.error("Tahun belum dipilih");
    return;
  }
  try {
    const res = await axios.get(ENDPOINTS.exportSpareparts(year));
    
    if (res.data && res.data.file) {
      // Buka URL file di tab baru
      window.open(res.data.file, "_blank");
    } else {
      console.error("Response tidak mengandung file URL");
    }

  } catch (err) {
    console.error("Gagal export excel:", err);
  }
};


// ADD SPAREPART
const isAddNewSparepartDrawerVisible = ref(false);
const addNewSparepart = (newSparepart) => {
  spareparts.value.unshift(newSparepart);
  totalSpareparts.value++;
  snackbarMessage.value = "Add New Sparepart Success!";
  isSnackbarTopEndVisible.value = true;
};

// ADD STOK
const isAddNewStokDrawerVisible = ref(false);
const selectedSparepart = ref(null);

const openAddStokDrawer = (sparepart) => {
  selectedSparepart.value = sparepart;
  isAddNewStokDrawerVisible.value = true;
};

const handleUpdateStok = async (data) => {
  try {
    globalLoading?.show();

    const sparepartId = selectedSparepart.value?.id;
    if (!sparepartId) return console.error("Sparepart ID tidak ditemukan!");

    // pastikan field number dan tidak null
    const payload = {
      stok_awal: Number(data.stok_awal) || 0,
      incoming: Number(data.incoming) || 0,
      usage: Number(data.usage) || 0,
    };

    const res = await axios.put(`${ENDPOINTS.spareparts}/${sparepartId}`, {
      nama_sparepart: selectedSparepart.value.nama_sparepart,
      loc: selectedSparepart.value.loc,
      category: selectedSparepart.value.category,
      remark: selectedSparepart.value.remark,
      stok_awal: data.stok_awal,
      incoming: data.incoming,
      usage: data.usage,
    });

    const updatedSparepart = res.data.data;
    const idx = spareparts.value.findIndex((s) => s.id === updatedSparepart.id);
    if (idx !== -1) spareparts.value[idx] = updatedSparepart;

    snackbarMessage.value = "Add New Stok Success!";
    isSnackbarTopEndVisible.value = true;
    isAddNewStokDrawerVisible.value = false;
  } catch (err) {
    console.error("Gagal update stok:", err.response?.data || err);
  } finally {
    globalLoading?.hide();
  }
};

// EDIT SPAREPART
const isEditSparepartDrawerVisible = ref(false);
const editedSparepart = ref(null);

const openEditDrawer = (sparepart) => {
  editedSparepart.value = { ...sparepart };
  isEditSparepartDrawerVisible.value = true;
};

const updateSparepart = (updatedSparepart) => {
  const index = spareparts.value.findIndex((u) => u.id === updatedSparepart.id);
  if (index !== -1) spareparts.value[index] = updatedSparepart;
  snackbarMessage.value = "Update Sparepart Completed!";
  isSnackbarTopEndVisible.value = true;
};

// DELETE SPAREPART
const deleteSparepart = async (id) => {
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.deleteSparepart(id)}`);
    await fetchSpareparts();
    snackbarMessage.value = "Delete Sparepart Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (err) {
    console.error("Error deleting sparepart:", err);
  } finally {
    globalLoading?.hide();
  }
};

// CONFIRM DELETE DIALOG
const isConfirmDeleteDialogVisible = ref(false);
const itemToDeleteId = ref(null);

const openDeleteConfirm = (id) => {
  itemToDeleteId.value = id;
  isConfirmDeleteDialogVisible.value = true;
};

const handleConfirmDelete = async () => {
  if (!itemToDeleteId.value) return;
  isConfirmDeleteDialogVisible.value = false;
  await deleteSparepart(itemToDeleteId.value);
  itemToDeleteId.value = null;
};

// FILTER + SEARCH
const filteredSpareparts = computed(() => {
  return spareparts.value.filter((item) => {
    const matchesCategory = selectedCategory.value
      ? item.category === selectedCategory.value
      : true;
    const matchesSearch = searchQuery.value
      ? item.nama_sparepart
          ?.toLowerCase()
          .includes(searchQuery.value.toLowerCase()) ||
        item.spec?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.loc?.toLowerCase().includes(searchQuery.value.toLowerCase())
      : true;
    return matchesCategory && matchesSearch;
  });
});

watch(selectedCategory, () => {
  page.value = 1;
});
</script>

<template>
  <section>
    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
      :color="snackbarMessage.includes('Delete') ? 'error' : 'success'"
      timeout="3000"
    >
      {{ snackbarMessage }}
    </VSnackbar>

    <!-- FILTER CARD -->
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>
      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedCategory"
              label="Select Category"
              :items="categories"
              clearable
              clear-icon="ri-close-line"
            />
          </VCol>
          <VCol cols="12" sm="4">
           <VSelect
             v-model="selectedYear"
             label="Select Year"
             :items="years"
             item-title="title"
             item-value="value"
             clearable
             clear-icon="ri-close-line"
           />
          </VCol>
        </VRow>
      </VCardText>
      <VDivider />

      <!-- SEARCH + ADD -->
      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <VBtn
  variant="outlined"
  color="secondary"
  prepend-icon="ri-upload-2-line"
  :disabled="!selectedYear"
  @click="exportToExcel(selectedYear)"
>
  Export to Excel
</VBtn>
        <VSpacer />
        <div class="d-flex align-center gap-4 flex-wrap">
          <div class="app-user-search-filter">
            <VTextField
              v-model="searchQuery"
              placeholder="Cari Sparepart"
              density="compact"
            />
          </div>
          <VBtn @click="isAddNewSparepartDrawerVisible = true">Add New Sparepart</VBtn>
        </div>
      </VCardText>

      <!-- TABLE -->
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredSpareparts"
        :loading="isLoading"
        :items-per-page="itemsPerPage"
        class="text-no-wrap rounded-0"
      >
        <!-- Custom cell templates... (sama seperti punya Anda) -->
        <template #item.actions="{ item }">
          <VTooltip text="Lihat Detail" location="top">
            <template #activator="{ props }">
              <IconBtn v-bind="props" size="small" @click="router.push(`/sparepart/detail?id=${item.id}`)">
                <VIcon icon="ri-eye-line" />
              </IconBtn>
            </template>
          </VTooltip>
          <VTooltip text="Tambah Stok" location="top">
            <template #activator="{ props }">
              <IconBtn v-bind="props" size="small" @click="openAddStokDrawer(item)">
                <VIcon icon="ri-add-line" />
              </IconBtn>
            </template>
          </VTooltip>
          <VTooltip text="Edit" location="top">
            <template #activator="{ props }">
              <IconBtn v-bind="props" size="small" @click="openEditDrawer(item)">
                <VIcon icon="ri-edit-box-line" />
              </IconBtn>
            </template>
          </VTooltip>
          <VTooltip text="Hapus" location="top">
            <template #activator="{ props }">
              <IconBtn v-bind="props" size="small" @click="openDeleteConfirm(item.id)">
                <VIcon icon="ri-delete-bin-7-line" />
              </IconBtn>
            </template>
          </VTooltip>
        </template>

        <!-- Pagination (sama seperti kode Anda) -->
      </VDataTable>
    </VCard>

    <!-- DRAWERS -->
    <AddNewSparepartDrawer
      v-model:isDrawerOpen="isAddNewSparepartDrawerVisible"
      @sparepart-added="addNewSparepart"
    />
    <AddNewStokDrawer
      v-model:isDrawerOpen="isAddNewStokDrawerVisible"
      :sparepart="selectedSparepart"
      @submit="handleUpdateStok"
    />
    <EditSparepartDrawer
      v-model:isDrawerOpen="isEditSparepartDrawerVisible"
      :sparepart="editedSparepart"
      @sparepart-updated="updateSparepart"
    />

    <!-- Confirm Delete Dialog -->
    <VDialog v-model="isConfirmDeleteDialogVisible" max-width="450px">
      <VCard>
        <VCardTitle class="text-h5 text-center pa-6">
          Hapus Sparepart?
        </VCardTitle>
        <VCardText class="text-center pb-6">
          Apakah Anda yakin ingin menghapus sparepart ini dari list?
        </VCardText>
        <VCardActions class="d-flex justify-center pb-6 gap-3">
          <VBtn color="secondary" variant="outlined" @click="isConfirmDeleteDialogVisible = false">
            Batal
          </VBtn>
          <VBtn color="error" variant="flat" @click="handleConfirmDelete">
            Ya, Hapus
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>

<style scoped lang="scss">
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>