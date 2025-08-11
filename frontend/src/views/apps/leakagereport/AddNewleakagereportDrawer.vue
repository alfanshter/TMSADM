<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { inject, nextTick, ref } from "vue";

const props = defineProps({ isDrawerOpen: Boolean });
const emit = defineEmits(["update:isDrawerOpen", "report-data"]);

const globalLoading = inject("globalLoading");

const refForm = ref();
const isFormValid = ref(false);
const isSubmitting = ref(false);

const form = ref({
  location: "",
  date: null,
  files: [],
});

// Fungsi Upload File
const handleFileUpload = (e) => {
  const uploaded = Array.from(e.target.files);
  form.value.files.push(...uploaded);
  e.target.value = "";
};

// Hapus File
const removeFile = (index) => {
  form.value.files.splice(index, 1);
};

// Reset Form
const resetForm = () => {
  nextTick(() => {
    refForm.value?.reset();
    refForm.value?.resetValidation();
    form.value.files = [];
  });
};

// Tutup Drawer
const closeNavigationDrawer = () => {
  emit("update:isDrawerOpen", false);
  resetForm();
};

// Submit FAW Report
const onSubmit = async () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (!valid) return;
    isSubmitting.value = true;
    globalLoading?.show();

    try {
      const token = localStorage.getItem("token");
      const formData = new FormData();
      formData.append("location", form.value.location);
      formData.append("date", form.value.date);
      form.value.files.forEach((file) => {
        formData.append("files[]", file);
      });

      const res = await axios.post(ENDPOINTS.fawReport, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
          "Content-Type": "multipart/form-data",
        },
      });

      emit("report-data", res.data.data);
      emit("update:isDrawerOpen", false);
      resetForm();
    } catch (err) {
      console.error(
        "Gagal tambah FAW Report:",
        err.response?.data || err.message
      );
    } finally {
      globalLoading?.hide();
      isSubmitting.value = false;
    }
  });
};
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="600"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="(val) => emit('update:isDrawerOpen', val)"
  >
    <AppDrawerHeaderSection
      title="Tambah FAW Report"
      @cancel="closeNavigationDrawer"
    />
    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <!-- Upload Files -->
              <VCol cols="12">
                <VCard outlined>
                  <VCardItem>
                    <template #title> Upload Files </template>
                    <template #append>
                      <VBtn
                        variant="outlined"
                        color="primary"
                        @click="$refs.fileInput.click()"
                      >
                        Add Files
                      </VBtn>
                      <input
                        type="file"
                        ref="fileInput"
                        multiple
                        hidden
                        @change="handleFileUpload"
                      />
                    </template>
                  </VCardItem>

                  <VCardText>
                    <div
                      v-if="form.files.length"
                      class="d-flex flex-column gap-4"
                    >
                      <VCard
                        v-for="(file, index) in form.files"
                        :key="index"
                        class="pa-3"
                        outlined
                      >
                        <div class="d-flex justify-space-between align-center">
                          <div>
                            <strong>{{ file.name }}</strong>
                            <div class="text-caption text-medium-emphasis">
                              {{ (file.size / 1024).toFixed(2) }} KB
                            </div>
                          </div>
                          <VBtn
                            color="error"
                            variant="text"
                            @click="removeFile(index)"
                          >
                            Delete
                          </VBtn>
                        </div>
                      </VCard>
                    </div>
                    <div v-else class="text-medium-emphasis">
                      Belum ada file yang diunggah.
                    </div>
                  </VCardText>
                </VCard>
              </VCol>

              <!-- Location -->
              <VCol cols="12">
                <VSelect
                  v-model="form.location"
                  label="Location"
                  placeholder="Select Location"
                  :items="[
                    'Lantai 1',
                    'Lantai 2',
                    'Lantai 3',
                    'Lantai 4',
                    'Lantai 5',
                    'Lantai 6',
                    'Cust.blend',
                  ]"
                  :rules="[(v) => !!v || 'Location is required']"
                />
              </VCol>

              <!-- Date -->
              <VCol cols="12">
                <AppDateTimePicker
                  v-model="form.date"
                  label="Date"
                  placeholder="Select Date"
                  :rules="[(v) => !!v || 'Date is required']"
                />
              </VCol>

              <!-- Buttons -->
              <VCol cols="12" class="d-flex gap-2">
                <VBtn
                  type="submit"
                  :loading="isSubmitting"
                  :disabled="isSubmitting"
                >
                  Submit
                </VBtn>
                <VBtn
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
