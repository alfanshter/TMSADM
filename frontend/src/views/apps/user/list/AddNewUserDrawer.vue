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
  email: "",
  password: "",
  role: "",
  phone: "",
});

// 👉 Submit user data
const onSubmit = async () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (!valid) return;
    globalLoading?.show();

    try {
      const token = localStorage.getItem("token");

      const res = await axios.post(
        ENDPOINTS.users,
        {
          name: form.value.name,
          email: form.value.email,
          password: form.value.password,
          role: form.value.role,
          phone: form.value.phone,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        }
      );

      emit("userData", res.data.data);
      emit("update:isDrawerOpen", false);
      resetForm();
    } catch (err) {
      console.error("Gagal tambah user:", err.response?.data || err.message);
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
      title="Tambah User"
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
                  label="Nama Lengkap"
                  placeholder="John Doe"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  placeholder="johndoe@mail.com"
                  :rules="[requiredValidator, emailValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  placeholder="Min 6 karakter"
                  type="password"
                  :rules="[
                    requiredValidator,
                    (v) => v.length >= 6 || 'Minimal 6 karakter',
                  ]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.phone"
                  label="Nomor Telepon"
                  placeholder="08xxxxxxxx"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="form.role"
                  label="Pilih Role"
                  :items="['admin', 'supervisor', 'team_leader']"
                  :rules="[requiredValidator]"
                  placeholder="Pilih Role"
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
