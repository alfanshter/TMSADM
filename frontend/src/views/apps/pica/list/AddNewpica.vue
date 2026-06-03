<script setup>
import { ref, watch, computed } from "vue";
import { ENDPOINTS } from "@/config/api";
import axios from "axios";

// Props
const props = defineProps({
  isDrawerOpen: Boolean,
  editData: { type: Object, default: null }, // kalau null → tambah, kalau ada → edit
});

const emit = defineEmits(["update:isDrawerOpen", "user-data", "refresh-data"]);

// Form state
const form = ref({
  problem: "",
  cause: "",
  corrective_action: "",
  date: "",
  pic: "",
  status: "",
});

const isSubmitting = ref(false);

// Reset function
const resetForm = () => {
  form.value = {
    problem: "",
    cause: "",
    corrective_action: "",
    date: "",
    pic: "",
    status: "",
  };
};

// Watch jika editData berubah → isi form
watch(
  () => props.editData,
  (val) => {
    if (val) {
      form.value = {
        problem: val.problem || "",
        cause: val.cause || "",
        corrective_action: val.corrective_action || "",
        date: val.date || "",
        pic: val.pic || "",
        status: val.status || "",
      };
    } else {
      resetForm();
    }
  },
  { immediate: true }
);

// Reset form saat drawer ditutup
watch(
  () => props.isDrawerOpen,
  (open) => {
    if (!open) resetForm();
  }
);



// Submit function
const submitForm = async () => {
  isSubmitting.value = true;
  try {
    if (props.editData && props.editData.id) {
      // Mode update: kirim payload ke parent
      emit("refresh-data", { id: props.editData.id, ...form.value });
    } else {
      // Mode add: cukup kirim payload mentah ke parent
      emit("user-data", form.value);
    }
    emit("update:isDrawerOpen", false);
  } catch (error) {
    console.error("Error saving PICA:", error);
  } finally {
    isSubmitting.value = false;
  }
};

const closeNavigationDrawer = () => {
  emit("update:isDrawerOpen", false);
  resetForm();
};
</script>

<template>
  <VNavigationDrawer
    :model-value="isDrawerOpen"
    location="end"
    temporary
    width="400"
    @update:model-value="val => emit('update:isDrawerOpen', val)"
  >
    <AppDrawerHeaderSection
      title="Tambah Pica"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <VCardText>
      <VForm @submit.prevent="submitForm">
        <VTextField
          v-model="form.problem"
          label="Problem"
          placeholder="Enter problem"
          class="mb-4"
          required
        />
        <VTextField
          v-model="form.cause"
          label="Cause"
          placeholder="Enter cause"
          class="mb-4"
          required
        />
        <VTextField
          v-model="form.corrective_action"
          label="Corrective Action"
          placeholder="Enter corrective action"
          class="mb-4"
          required
        />
         <AppDateTimePicker
              v-model="form.date"
              label="Date"
              placeholder="Select Date"
              class="mb-4"
            />
        <VTextField
          v-model="form.pic"
          label="PIC"
          placeholder="Enter"
          class="mb-4"
          required
        />
        <VTextField
          v-model="form.status"
          label="Status"
          placeholder="Open / Closed"
          class="mb-4"
          required
        />

        <div class="d-flex justify-end gap-2 mt-4">
          <VBtn
            type="submit"
            color="primary"
            :loading="isSubmitting"
          >
            {{ props.editData ? "Update" : "Save" }}
          </VBtn>

          <VBtn
            variant="outlined"
            color="error"
            @click="emit('update:isDrawerOpen', false)"
            :disabled="isSubmitting"
          >
            Cancel
          </VBtn>
        </div>
      </VForm>
    </VCardText>
  </VNavigationDrawer>
</template>
