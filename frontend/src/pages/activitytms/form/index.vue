<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { ref, watch } from "vue";
const content = ref(`<p>
      This is a radically reduced version of tiptap. It has support for a document, with paragraphs and text. That's it. It's probably too much for real minimalists though.
    </p>
    <p>
      The paragraph extension is not really required, but you need at least one node. Sure, that node can be something different.
    </p>`);

const activeTab = ref("Restock");

const selectedAttrs = ref(["Biodegradable", "Expiry Date"]);
const selectedMaintenanceTypes = ref([]);
const birthDate = ref("");

const code = ref("");
const location = ref("");
const scopeOfWork = ref("");

const itemMachines = ref([]);
const totalItemMachines = ref(0);
const selectedItemMachine = ref(null);

const fetchItemMachines = async () => {
  try {
    const res = await axios.get(ENDPOINTS.itemMachines);
    const result = res.data.data ?? res.data;
    itemMachines.value = result;
    totalItemMachines.value = Array.isArray(result) ? result.length : 0;
    console.log("Data itemMachines:", itemMachines.value);
  } catch (error) {
    console.error("Error fetching item machines:", error);
  }
};

watch(selectedItemMachine, (newVal) => {
  if (newVal) {
    // Jika kamu simpan ID saja di selectedItemMachine:
    const selected = itemMachines.value.find((item) => item.id === newVal);

    if (selected) {
      code.value = selected.code ?? "";
      location.value = selected.location ?? "";
      scopeOfWork.value = selected.scope_of_work ?? "";
    }
  } else {
    code.value = "";
    location.value = "";
    scopeOfWork.value = "";
  }
});

onMounted(() => {
  fetchItemMachines();
});
</script>

<template>
  <div>
    <div class="d-flex flex-wrap justify-space-between gap-4 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 mb-1">Add a new Activity TMS</h4>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn variant="outlined" color="secondary"> Discard </VBtn>
        <VBtn variant="outlined" color="primary"> Save Draft </VBtn>
        <VBtn>Publish Product</VBtn>
      </div>
    </div>

    <VRow>
      <VCol md="12">
        <!-- item machine -->
        <VCard class="mb-6">
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <VSelect
                  v-model="selectedItemMachine"
                  :items="itemMachines"
                  item-title="name"
                  item-value="id"
                  placeholder="Item Machine"
                  label="Item Machine"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="code"
                  label="Code"
                  readonly
                  placeholder="FXSK123U"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="location"
                  label="Location"
                  readonly
                  placeholder="Tower 1"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="scopeOfWork"
                  label="Scope_of_work"
                  readonly
                  placeholder="Safety"
                />
              </VCol>

              <VCol>
                <AppDateTimePicker
                  v-model="birthDate"
                  label="Date"
                  placeholder="Select Date"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- 👉 Product Image -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title> Maintenance Type </template>

            <!-- Checkbox -->
            <div class="d-flex flex-column mt-2">
              <VCheckbox
                label="Cleaning Critical"
                value="cleaning_critical"
                v-model="selectedMaintenanceTypes"
              />
              <VCheckbox
                label="Just Cleaning"
                value="just_cleaning"
                v-model="selectedMaintenanceTypes"
              />
              <VCheckbox
                label="Replacement Part"
                value="replacement_part"
                v-model="selectedMaintenanceTypes"
              />
              <VCheckbox
                label="Preventive PM"
                value="preventive_pm"
                v-model="selectedMaintenanceTypes"
              />
            </div>
          </VCardItem>

          <VCardText>
            <DropZone />
          </VCardText>
        </VCard>
      </VCol>

      <VCol md="4" cols="12"> </VCol>
    </VRow>
  </div>
</template>

<style lang="scss" scoped>
.drop-zone {
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
}
</style>

<style lang="scss">
.inventory-card {
  .v-radio-group,
  .v-checkbox {
    .v-selection-control {
      align-items: start !important;
    }

    .v-label.custom-input {
      border: none !important;
    }
  }
}

.ProseMirror {
  p {
    margin-block-end: 0;
  }

  padding: 0.5rem;
  outline: none;

  p.is-editor-empty:first-child::before {
    block-size: 0;
    color: #adb5bd;
    content: attr(data-placeholder);
    float: inline-start;
    pointer-events: none;
  }
}
</style>
