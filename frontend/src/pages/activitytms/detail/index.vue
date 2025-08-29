<script setup>


// Contoh data, nanti bisa diisi dari API
const maintenanceData = ref({
  machineName: 'Mesin A',
  code: 'MS-001',
  location: 'Plant 1',
  date: '2025-08-29',
  scopeOfWork: ['Production', 'Safety'],
  downtimeProduction: '2 hours',
  maintenanceTypes: [
    {
      type: 'Cleaning Critical',
      photosBefore: ['@images/before1.png', '@images/before2.png'],
      photosAfter: ['@images/after1.png'],
      jsaFiles: ['jsa1.pdf'],
    },
    {
      type: 'Replacement Part',
      photosBefore: ['@images/before3.png'],
      photosAfter: ['@images/after2.png'],
      jsaFiles: ['jsa2.pdf'],
      spareParts: [
        { name: 'Gear', qty: 2, spec: 'G-20', loc: 'Rack A', type: 'Mechanical' },
        { name: 'Belt', qty: 1, spec: 'B-10', loc: 'Rack B', type: 'Mechanical' },
      ],
    },
  ],
})
</script>

<template>
  <div>
    <!-- Mesin Info -->
    <VCard class="mb-6">
      <VCardItem>
        <template #title>
          <h5 class="text-h5">Machine Info</h5>
        </template>
      </VCardItem>
      <VCardText>
        <div class="d-flex flex-column gap-y-2">
          <div><strong>Machine Name:</strong> {{ maintenanceData.machineName }}</div>
          <div><strong>Code:</strong> {{ maintenanceData.code }}</div>
          <div><strong>Location:</strong> {{ maintenanceData.location }}</div>
          <div><strong>Date:</strong> {{ maintenanceData.date }}</div>
        </div>
      </VCardText>
    </VCard>

    <!-- Scope of Work -->
    <VCard class="mb-6">
      <VCardItem>
        <template #title>
          <h5 class="text-h5">Scope of Work</h5>
        </template>
      </VCardItem>
      <VCardText>
        <div class="d-flex flex-column gap-y-2">
          <div v-for="scope in maintenanceData.scopeOfWork" :key="scope">
            <strong>{{ scope }}:</strong>
            <span v-if="scope === 'Production'">
              Downtime: {{ maintenanceData.downtimeProduction }}
            </span>
            <span v-else>Check safety procedures</span>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Maintenance Types -->
    <div v-for="mt in maintenanceData.maintenanceTypes" :key="mt.type" class="mb-6">
      <VCard>
        <VCardItem>
          <template #title>
            <h5 class="text-h5">{{ mt.type }}</h5>
          </template>
        </VCardItem>

        <VCardText>
          <!-- Photos Before -->
          <div v-if="mt.photosBefore && mt.photosBefore.length">
            <h6 class="text-h6">Photos Before:</h6>
            <div class="d-flex gap-2 flex-wrap">
              <VAvatar
                v-for="(photo, idx) in mt.photosBefore"
                :key="idx"
                size="80"
                :image="photo"
                rounded
              />
            </div>
          </div>

          <!-- Photos After -->
          <div v-if="mt.photosAfter && mt.photosAfter.length" class="mt-4">
            <h6 class="text-h6">Photos After:</h6>
            <div class="d-flex gap-2 flex-wrap">
              <VAvatar
                v-for="(photo, idx) in mt.photosAfter"
                :key="idx"
                size="80"
                :image="photo"
                rounded
              />
            </div>
          </div>

          <!-- JSA Files -->
          <div v-if="mt.jsaFiles && mt.jsaFiles.length" class="mt-4">
            <h6 class="text-h6">JSA Files:</h6>
            <ul>
              <li v-for="(file, idx) in mt.jsaFiles" :key="idx">
                <a :href="file" target="_blank">{{ file }}</a>
              </li>
            </ul>
          </div>

          <!-- Spare Parts -->
          <div v-if="mt.type === 'Replacement Part' && mt.spareParts?.length" class="mt-4">
            <h6 class="text-h6">Spare Parts Replaced:</h6>
            <VDataTable
              :headers="[
                { title: 'Name', key: 'name' },
                { title: 'Qty', key: 'qty' },
                { title: 'Spec', key: 'spec' },
                { title: 'Loc', key: 'loc' },
                { title: 'Type', key: 'type' }
              ]"
              :items="mt.spareParts"
              item-value="name"
              class="text-no-wrap"
            />
          </div>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>
