<script setup>
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import { VForm } from "vuetify/components/VForm";

const props = defineProps({
  isDrawerOpen: { type: Boolean, required: true },
  sparepart: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:isDrawerOpen", "submit"]);

const handleDrawerModelValueUpdate = (val) => {
  emit("update:isDrawerOpen", val);
};

const refVForm = ref();
const stok_awal = ref(0);
const incoming = ref(0);
const usage = ref(0);

watch(
  () => props.sparepart,
  (val) => {
    if (val) {
      stok_awal.value = val.stok_awal || 0;
      incoming.value = val.incoming || 0;
      usage.value = val.usage || 0;
    }
  },
  { immediate: true }
);

const submitForm = () => {
  if (refVForm.value?.validate()) {
    emit("submit", {
      id: props.sparepart.id,
      stok_awal: stok_awal.value,
      incoming: incoming.value,
      usage: usage.value,
    });
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
    <AppDrawerHeaderSection
      title="Update Stok"
      @cancel="() => emit('update:isDrawerOpen', false)"
    />
    <VDivider />

    <VCard flat>
      <PerfectScrollbar :options="{ wheelPropagation: false }" class="h-100">
        <VCardText style="block-size: calc(100vh - 5rem)">
          <VForm ref="refVForm" @submit.prevent="submitForm">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model.number="stok_awal"
                  type="number"
                  label="Stok Awal"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model.number="incoming"
                  type="number"
                  label="Incoming"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model.number="usage"
                  type="number"
                  label="Usage"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-start">
                  <VBtn type="submit" color="primary" class="me-4">Update</VBtn>
                  <VBtn
                    color="error"
                    variant="outlined"
                    @click="() => emit('update:isDrawerOpen', false)"
                  >
                    Cancel
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
