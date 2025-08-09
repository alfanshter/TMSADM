<script setup>
const firstName = ref("");
const email = ref("");
const mobile = ref();
const password = ref();
const checkbox = ref(false);
</script>

<template>
  <VCol md="8" class="mx-auto">
    <!-- 👉 FAW Report -->
    <VCard class="mb-6" title="Faw Report">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VCol>
              <VLabel class="mb-1"> Description (Optional) </VLabel>
              <TiptapEditor v-model="content" class="border rounded-lg" />
            </VCol>
          </VCol>
          <VCol cols="12" md="6">
            <VTextField label="Result" placeholder="Done" />
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

    <!-- 👉 report Image -->
    <VCard class="mb-6">
      <VCardItem>
        <template #title> Report Image </template>
        <template #append>
          <h6 class="text-h6 text-primary cursor-pointer">
            Add Media from Computer
          </h6>
        </template>
      </VCardItem>

      <VCardText>
        <DropZone />
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
