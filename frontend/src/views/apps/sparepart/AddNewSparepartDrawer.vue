<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { inject, nextTick, ref } from "vue";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import { VForm } from "vuetify/components/VForm";

const props = defineProps({
  isDrawerOpen: { type: Boolean, required: true },
});

const emit = defineEmits(["update:isDrawerOpen", "sparepart-added"]);

const globalLoading = inject("globalLoading");

const categories = [
  { title: "Belting & House", value: "Belting & House" },
  { title: "Safety", value: "Safety" },
  { title: "Tools", value: "Tools" },
  { title: "Spare part & Cons", value: "Spare part & Cons" },
];

const handleDrawerModelValueUpdate = (val) => {
  emit("update:isDrawerOpen", val);
};

const refVForm = ref();
const nama_sparepart = ref("");
const spec = ref("");
const loc = ref("");
const category = ref("");
const stok = ref("");
const remark = ref("");

const resetForm = () => {
  refVForm.value?.reset();
  emit("update:isDrawerOpen", false);
};

const closeNavigationDrawer = () => {
  emit("update:isDrawerOpen", false);
  nextTick(() => {
    refVForm.value?.reset();
    refVForm.value?.resetValidation();
  });
};

// ---- Submit sparepart ke backend ----
const submitSparepart = async () => {
  const isValid = await refVForm.value?.validate();
  if (!isValid) return;

  try {
    globalLoading?.show();
    const payload = {
      nama_sparepart: nama_sparepart.value,
      spec: spec.value,
      loc: loc.value,
      category: category.value,
      remark: remark.value,
    };
    const res = await axios.post(ENDPOINTS.spareparts, payload);

    // Emit ke parent supaya table update
    emit("sparepart-added", res.data.data);

    // Tutup drawer & reset form
    resetForm();
  } catch (error) {
    console.error("Error adding sparepart:", error.response?.data || error);
  } finally {
    globalLoading?.hide();
  }
};
</script>

<template>
  <VNavigationDrawer
    :model-value="props.isDrawerOpen"
    temporary
    location="end"
    width="370"
    border="none"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Header -->
    <AppDrawerHeaderSection
      title="Add a Sparepart"
      @cancel="closeNavigationDrawer"
    />
    <VDivider />

    <VCard flat>
      <PerfectScrollbar :options="{ wheelPropagation: false }" class="h-100">
        <VCardText style="block-size: calc(100vh - 5rem)">
          <VForm ref="refVForm" @submit.prevent="submitSparepart">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nama_sparepart"
                  label="Nama Sparepart"
                  :rules="[requiredValidator]"
                  placeholder="Masukkan Nama Sparepart"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="spec"
                  label="Spesifikasi"
                  placeholder="Masukkan Spesifikasi"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="loc"
                  label="Lokasi"
                  :rules="[requiredValidator]"
                  placeholder="Masukkan Lokasi"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="category"
                  :items="categories"
                  item-title="title"
                  item-value="value"
                  label="Kategori"
                  :rules="[requiredValidator]"
                  placeholder="Pilih Kategori"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="remark"
                  label="Remark"
                  :rules="[requiredValidator]"
                  placeholder="Masukkan Remark"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-start">
                  <VBtn type="submit" color="primary" class="me-4"> Add </VBtn>
                  <VBtn color="error" variant="outlined" @click="resetForm">
                    Discard
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </PerfectScrollbar>
    </VCard>
  </VNavigationDrawer>
</template>

<style lang="scss">
.v-navigation-drawer__content {
  overflow-y: hidden !important;
}
</style>
