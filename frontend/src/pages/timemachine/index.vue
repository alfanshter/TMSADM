<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewTimeMachineDrawer from "@/views/apps/timemachine/list/AddNewItemmachine.vue";
import axios from "axios";
import { onMounted, ref } from "vue";

// State
const data = ref([]);
const totalData = ref(0);

const searchQuery = ref("");
const selectedRole = ref();

const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);
// Headers sesuai data backend
const headers = [
  { title: "Nama Machine", key: "name" },
  { title: "Code", key: "code" },
  { title: "Location Telepon", key: "location" },
  { title: "Scope of work", key: "scope_of_work" },
];
const roles = [
  {
    title: "Safety",
    value: "Production",
  }
];

// Ambil data user
const fetchdata = async () => {
  try {
    const res = await axios.get(ENDPOINTS.itemmachine);

    // Asumsikan data langsung array
    data.value = res.data.data;
    console.log("Daftar Data:", data.value);

    // Validasi agar tidak error
    data.value = data.value;
    totalData.value = Array.isArray(data.value) ? data.value.length : 0;
  } catch (error) {
    console.error("Error fetching :", error);
  }
};

// Run saat component mounted
onMounted(() => {
  fetchdata();
});

// Tambah item machine
const addData = async (data) => {
  try {
    const res = await axios.post(ENDPOINTS.itemmachine, data);
    console.log("Data berhasil ditambahkan:", res.data);
    fetchdata(); // refresh data setelah tambah user
  } catch (error) {
    console.error("Error adding data:", error);
  }
};

// Delete data
const deleteData = async (id) => {
  try {
    await axios.delete(`${ENDPOINTS.itemmachine}/${id}`);
    fetchdata();
  } catch (error) {
    console.error("Error deleting data:", error);
  }
};

// Dummy resolveRole (supaya role tampil icon-nya)
const resolveUserRoleVariant = (role) => {
  return {
    icon: "ri-user-line",
    color: "primary",
  };
};

// Dummy resolve status (jika status boolean)
const resolvedataStatusVariant = (status) => {
  return status ? "success" : "error";
};

const isAddNewUserDrawerVisible = ref(false);
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
              v-model="selectedRole"
              label="Select Role"
              placeholder="Select Role"
              :items="roles"
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
              placeholder="Search Data"
              density="compact"
            />
          </div>
          <!-- 👉 Add user button -->
          <VBtn @click="isAddNewUserDrawerVisible = true"> Add Data </VBtn>
        </div>
      </VCardText>

      <!-- SECTION datatable -->
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="data"
        :loading="isLoading"
        class="text-no-wrap rounded-0"
        :items-per-page="itemsPerPage"
      >
        <!-- User -->
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="34"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="
                !item.avatar
                  ? resolveUserRoleVariant(item.role).color
                  : undefined
              "
              class="me-3"
            >
              <VImg v-if="item.avatar" :src="item.avatar" />
              <span v-else>{{ item.name.charAt(0).toUpperCase() }}</span>
            </VAvatar>

            <div class="d-flex flex-column">

              <span class="text-sm text-medium-emphasis">{{ item.name }}</span>
            </div>
          </div>
        </template>

     
        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn size="small" @click="deleteUser(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <IconBtn
            size="small"
          >
            <VIcon icon="ri-eye-line" />
          </IconBtn>

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
                <VListItem link>
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
              {{ paginationMeta({ page, itemsPerPage }, totalData) }}
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
                :disabled="page >= Math.ceil(totalData / itemsPerPage)"
                @click="
                  page >= Math.ceil(totalData / itemsPerPage)
                    ? (page = Math.ceil(totalData / itemsPerPage))
                    : page++
                "
              />
            </div>
          </div>
        </template>
      </VDataTable>
      <!-- SECTION -->
    </VCard>
    <!-- 👉 Add New User -->
    <AddNewTimeMachineDrawer
      v-model:isDrawerOpen="isAddNewUserDrawerVisible"
      @user-data="addData"
    />
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
