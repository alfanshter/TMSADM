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
    <div v-if="label"
      class="text-subtitle-1 font-weight-medium mb-2"
      :style="{ color: label === 'BEFORE' ? '#f44336' : '#4caf50' }">
      {{ label }}
    </div>

    <!-- Preview -->
    <VRow class="w-100">
      <VCol v-for="(item, index) in fileData" :key="index" cols="12" md="6">
        <VCard :ripple="false">
          <VCardText class="d-flex flex-column">
            <VImg :src="item.url" width="100%" height="200px" class="mx-auto" cover />

            <div class="text-center mt-2 font-weight-bold text-uppercase">
              <VChip :color="label === 'BEFORE' ? 'error' : 'success'" size="small">
                {{ label }}
              </VChip>
            </div>

            <div class="mt-2 text-center">
          
              <span v-if="item.file">
                {{ (item.file.size / 1000).toFixed(1) }} KB
              </span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>


<style lang="scss" scoped>
.drop-zone {
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
}
</style>
