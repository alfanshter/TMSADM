<script setup>
import { nextTick, ref, watch } from "vue";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import { VForm } from "vuetify/components/VForm";

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  sparepart: { type: Object, default: () => ({}) },
});

const categories = [
  { title: "Belting & House", value: "Belting & House" },
  { title: "Safety", value: "Safety" },
  { title: "Tools", value: "Tools" },
  { title: "Spare part & Cons", value: "Spare part & Cons" },
];

const emit = defineEmits(["update:isDrawerOpen", "update-sparepart"]);

const handleDrawerModelValueUpdate = (val) => {
  emit("update:isDrawerOpen", val);
};

const refVForm = ref();
const name = ref("");
const spec = ref("");
const loc = ref("");
const category = ref("");
const remark = ref("");

// Bind data dari props.sparepart
watch(
  () => props.sparepart,
  (val) => {
    if (val) {
      name.value = val.nama_sparepart || "";
      spec.value = val.spec || "";
      loc.value = val.loc || "";
      category.value = val.category || "";
      remark.value = val.remark || "";
    }
  },
  { immediate: true }
);

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

// ===== Submit form =====
const submitForm = () => {
  if (refVForm.value?.validate()) {
    const updatedData = {
      id: props.sparepart.id,
      nama_sparepart: name.value,
      spec: spec.value,
      loc: loc.value,
      category: category.value,
      remark: remark.value,
    };
    emit("update-sparepart", updatedData); // kirim ke parent
    emit("update:isDrawerOpen", false); // tutup drawer
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
    <!-- Header -->
    <AppDrawerHeaderSection
      title="Edit Sparepart"
      @cancel="closeNavigationDrawer"
    />
    <VDivider />

    <VCard flat>
      <PerfectScrollbar :options="{ wheelPropagation: false }" class="h-100">
        <VCardText style="block-size: calc(100vh - 5rem)">
          <VForm ref="refVForm" @submit.prevent="submitForm">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="name"
                  label="Nama Sparepart"
                  :rules="[requiredValidator]"
                  placeholder="John Doe"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="spec"
                  label="Spesifikasi"
                  :rules="[requiredValidator]"
                  placeholder=""
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
                  <VBtn type="submit" color="primary" class="me-4">Update</VBtn>
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
