<script setup>
import { computed, ref } from "vue";

const page = ref(1);
const itemsPerPage = ref(10);
const searchQuery = ref("");
const isLoading = ref(false);
const isAddNewItemMachinesDrawerVisible = ref(false);

const selectedScopeOfWork = ref(null);
const scope_of_work = ["Mechanical", "Electrical", "HVAC"];

const headers = [
  { title: "No", key: "no" },
  { title: "Nama Mesin", key: "name" },
  { title: "Nomor Mesin", key: "code" },
  { title: "Lokasi", key: "location" },
  { title: "ACT / Month", key: "act" },
  { title: "Actions", key: "actions", sortable: false },
];

const itemMachines = ref([
  {
    id: 1,
    no: 1,
    name: "Bucket Elevator",
    code: "BE 1107",
    location: "Tower Lv 0 + 40m",
    act: "2x",
  },
  {
    id: 2,
    no: 2,
    name: "Rotary Distributor",
    code: "RD 1110",
    location: "Tower Lv + 33m",
    act: "2x",
  },
  {
    id: 3,
    no: 3,
    name: "Dust Collector",
    code: "S01-S04",
    location: "Tower Lv + 33m",
    act: "4x",
  },
  {
    id: 4,
    no: 4,
    name: "Dust Collector",
    code: "OPRP 2-3",
    location: "Intake Silo & Macrobin",
    act: "4x",
  },
  {
    id: 5,
    no: 5,
    name: "Silo 8 Ton - Ca Carbonate",
    code: "S01-S04",
    location: "Tower Lv + 33",
    act: "per 6 bln",
  },
  {
    id: 6,
    no: 6,
    name: "Silo 4 Ton",
    code: "F01-F04",
    location: "Tower Lv + 33",
    act: "per 6 bln",
  },
  {
    id: 7,
    no: 7,
    name: "Macrobin",
    code: "K01-K14",
    location: "Tower Lv + 33",
    act: "per 6 bln",
  },
  {
    id: 8,
    no: 8,
    name: "Microbin",
    code: "M01-M22",
    location: "Tower Lv + 33",
    act: "per 6 bln",
  },
  {
    id: 9,
    no: 9,
    name: "Intake Unit OPRP 4",
    code: "SF 2014_1",
    location: "Microbin",
    act: "4x",
  },
  {
    id: 10,
    no: 10,
    name: "Intake Unit OPRP 5",
    code: "SF 2014_2",
    location: "Microbin",
    act: "4x",
  },
  {
    id: 11,
    no: 11,
    name: "Intake Unit OPRP 8",
    code: "SF 3101",
    location: "Handtip + pipe lile to Hoper",
    act: "4x",
  },
  {
    id: 12,
    no: 12,
    name: "Hoist 3",
    code: "2SH15A9053011",
    location: "Intake",
    act: "2x",
  },
  {
    id: 13,
    no: 13,
    name: "Exhaust Fan Unit",
    code: "-",
    location: "Tower",
    act: "1x",
  },
  {
    id: 14,
    no: 14,
    name: "Electrical Panel Exhaust Fan",
    code: "JB LIFT & EF",
    location: "6th floor area",
    act: "1x",
  },
]);

const totalItemMachines = computed(() => itemMachines.value.length);

const filteredItemMachines = computed(() => {
  if (!searchQuery.value) return itemMachines.value;
  return itemMachines.value.filter((item) =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const deleteItemMachine = (id) => {
  itemMachines.value = itemMachines.value.filter((item) => item.id !== id);
};

const openEditDrawer = (item) => {
  console.log("Edit:", item);
};
</script>

<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Filters</VCardTitle>
    </VCardItem>
    <VCardText>
      <VRow>
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
      <VBtn
        variant="outlined"
        color="secondary"
        prepend-icon="ri-upload-2-line"
      >
        Export
      </VBtn>
      <VSpacer />
      <div class="d-flex align-center gap-4 flex-wrap">
        <div class="app-user-search-filter" style="min-width: 250px; flex: 1">
          <VTextField
            v-model="searchQuery"
            placeholder="Search Machine"
            density="compact"
            variant="outlined"
            hide-details
          />
        </div>
        <VBtn @click="isAddNewItemMachinesDrawerVisible = true">
          Add New Schedule
        </VBtn>
      </div>
    </VCardText>

    <VDataTable
      v-model:page="page"
      :headers="headers"
      :items="filteredItemMachines"
      :loading="isLoading"
      class="text-no-wrap rounded-0"
      :items-per-page="itemsPerPage"
    >
      <template #item.no="{ item }">
        <span>{{ item.no }}</span>
      </template>

      <template #item.name="{ item }">
        <span>{{ item.name }}</span>
      </template>

      <template #item.code="{ item }">
        <span>{{ item.code }}</span>
      </template>

      <template #item.location="{ item }">
        <span>{{ item.location }}</span>
      </template>

      <template #item.act="{ item }">
        <span>{{ item.act }}</span>
      </template>

      <template #item.actions="{ item }">
        <VBtn
          size="small"
          color="error"
          variant="text"
          @click="deleteItemMachine(item.id)"
        >
          Delete
        </VBtn>
        <VBtn size="small" variant="text" @click="openEditDrawer(item)">
          Edit
        </VBtn>
      </template>

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
          <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
            {{ page }} / {{ Math.ceil(totalItemMachines / itemsPerPage) }}
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
  </VCard>
</template>
