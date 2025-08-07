<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewUserDrawer from "@/views/apps/user/list/AddNewUserDrawer.vue";
import EditUserDrawer from "@/views/apps/user/list/EditUserDrawer.vue";
import axios from "axios";
import { inject, onMounted, ref } from "vue";

// Inject global loading
const globalLoading = inject("globalLoading");

// State
const users = ref([]);
const totalUsers = ref(0);
const searchQuery = ref("");
const selectedRole = ref();
const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

// Snackbar state
const isSnackbarTopEndVisible = ref(false);
const snackbarMessage = ref("Add New User Success!");

const headers = [
  { title: "Nama Lengkap", key: "name" },
  { title: "Role", key: "role" },
  { title: "Nomor Telepon", key: "phone" },
  { title: "Status", key: "status" },
  { title: "Actions", key: "actions", sortable: false },
];

const roles = [
  { title: "Admin", value: "admin" },
  { title: "TeamLeader", value: "team_leader" },
  { title: "Supervisor", value: "supervisor" },
];

// Ambil data user
const fetchUsers = async () => {
  try {
    const res = await axios.get(ENDPOINTS.users);
    const result = res.data.data ?? res.data;
    users.value = result;
    totalUsers.value = Array.isArray(result) ? result.length : 0;
  } catch (error) {
    console.error("Error fetching users:", error);
  }
};

// Run saat component mounted
onMounted(() => {
  fetchUsers();
});

const addNewUser = (userBaru) => {
  users.value.unshift(userBaru);
  totalUsers.value++;

  // Tampilkan snackbar
  snackbarMessage.value = "Add New User Success!";
  isSnackbarTopEndVisible.value = true;
};

// Edit user
const isEditUserDrawerVisible = ref(false);
const editedUser = ref(null);

const openEditDrawer = (user) => {
  editedUser.value = { ...user };
  isEditUserDrawerVisible.value = true;
};

const updateUser = (updatedUser) => {
  const index = users.value.findIndex((u) => u.id === updatedUser.id);
  if (index !== -1) users.value[index] = updatedUser;
};

// Delete user
const deleteUser = async (id) => {
  try {
    globalLoading?.show();
    await axios.delete(`${ENDPOINTS.users}/${id}`);
    await fetchUsers(); // Refresh list

    // Tampilkan snackbar
    snackbarMessage.value = "Delete User Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Error deleting user:", error);
    // Tampilkan snackbar error
    snackbarMessage.value = "Delete User Failed!";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

// Dummy resolveRole
const resolveUserRoleVariant = (role) => {
  return {
    icon: "ri-user-line",
    color: "primary",
  };
};

// Dummy resolve status
const resolveUserStatusVariant = (status) => {
  return status ? "success" : "error";
};

const isAddNewUserDrawerVisible = ref(false);
</script>

<template>
  <section>
    <!-- ✅ Snackbar for success -->
    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
      :color="snackbarMessage.includes('Delete') ? 'error' : 'success'"
      timeout="3000"
    >
      {{ snackbarMessage }}
    </VSnackbar>
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
              placeholder="Search User"
              density="compact"
            />
          </div>
          <!-- 👉 Add user button -->
          <VBtn @click="isAddNewUserDrawerVisible = true"> Add New User </VBtn>
        </div>
      </VCardText>

      <!-- SECTION datatable -->
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="users"
        :loading="isLoading"
        class="text-no-wrap rounded-0"
        :items-per-page="itemsPerPage"
      >
        <!-- User -->
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <div class="d-flex flex-column">
              <span class="text-sm text-medium-emphasis">{{ item.email }}</span>
            </div>
          </div>
        </template>

        <!-- Role -->
        <template #item.role="{ item }">
          <div class="d-flex gap-2">
            <VIcon
              :icon="resolveUserRoleVariant(item.role).icon"
              :color="resolveUserRoleVariant(item.role).color"
              size="22"
            />
            <span class="text-capitalize text-high-emphasis">{{
              item.role
            }}</span>
          </div>
        </template>

        <!-- Nomor Telepon -->
        <template #item.phone="{ item }">
          <span>{{ item.phone }}</span>
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
          <IconBtn size="small" @click="deleteUser(item.id)">
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
                :disabled="page >= Math.ceil(totalUsers / itemsPerPage)"
                @click="
                  page >= Math.ceil(totalUsers / itemsPerPage)
                    ? (page = Math.ceil(totalUsers / itemsPerPage))
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
    <AddNewUserDrawer
      v-model:isDrawerOpen="isAddNewUserDrawerVisible"
      @user-data="addNewUser"
    />

    <EditUserDrawer
      v-model:isDrawerOpen="isEditUserDrawerVisible"
      :user="editedUser"
      @update-user="updateUser"
    />
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
