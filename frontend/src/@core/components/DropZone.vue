<script setup>
import { useDropZone, useFileDialog, useObjectUrl } from "@vueuse/core";

import { ref } from "vue";

const dropZoneRef = ref();
const fileData = ref([]);
const { open, onChange } = useFileDialog({ accept: "image/*" });

function onDrop(DroppedFiles) {
  DroppedFiles?.forEach((file) => {
    if (file.type.slice(0, 6) !== "image/") {
      alert("Only image files are allowed");
      return;
    }
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? "",
    });
  });
}

onChange((selectedFiles) => {
  if (!selectedFiles) return;
  for (const file of selectedFiles) {
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? "",
    });
  }
});

useDropZone(dropZoneRef, onDrop);
</script>
<template>
  <div class="flex">
    <div class="w-full h-auto relative">
      <div ref="dropZoneRef" class="cursor-pointer" @click="() => open()">
        <!-- Jika belum ada gambar -->
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

        <!-- Jika ada gambar -->
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

                  <!-- Label Before / After -->
                  <div class="text-center mt-2 font-weight-bold text-uppercase">
                    <VChip
                      v-if="index % 2 === 0 || index % 2 === 1"
                      :color="index % 2 === 0 ? 'error' : 'success'"
                      size="small"
                    >
                      {{ index % 2 === 0 ? "Before" : "After" }}
                    </VChip>
                  </div>

                  <!-- File info -->
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
