<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { nextTick, ref, watch } from "vue";

// Props dan emit
const props = defineProps({
  isDrawerOpen: Boolean,
  itemMachines: Object, // item machines yang akan diedit
});
const emit = defineEmits(["update:isDrawerOpen", "update-itemMachines"]);
// Inject global loading
const globalLoading = inject("globalLoading");

// Refs
const refForm = ref();
const isFormValid = ref(false);

// Form state
const form = ref({
  id: null,
  name: "",
  code: "",
  location: "",
  scope_of_work: "",
});

// Auto-set form ketika props.itemMachines berubah
watch(
  () => props.itemMachines,
  (newVal) => {
    if (newVal) {
      form.value = {
        id: newVal.id,
        name: newVal.name,
        code: newVal.code,
        location: newVal.location,
        scope_of_work: newVal.scope_of_work,
      };
    }
  },
  { immediate: true }
);

// 👉 Submit update
const onSubmit = async () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (!valid) return;
    globalLoading?.show();

    try {
      const token = localStorage.getItem("token");

      const res = await axios.put(
        `${ENDPOINTS.itemMachines}/${form.value.id}`,
        {
          name: form.value.name,
          code: form.value.code,
          location: form.value.location,
          scope_of_work: form.value.scope_of_work,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        }
      );

      emit("update-itemMachines", res.data.data ?? res.data);
      emit("update:isDrawerOpen", false);
      resetForm();
    } catch (err) {
      console.error(
        "Gagal update item machines:",
        err.response?.data || err.message
      );
    } finally {
      globalLoading?.hide();
    }
  });
};

// 👉 Reset form
const resetForm = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
  });
};

const closeNavigationDrawer = () => {
  emit("update:isDrawerOpen", false);
  resetForm();
};

const handleDrawerModelValueUpdate = (val) => {
  emit("update:isDrawerOpen", val);
};
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      title="Edit Item Machines"
      @cancel="closeNavigationDrawer"
    />
    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  label="Nama Mesin"
                  placeholder="Nama Mesin"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.code"
                  label="Code"
                  placeholder="Masukkan Kode"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.location"
                  label="Lokasi"
                  placeholder="Masukkan Lokasi"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="form.scope_of_work"
                  label="Pilih Scope of Work"
                  :items="['safety', 'production']"
                  :rules="[requiredValidator]"
                  placeholder="Pilih Scope of Work"
                />
              </VCol>

              <VCol cols="12" class="d-flex gap-2">
                <VBtn type="submit" class="me-2">Update</VBtn>
                <VBtn
                  type="reset"
                  variant="outlined"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
