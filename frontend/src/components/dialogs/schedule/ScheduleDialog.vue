<script setup>
import americanExDark from '@images/icons/payments/img/ae-dark.png'
import americanExLight from '@images/icons/payments/img/american-express.png'
import dcDark from '@images/icons/payments/img/dc-dark.png'
import dcLight from '@images/icons/payments/img/dc-light.png'
import jcbDark from '@images/icons/payments/img/jcb-dark.png'
import jcbLight from '@images/icons/payments/img/jcb-light.png'
import masterCardDark from '@images/icons/payments/img/master-dark.png'
import masterCardLight from '@images/icons/payments/img/mastercard.png'
import visaDark from '@images/icons/payments/img/visa-dark.png'
import visaLight from '@images/icons/payments/img/visa-light.png'
import { ref, watch } from 'vue'
import axios from 'axios'
import { ENDPOINTS } from "@/config/api";
const activityData = ref([])
const isLoadingActivities = ref(false)

const activityPage = ref(1)
const activityHeaders = [
  { title: 'No', key: 'no' },
  { title: 'Machine', key: 'item_machine_name' },
  { title: 'Date', key: 'date' },
  { title: 'Action', key: 'action' },
]
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  activityIds: {
    type: Array,
    default: () => []
  }
})
console.log("dinda", props.activityIds);


const emit = defineEmits(['update:isDialogVisible'])

const visa = useGenerateImageVariant(visaLight, visaDark)
const masterCard = useGenerateImageVariant(masterCardLight, masterCardDark)
const americanEx = useGenerateImageVariant(americanExLight, americanExDark)
const jcb = useGenerateImageVariant(jcbLight, jcbDark)
const dc = useGenerateImageVariant(dcLight, dcDark)

const dialogVisibleUpdate = val => {
  emit('update:isDialogVisible', val)
}

const paymentProvidersData = [
  {
    title: 'Adyen',
    providers: [
      visa,
      masterCard,
      americanEx,
      jcb,
      dc,
    ],
  },
  {
    title: '2Checkout',
    providers: [
      visa,
      americanEx,
      jcb,
      dc,
    ],
  },

]

watch(
  () => props.activityIds,
  async (newIds) => {
    if (newIds.length === 0) {
      activityData.value = []
      return
    }

    isLoadingActivities.value = true
    try {
      const res = await axios.post(ENDPOINTS.getActivityByScheduleList, {
        ids: newIds
      });

      activityData.value = res.data.data || []
    } catch (err) {
      console.error("Gagal ambil activity:", err)
    } finally {
      isLoadingActivities.value = false
    }
  },
  { immediate: true } // supaya langsung jalan saat pertama kali dialog dibuka
)
</script>


<template>
  <VDialog :model-value="props.isDialogVisible" max-width="900" @update:model-value="dialogVisibleUpdate">
    <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
      <!-- 👉 dialog close btn -->
      <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

      <VCardText class="pt-5">

        <div class="mb-6">
          <h4 class="text-h4 text-center mb-2">
            <!-- {{ activityData.va[0].item_machine.name }} -->
          </h4>
        </div>


        <div class="mb-4">
          <h5 class="text-h5 text-center">Selected Schedule</h5><br>

          <div v-if="isLoadingActivities" class="text-center">Loading...</div>

          <ul v-else>
            <VDataTable :headers="activityHeaders" :items="activityData" :loading="isLoadingActivities"
              v-model:page="activityPage" :items-per-page="5" class="text-no-wrap rounded-0">
              <!-- No Column -->
              <template #item.no="{ index }">
                {{ (activityPage - 1) * 5 + index + 1 }}
              </template>

              <!-- Date Column -->
              <template #item.date="{ item }">
                {{ item.date || 'No Date' }}
              </template>

              <!-- Item Machine Name Column -->
              <template #item.item_machine_name="{ item }">
                {{ item.item_machine.name }}
              </template>

              <!-- Actions -->
              <template #item.action="{ item }">

                <IconBtn size="small" @click="$router.push(`/activitytms/detail?id=${item.id}`)">
                  <VIcon icon="ri-eye-line" />
                </IconBtn>
              </template>

            </VDataTable>


          </ul>

        </div>


      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
.refer-link-input {
  .v-field--appended {
    padding-inline-end: 0;
  }

  .v-field__append-inner {
    padding-block-start: 0.125rem;
  }
}
</style>
