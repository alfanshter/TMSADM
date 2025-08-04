<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { nextTick, ref, watch } from "vue";

// Props dan emit
const props = defineProps({
  isDrawerOpen: Boolean,
  user: Object, // user yang akan diedit
});
const emit = defineEmits(["update:isDrawerOpen", "update-user"]);

// Refs
const refForm = ref();
const isFormValid = ref(false);

// Form state
const form = ref({
  id: null,
  name: "",
  email: "",
  role: "",
  phone: "",
});

// Auto-set form ketika props.user berubah
watch(
  () => props.user,
  (newVal) => {
    if (newVal) {
      form.value = {
        id: newVal.id,
        name: newVal.name,
        email: newVal.email,
        role: newVal.role,
        phone: newVal.phone,
      };
    }
  },
  { immediate: true }
);

// 👉 Submit update
const onSubmit = async () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (!valid) return;

    try {
      const token = localStorage.getItem("token");

      const res = await axios.put(
        `${ENDPOINTS.users}/${form.value.id}`,
        {
          name: form.value.name,
          email: form.value.email,
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

      emit("update-user", res.data.data ?? res.data);
      emit("update:isDrawerOpen", false);
      resetForm();
    } catch (err) {
      console.error("Gagal update user:", err.response?.data || err.message);
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
    <AppDrawerHeaderSection title="Edit User" @cancel="closeNavigationDrawer" />
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
