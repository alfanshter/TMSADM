<script setup>
import { ENDPOINTS } from "@/config/api";
import AddNewpicaDrawer from "@/views/apps/pica/list/AddNewpica.vue";
import axios from "axios";
import { onMounted, ref } from "vue";



// State
const data = ref([]);
const totalData = ref(0);

// Inject global loading
const globalLoading = inject("globalLoading");

const snackbarMessage = ref('');
const isSnackbarTopEndVisible = ref(false)

const searchQuery = ref("");
const selectedRole = ref();

const itemsPerPage = ref(10);
const page = ref(1);
const isLoading = ref(false);

// Headers sesuai data backend PICA
const headers = [
  { title: "No", key: "no" },
  { title: "Problem", key: "problem" },
  { title: "Cause", key: "cause" },
  { title: "Corrective Action", key: "corrective_action" },
  { title: "Date", key: "date" },
  { title: "PIC", key: "pic" },
  { title: "Status", key: "status" },
  { title: "Actions", key: "actions" },
];

const roles = [
  {
    title: "Safety",
    value: "Production",
  },
];

// Ambil data PICA
const fetchdata = async () => {
  try {
    const res = await axios.get(ENDPOINTS.picas);

    // Asumsikan data langsung array
    data.value = res.data.data;
    console.log("Daftar PICA:", data.value);

    totalData.value = Array.isArray(data.value) ? data.value.length : 0;
  } catch (error) {
    console.error("Error fetching PICA:", error);
  }
};

// Run saat component mounted
onMounted(() => {
  fetchdata();
});

const addData = async (payload) => {
  try {
    globalLoading?.show();
    await axios.post(ENDPOINTS.picas, payload);
    await fetchdata(); // tunggu sampai data reload baru lanjut
    snackbarMessage.value = "Add PICA Completed!";
    isSnackbarTopEndVisible.value = true;
    isAddNewpicaDrawerVisible.value = false; // baru tutup drawer setelah selesai
  } catch (error) {
    console.error("Error adding PICA:", error);
    snackbarMessage.value = "Add PICA Failed!";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

// Delete PICA
const deleteData = async (id) => {
  try {
     globalLoading?.show();
    await axios.delete(ENDPOINTS.deletePica(id));
    fetchdata();

    // Tampilkan snackbar
    snackbarMessage.value = "Delete PICA Completed!";
    isSnackbarTopEndVisible.value = true;
  } catch (error) {
    console.error("Error deleting PICA:", error);

    // Tampilkan snackbar error
    snackbarMessage.value = "Delete PICA Failed!";
    isSnackbarTopEndVisible.value = true;
  } finally {
    globalLoading?.hide();
  }
};

// Dummy resolveRole (supaya role tampil icon-nya)
const resolveUserRoleVariant = (role) => {
  return {
    icon: "ri-user-line",
    color: "primary",
  };
};

// Dummy resolve status (jika status boolean/string)
const resolvedataStatusVariant = (status) => {
  return status === "closed" ? "success" : "warning";
};

const isAddNewpicaDrawerVisible = ref(false);
//edit
const editData = ref(null);


const handleSave = async (payload) => {
  try {
    globalLoading?.show();

    if (payload.id) {
      await axios.put(ENDPOINTS.updatePica(payload.id), payload);
      snackbarMessage.value = "Update PICA Completed!";
    } else {
      await axios.post(ENDPOINTS.picas, payload);
      snackbarMessage.value = "Add PICA Completed!";
    }

    await fetchdata();
    isAddNewpicaDrawerVisible.value = false;
    editData.value = null;
    isSnackbarTopEndVisible.value = true; 
  } catch (error) {
    console.error("Error saving PICA:", error);
    snackbarMessage.value = "Save PICA Failed!";
    isSnackbarTopEndVisible.value = true; 
  } finally {
    globalLoading?.hide();
  }
};


const openEditDrawer = (item) => {
  editData.value = { ...item };
  isAddNewpicaDrawerVisible.value = true;
};
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
                      >
                        ({{ prefixWithPlus(data.change) }}%)
                      </span>
                    </h4>

                    <p class="text-sm mb-0">{{ data.desc }}</p>
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
              placeholder="Search PICA"
              density="compact"
            />
          </div>
          <!-- 👉 Add pica button -->
          <VBtn @click="isAddNewpicaDrawerVisible = true"> Add PICA </VBtn>
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
        <!-- No -->
        <template #item.no="{ index }">
          {{ (page - 1) * itemsPerPage + index + 1 }}
        </template>
        <!-- Row rendering sesuai field PICA -->
        <template #item.problem="{ item }">
          <span>{{ item.problem }}</span>
        </template>

        <template #item.cause="{ item }">
          <span>{{ item.cause }}</span>
        </template>

        <template #item.corrective_action="{ item }">
          <span>{{ item.corrective_action }}</span>
        </template>

        <template #item.date="{ item }">
          <span>{{ item.date }}</span>
        </template>

        <template #item.pic="{ item }">
          <span>{{ item.pic }}</span>
        </template>

        <template #item.status="{ item }">
          <VChip :color="resolvedataStatusVariant(item.status)">
            {{ item.status }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn size="small" @click="deleteData(item.id)">
            <VIcon icon="ri-delete-bin-7-line" />
          </IconBtn>

          <IconBtn size="small">
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

    <!-- 👉 Add New PICA -->
    <AddNewpicaDrawer
      v-model:isDrawerOpen="isAddNewpicaDrawerVisible"
      :edit-data="editData"
      @user-data="addData"
      @refresh-data="handleSave"
    />
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
