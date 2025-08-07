<script setup>
import { useDropZone, useFileDialog, useObjectUrl } from "@vueuse/core";
import { ref, watch } from "vue";

// Props dan emit
const props = defineProps({
  label: String,
  modelValue: Array,
});
const emit = defineEmits(["update:modelValue"]);

// Setup
const dropZoneRef = ref();
const fileData = ref([]);

// Inisialisasi jika ada modelValue dari luar
watch(
  () => props.modelValue,
  (val) => {
    if (Array.isArray(val)) {
      fileData.value = val.map((file) => ({
        file,
        url: useObjectUrl(file).value ?? "",
      }));
    }
  },
  { immediate: true }
);

// File dialog
const { open, onChange } = useFileDialog({ accept: "image/*" });

// Tambah file
function addFiles(files) {
  const validFiles = [];
  files?.forEach((file) => {
    if (file.type.startsWith("image/")) {
      const fileObj = {
        file,
        url: useObjectUrl(file).value ?? "",
      };
      fileData.value.push(fileObj);
      validFiles.push(fileObj);
    }
  });

  emit(
    "update:modelValue",
    fileData.value.map((f) => f.file)
  );
}

function onDrop(droppedFiles) {
  addFiles(droppedFiles);
}

onChange((selectedFiles) => {
  if (!selectedFiles) return;
  addFiles(Array.from(selectedFiles));
});

useDropZone(dropZoneRef, onDrop);
</script>

<template>
  <div class="flex flex-col gap-2">
    <!-- ✅ Tampilkan label -->
    <div
      v-if="label"
      class="text-subtitle-1 font-weight-medium mb-2"
      :style="{ color: label === 'BEFORE' ? '#f44336' : '#4caf50' }"
    >
      {{ label }}
    </div>

    <div class="w-full h-auto relative">
      <div ref="dropZoneRef" class="cursor-pointer" @click="() => open()">
        <!-- Dropzone kosong -->
        <div
          v-if="fileData.length === 0"
          class="d-flex flex-column justify-center align-center gap-y-2 pa-12 border-dashed drop-zone"
        >
          <VAvatar variant="tonal" color="secondary" rounded>
            <VIcon icon="ri-upload-2-line" />
          </VAvatar>
          <h4 class="text-h4 text-wrap">Drag and Drop Your Image Here.</h4>
          <span class="text-disabled">or</span>
          <VBtn variant="outlined" size="small">Browse Images</VBtn>
        </div>

        <!-- Dropzone ada file -->
        <div
          v-else
          class="d-flex justify-center align-center gap-3 pa-8 border-dashed drop-zone flex-wrap"
        >
          <VRow class="w-100">
            <VCol
              v-for="(item, index) in fileData"
              :key="index"
              cols="12"
              md="6"
            >
              <VCard :ripple="false">
                <VCardText class="d-flex flex-column" @click.stop>
                  <VImg
                    :src="item.url"
                    width="100%"
                    height="200px"
                    class="mx-auto"
                    cover
                  />

                  <div class="text-center mt-2 font-weight-bold text-uppercase">
                    <VChip
                      :color="label === 'BEFORE' ? 'error' : 'success'"
                      size="small"
                    >
                      {{ label }}
                    </VChip>
                  </div>

                  <div class="mt-2 text-center">
                    <span class="clamp-text text-wrap">{{
                      item.file.name
                    }}</span>
                    <br />
                    <span>{{ (item.file.size / 1000).toFixed(1) }} KB</span>
                  </div>
                </VCardText>

                <VCardActions>
                  <VBtn
                    variant="text"
                    block
                    @click.stop="fileData.splice(index, 1)"
                  >
                    Remove File
                  </VBtn>
                </VCardActions>
              </VCard>
            </VCol>
          </VRow>

          <!-- Icon Add Image -->
          <div class="w-full flex justify-center mt-4">
            <VBtn
              variant="outlined"
              color="primary"
              icon
              @click.stop="open()"
              title="Tambah Gambar"
            >
              <VIcon icon="ri-add-line" />
            </VBtn>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.drop-zone {
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
}
</style>
