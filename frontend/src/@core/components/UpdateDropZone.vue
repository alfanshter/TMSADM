<script setup>
import { useDropZone, useFileDialog, useObjectUrl } from "@vueuse/core";
import { ref, watch } from "vue";

const props = defineProps({
  label: String,
  modelValue: {
    type: Array,
    default: () => [],
  },
});
const emit = defineEmits(["update:modelValue"]);

const dropZoneRef = ref();
const fileData = ref([]);
const apiUrl = import.meta.env.VITE_API_URL;

// ✅ Inisialisasi dari parent (bisa file lama atau baru)
watch(
  () => props.modelValue,
  (val) => {
    if (Array.isArray(val)) {
      val.forEach((item) => {
        // kalau sudah ada, jangan duplicate
        if (!fileData.value.some((f) => f.id === item.id || f.file === item)) {
          if (item instanceof File) {
            fileData.value.push({
              file: item,
              url: useObjectUrl(item).value ?? "",
              isNew: true,
              id: null,
            });
          } else {
            if (!fileData.value.some((f) => f.id === item.id)) {
              fileData.value.push({
                file: null,
                url: `${apiUrl}/storage/${item.foto}`, // pastikan field `foto` betul
                isNew: false,
                id: item.id ?? null,
              });
            }
            // fileData.value.push({
            //   file: null,
            //   url: `${apiUrl}/storage/${item.foto}`,
            //   isNew: false,
            //   id: item.id ?? null,
            // });
          }
        }
      });
    }
  },
  { immediate: true }
);

// File dialog
const { open, onChange } = useFileDialog({ accept: "image/*" });

// Tambah file baru
function addFiles(files) {
  const newItems = [];
  files?.forEach((file) => {
    if (file.type.startsWith("image/")) {
      const fileObj = {
        file,
        url: useObjectUrl(file).value ?? "",
        isNew: true,
        id: null,
      };
      fileData.value.push(fileObj);
      newItems.push(fileObj);
    }
  });

  emit("update:modelValue", fileData.value);
}

function onDrop(droppedFiles) {
  addFiles(droppedFiles);
}

onChange((selectedFiles) => {
  if (!selectedFiles) return;
  addFiles(Array.from(selectedFiles));
});

useDropZone(dropZoneRef, onDrop);

// Hapus file
function removeFile(index) {
  fileData.value.splice(index, 1);
  emit("update:modelValue", fileData.value);
}
</script>

<template>
  <div class="flex flex-col gap-2">
    <!-- Label -->
    <div v-if="label" class="text-subtitle-1 font-weight-medium mb-2"
      :style="{ color: label === 'BEFORE' ? '#f44336' : '#4caf50' }">
      {{ label }}
    </div>

    <div class="w-full h-auto relative">
      <div ref="dropZoneRef" class="cursor-pointer" @click="() => open()">
        <!-- Kosong -->
        <div v-if="fileData.length === 0"
          class="d-flex flex-column justify-center align-center gap-y-2 pa-12 border-dashed drop-zone">
          <VAvatar variant="tonal" color="secondary" rounded>
            <VIcon icon="ri-upload-2-line" />
          </VAvatar>
          <h4 class="text-h4 text-wrap">Drag and Drop Your Image Here.</h4>
          <span class="text-disabled">or</span>
          <VBtn variant="outlined" size="small">Browse Images</VBtn>
        </div>

        <!-- Ada file -->
        <div v-else class="d-flex justify-center align-center gap-3 pa-8 border-dashed drop-zone flex-wrap">
          <VRow class="w-100">
            <VCol v-for="(item, index) in fileData" :key="index" cols="12" md="6">
              <VCard :ripple="false">
                <VCardText class="d-flex flex-column" @click.stop>
                  <VImg :src="item.url" width="100%" height="200px" class="mx-auto" cover />

                  <div class="text-center mt-2 font-weight-bold text-uppercase">
                    <VChip :color="label === 'BEFORE' ? 'error' : 'success'" size="small">
                      {{ label }}
                    </VChip>
                  </div>

                  <div class="mt-2 text-center">
                    <span class="clamp-text text-wrap">{{
                      item.file ? item.file.name : `File #${item.id}`
                      }}</span>
                    <br />
                    <span v-if="item.file">{{
                      (item.file.size / 1000).toFixed(1)
                      }} KB</span>
                    <span v-else>Lama (server)</span>
                  </div>
                </VCardText>

                <VCardActions>
                  <VBtn variant="text" block @click.stop="removeFile(index)">
                    Remove File
                  </VBtn>
                </VCardActions>
              </VCard>
            </VCol>
          </VRow>

          <!-- Tombol tambah -->
          <div class="w-full flex justify-center mt-4">
            <VBtn variant="outlined" color="primary" icon @click.stop="open()" title="Tambah Gambar">
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
