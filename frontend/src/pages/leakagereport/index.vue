<script setup>
const firstName = ref("");
const email = ref("");
const mobile = ref();
const password = ref();
const checkbox = ref(false);
const files = ref([]);

// Fungsi ketika user pilih file
const handleFileChange = (event) => {
  const selectedFiles = Array.from(event.target.files);
  files.value.push(...selectedFiles);
};

// Fungsi hapus file dari daftar
const removeFile = (index) => {
  files.value.splice(index, 1);
};

// Fungsi tambah input file baru (klik tombol)
const addFileInput = () => {
  document.getElementById("fileInput").click();
};

function handleFileUpload(e) {
  const uploaded = Array.from(e.target.files);
  files.value.push(...uploaded);
  e.target.value = ""; // reset supaya bisa pilih file yang sama lagi
}
</script>

<template>
  <VCol md="8" class="mx-auto">
    <!-- 👉 Laekage Report -->
    <VCard class="mb-6" title="Laekage Report">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <!-- 👉 report Image -->
            <VCard class="mb-6">
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
                <div v-if="files.length" class="d-flex flex-column gap-4">
                  <VCard
                    v-for="(file, index) in files"
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
                        Delate
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
          <VCol cols="12" md="6">
            <VSelect
              placeholder="Select Location"
              label="Location"
              :items="[
                'Lantai 1',
                'Lantai 2',
                'Lantai 3',
                'Lantai 4',
                'Lantai 5',
                'Lantai 6',
                'Cust.blend',
              ]"
            />
          </VCol>
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="birthDate"
              label="Date"
              placeholder="Select Date"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <div class="d-flex flex-wrap justify-end gap-4 mb-6">
      <div class="d-flex gap-4 align-center-end flex-wrap">
        <VBtn variant="outlined" color="secondary"> Discard </VBtn>
        <VBtn variant="outlined" color="primary"> Save Draft </VBtn>
        <VBtn>Publish Report</VBtn>
      </div>
    </div>
  </VCol>
</template>
