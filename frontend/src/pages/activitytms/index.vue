<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { inject, onMounted, ref } from "vue";

// Inject global loading
const globalLoading = inject("globalLoading");

// State
const activityTms = ref([]);
const totalActivityTms = ref(0);
const searchQuery = ref("");
const selected = ref();
const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

const headers = [
  { title: "Nama Mesin", key: "name" },
  { title: "Code", key: "code" },
  { title: "Lokasi", key: "location" },
  { title: "Scope of Work", key: "scope_of_work" },
  { title: "Maintenance Type", key: "maintenance_type" },
  { title: "Date", key: "date" },
  { title: "Actions", key: "actions", sortable: false },
];

const scope_of_work = [
  { title: "Safety", value: "safety" },
  { title: "Production", value: "production" },
];

// Ambil data item activity TMS
const fetchActivityTms = async () => {
  try {
    const res = await axios.get(ENDPOINTS.activityTms);
    const result = res.data.data ?? res.data;
    console.log("Ambil data activity TMS:", result);
    activityTms.value = result;
    totalActivityTms.value = Array.isArray(result) ? result.length : 0;
  } catch (error) {
    console.error("Error fetching activity TMS:", error);
  }
};

// Run saat component mounted
onMounted(() => {
  fetchActivityTms();
});

const addNewActivityTms = (activityTmsBaru) => {
  activityTms.value.unshift(activityTmsBaru);
  totalActivityTms.value++;
};

// Edit item machine
const isEditItemMachineDrawerVisible = ref(false);
const editedItemMachine = ref(null);

const openEditDrawer = (itemMachine) => {
  editedItemMachine.value = { ...itemMachine };
  isEditItemMachineDrawerVisible.value = true;
};

const updateItemMachine = (updatedItemMachine) => {
  const index = itemMachines.value.findIndex(
    (u) => u.id === updatedItemMachine.id
  );
  if (index !== -1) itemMachines.value[index] = updatedItemMachine;
};

// Delete item machine
const deleteItemMachine = async (id) => {
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.itemMachines}/${id}`);
    await fetchItemMachines(); // Refresh list
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

const isAddNewActivityTmsDrawerVisible = ref(false);
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <div class="d-flex mb-6">
      <VRow>
        <template v-for="(data, id) in widgetData" :key="id">
          <VCol cols="12" md="3" sm="6">
            <VCard>
              <VCardText>
                <div class="d-flex justify-space-between">
                  <div class="d-flex flex-column gap-y-1">
                    <span class="text-base text-high-emphasis">{{
                      data.title
                    }}</span>
                    <h4 class="text-h4 d-flex align-center gap-2">
                      {{ data.value }}
                      <span
                        class="text-base font-weight-regular"
                        :class="data.change > 0 ? 'text-success' : 'text-error'"
                        >({{ prefixWithPlus(data.change) }}%)</span
                      >
                    </h4>

                    <p class="text-sm mb-0">
                      {{ data.desc }}
                    </p>
                  </div>
                  <VAvatar
                    :color="data.iconColor"
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon :icon="data.icon" size="26" />
                  </VAvatar>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>
      </VRow>
    </div>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>
      <VCardText>
        <VRow>
          <!-- 👉 Select Role -->
          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectedScopeOfWork"
              label="Select Scope of Work"
              placeholder="Select Scope of Work"
              :items="scope_of_work"
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
              placeholder="Search User"
              density="compact"
            />
          </div>
        </div>
      </VCardText>

      <!-- SECTION datatable -->
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="activityTms"
        :loading="isLoading"
        class="text-no-wrap rounded-0"
        :items-per-page="itemsPerPage"
      >
        <!-- nama mesin -->
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <div class="d-flex flex-column">
              <span class="text-capitalize text-high-emphasis">{{
                item.item_machine?.name
              }}</span>
            </div>
          </div>
        </template>

        <!-- Code -->
        <template #item.code="{ item }">
          <div class="d-flex gap-2">
            <VIcon
              :icon="resolveUserRoleVariant(item.code).icon"
              :color="resolveUserRoleVariant(item.code).color"
              size="22"
            />
            <span class="text-capitalize text-high-emphasis">{{
              item.item_machine?.code
            }}</span>
          </div>
        </template>

        <!-- Lokasi -->
        <template #item.location="{ item }">
          <span class="text-capitalize text-high-emphasis">{{
            item.item_machine?.location
          }}</span>
        </template>

        <!-- Scope of Work -->
        <template #item.scope_of_work="{ item }">
          <span class="text-capitalize text-high-emphasis">{{
            item.item_machine?.scope_of_work
          }}</span>
        </template>

        <!-- Maintenance Type -->
        <template #item.maintenance_type="{ item }">
          <span class="text-capitalize text-high-emphasis">{{
            item.maintenance_type
          }}</span>
        </template>

        <!-- date -->
        <template #item.date="{ item }">
          <span class="text-capitalize text-high-emphasis">{{
            item.date
          }}</span>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="resolveUserStatusVariant(item.status === 1)"
            size="small"
            class="text-capitalize"
          >
            {{ item.status === 1 ? "Aktif" : "Tidak Aktif" }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <!-- Delete button -->
          <IconBtn size="small" @click="deleteItemMachine(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <!-- View button (opsional, bisa kamu hubungkan ke modal nanti) -->
          <IconBtn size="small">
            <VIcon icon="ri-eye-line" />
          </IconBtn>

          <!-- More menu -->
          <IconBtn size="small" color="medium-emphasis">
            <VIcon icon="ri-more-2-line" />

            <VMenu activator="parent">
              <VList>
                <VListItem link>
                  <template #prepend>
                    <VIcon icon="ri-download-line" />
                  </template>
                  <VListItemTitle>Download</VListItemTitle>
                </VListItem>

                <!-- Edit item -->
                <VListItem link @click="openEditDrawer(item)">
                  <template #prepend>
                    <VIcon icon="ri-edit-box-line" />
                  </template>
                  <VListItemTitle>Edit</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
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
              {{ paginationMeta({ page, itemsPerPage }, totalUsers) }}
            </p>

            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                class="flip-in-rtl"
                icon="ri-arrow-left-s-line"
                variant="text"
                density="comfortable"
                color="high-emphasis"
                :disabled="page <= 1"
                @click="page <= 1 ? (page = 1) : page--"
              />

              <VBtn
                class="flip-in-rtl"
                icon="ri-arrow-right-s-line"
                density="comfortable"
                variant="text"
                color="high-emphasis"
                :disabled="page >= Math.ceil(totalItemMachines / itemsPerPage)"
                @click="
                  page >= Math.ceil(totalItemMachines / itemsPerPage)
                    ? (page = Math.ceil(totalItemMachines / itemsPerPage))
                    : page++
                "
              />
            </div>
          </div>
        </template>
      </VDataTable>
      <!-- SECTION -->
    </VCard>
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
