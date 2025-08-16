<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewSparepartDrawer from "@/views/apps/sparepart/AddNewSparepartDrawer.vue";
import axios from "axios";
import { inject, onMounted, ref } from "vue";

// Inject global loading
const globalLoading = inject("globalLoading");

// State
const itemMachines = ref([]);
const totalItemMachines = ref(0);
const searchQuery = ref("");
const selected = ref();
const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

// Filter scope of work
const selectedScopeOfWork = ref(null);

// Snackbar state
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("Add New Item Machine Success!");

const headers = [
  { title: "Nama Sparepart", key: "nama_sparepart" },
  { title: "Spesifikasi", key: "spec" },
  { title: "Lokasi", key: "loc" },
  { title: "Kategori", key: "category" },
  { title: "Stok", key: "stok" },
  { title: "Remark", key: "remark" },
  { title: "Actions", key: "actions", sortable: false },
];

const categories = [
  { title: "Belting & House", value: "Belting & House" },
  { title: "Safety", value: "Safety" },
  { title: "Tools", value: "Tools" },
  { title: "Spare part & Cons", value: "Spare part & Cons" },
];

// Ambil data item machines
const fetchItemMachines = async () => {
  try {
    const res = await axios.get(ENDPOINTS.itemMachines);
    const result = res.data.data ?? res.data;
    itemMachines.value = result;
    totalItemMachines.value = Array.isArray(result) ? result.length : 0;
  } catch (error) {
    console.error("Error fetching item machines:", error);
  }
};

// Run saat component mounted
onMounted(() => {
  fetchItemMachines();
});

const addNewItemMachine = (itemMachineBaru) => {
  itemMachines.value.unshift(itemMachineBaru);
  totalItemMachines.value++;

  // Tampilkan snackbar
  snackbarMessage.value = "Add New Item Machine Success!";
  isSnackbarTopEndVisible.value = true;
};

// Edit item machine
const isEditSparepartDrawerVisible = ref(false);
const editedSparepart = ref(null);

const openEditDrawer = (sparepart) => {
  editedSparepart.value = { ...sparepart };
  isEditSparepartDrawerVisible.value = true;
};

const updateItemMachine = (updatedItemMachine) => {
  const index = itemMachines.value.findIndex(
    (u) => u.id === updatedItemMachine.id
  );
  if (index !== -1) itemMachines.value[index] = updatedItemMachine;
  // Tampilkan snackbar
  snackbarMessage.value = "Update Item Machine Completed!";
  isSnackbarTopEndVisible.value = true;
};

// Delete item machine
const deleteItemMachine = async (id) => {
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.itemMachines}/${id}`);
    await fetchItemMachines(); // Refresh list

    // Tampilkan snackbar
    snackbarMessage.value = "Delete Item Machine Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Error deleting item machine:", error);
  } finally {
    globalLoading?.hide();
  }
};

// Dummy resolveRole
const resolveUserRoleVariant = (role) => {
  return {
    icon: "ri-settings-2-line",
    color: "primary",
  };
};

// Dummy resolve status
const resolveUserStatusVariant = (status) => {
  return status ? "success" : "error";
};

const isAddNewItemMachinesDrawerVisible = ref(false);

const filteredItemMachines = computed(() => {
  return itemMachines.value.filter((item) => {
    const matchesScope = selectedScopeOfWork.value
      ? item.scope_of_work === selectedScopeOfWork.value
      : true;

    const matchesSearch = searchQuery.value
      ? item.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.code?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.location?.toLowerCase().includes(searchQuery.value.toLowerCase())
      : true;

    return matchesScope && matchesSearch;
  });
});

watch(selectedScopeOfWork, () => {
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

    <!-- 👉 Card Filter -->
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Contoh filter scope of work -->
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedCategory"
              label="Select Category"
              placeholder="Select Category"
              :items="categories"
              clearable
              clear-icon="ri-close-line"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <!-- 👉 Export button -->
        <VBtn
          variant="outlined"
          color="secondary"
          prepend-icon="ri-upload-2-line"
        >
          Export
        </VBtn>

        <VSpacer />

        <div class="d-flex align-center gap-4 flex-wrap">
          <!-- 👉 Search  -->
          <div class="app-user-search-filter">
            <VTextField
              v-model="searchQuery"
              placeholder="Cari Sparepart"
              density="compact"
            />
          </div>

          <!-- 👉 Add sparepart button -->
          <VBtn @click="isEditSparepartDrawerVisible = true">
            Add New Sparepart
          </VBtn>
        </div>
      </VCardText>

      <!-- SECTION datatable -->
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredSpareparts"
        :loading="isLoading"
        class="text-no-wrap rounded-0"
        :items-per-page="itemsPerPage"
      >
        <!-- Nama Sparepart -->
        <template #item.nama_sparepart="{ item }">
          <div class="d-flex align-center">
            <div class="d-flex flex-column">
              <span class="text-sm text-medium-emphasis">{{
                item.nama_sparepart
              }}</span>
            </div>
          </div>
        </template>

        <!-- Spec -->
        <template #item.spec="{ item }">
          <span>{{ item.spec }}</span>
        </template>

        <!-- Type -->
        <template #item.type="{ item }">
          <span>{{ item.type }}</span>
        </template>

        <!-- Loc -->
        <template #item.loc="{ item }">
          <span>{{ item.loc }}</span>
        </template>

        <!-- Category -->
        <template #item.category="{ item }">
          <span>{{ item.category }}</span>
        </template>

        <!-- Start Stock -->
        <template #item.start_stock="{ item }">
          <span>{{ item.start_stock }}</span>
        </template>

        <!-- Incoming -->
        <template #item.incoming="{ item }">
          <span>{{ item.incoming }}</span>
        </template>

        <!-- Usage -->
        <template #item.usage="{ item }">
          <span>{{ item.usage }}</span>
        </template>

        <!-- End Month -->
        <template #item.end_month="{ item }">
          <span>{{ item.end_month }}</span>
        </template>

        <!-- Remark -->
        <template #item.remark="{ item }">
          <span>{{ item.remark }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn size="small" @click="deleteSparepart(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <IconBtn size="small" @click="openEditDrawer(item)">
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <VDivider />
          <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
            <div
              class="d-flex align-center gap-x-2 text-medium-emphasis text-base"
            >
              Rows Per Page:
              <VSelect
                v-model="itemsPerPage"
                class="per-page-select"
                variant="plain"
                :items="[10, 20, 25, 50, 100]"
              />
            </div>

            <p
              class="d-flex align-center text-base text-high-emphasis me-2 mb-0"
            >
              {{ paginationMeta({ page, itemsPerPage }, totalSpareparts) }}
            </p>

            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                icon="ri-arrow-left-s-line"
                variant="text"
                density="comfortable"
                color="high-emphasis"
                :disabled="page <= 1"
                @click="page <= 1 ? (page = 1) : page--"
              />
              <VBtn
                icon="ri-arrow-right-s-line"
                variant="text"
                density="comfortable"
                color="high-emphasis"
                :disabled="page >= Math.ceil(totalSpareparts / itemsPerPage)"
                @click="
                  page >= Math.ceil(totalSpareparts / itemsPerPage)
                    ? (page = Math.ceil(totalSpareparts / itemsPerPage))
                    : page++
                "
              />
            </div>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- 👉 Add New Drawer -->
    <AddNewSparepartDrawer
      v-model:isDrawerOpen="isEditSparepartDrawerVisible"
      @item-data="addNewSparepart"
    />

    <!-- 👉 Edit Drawer -->
    <EditSparepartDrawer
      v-model:isDrawerOpen="isEditSparepartDrawerVisible"
      :sparepart="editedSparepart"
      @update-sparepart="updateSparepart"
    />
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
