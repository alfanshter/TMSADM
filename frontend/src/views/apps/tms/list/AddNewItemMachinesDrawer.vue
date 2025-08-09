<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { nextTick, ref } from "vue";

// Props dan emit
const props = defineProps({ isDrawerOpen: Boolean });
const emit = defineEmits(["update:isDrawerOpen", "userData"]);
// Inject global loading
const globalLoading = inject("globalLoading");

// Refs
const refForm = ref();
const isFormValid = ref(false);
const isSubmitting = ref(false); // ⬅️ Loading state baru

// Form state
const form = ref({
  name: "",
  code: "",
  location: "",
  scope_of_work: "",
});

// 👉 Submit machines data
const onSubmit = async () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (!valid) return;
    globalLoading?.show();

    try {
      const token = localStorage.getItem("token");

      const res = await axios.post(
        ENDPOINTS.itemMachines,
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

      emit("item-data", res.data.data);
      emit("update:isDrawerOpen", false);
      resetForm();
    } catch (err) {
      console.error(
        "Gagal tambah Item machines:",
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
      title="Tambah Item Machines"
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
                <VBtn
                  type="submit"
                  class="me-2"
                  :loading="isSubmitting"
                  :disabled="isSubmitting"
                >
                  Submit
                </VBtn>
                <VBtn
                  type="reset"
                  variant="outlined"
                  color="error"
                  @click="closeNavigationDrawer"
                  :disabled="isSubmitting"
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
