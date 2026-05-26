<script setup>
import { ENDPOINTS } from "@/config/api";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const sparepartId = route.query.id ?? null;

const sparepart = ref(null);
const activityUsages = ref([]);
const isLoading = ref(false);

// ─── FETCH DETAIL ────────────────────────────────────────────────────────────
const fetchDetail = async () => {
  if (!sparepartId) return;
  isLoading.value = true;
  try {
    const res = await axios.get(ENDPOINTS.getSparepart(sparepartId));
    const data = res.data.data ?? res.data;
    sparepart.value = data;
    activityUsages.value = data.activity_usages ?? [];
  } catch (err) {
    console.error("Error fetching sparepart detail:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchDetail());

// ─── HEADERS TABEL ACTIVITY ──────────────────────────────────────────────────
const activityHeaders = [
  { title: "Tanggal", key: "date" },
  { title: "Nama Mesin", key: "machine_name" },
  { title: "Kode", key: "machine_code" },
  { title: "Lokasi", key: "machine_location" },
  { title: "Scope of Work", key: "scope_of_work" },
  { title: "Qty Digunakan", key: "qty" },
  { title: "Aksi", key: "actions", sortable: false },
];

// navigasi ke detail activity
function goToActivity(activityId) {
  router.push(`/activitytms/detail?id=${activityId}`);
}

function goBack() {
  router.back();
}

// chip color per kategori
const categoryColor = (cat) => {
  const map = {
    "Belting & House": "indigo",
    Safety: "error",
    Tools: "warning",
    "Spare part & Cons": "success",
  };
  return map[cat] ?? "secondary";
};

// chip color remark
const remarkColor = (remark) => {
  if (!remark) return "secondary";
  const r = remark.toLowerCase();
  if (r === "ok") return "success";
  if (r === "habis") return "error";
  return "warning";
};
</script>

<template>
  <section>
    <!-- Header -->
    <div class="d-flex align-center gap-3 mb-6">
      <VBtn icon variant="text" @click="goBack">
        <VIcon icon="ri-arrow-left-line" />
      </VBtn>
      <div>
        <h4 class="text-h4 mb-0">Detail Sparepart</h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Informasi lengkap sparepart &amp; riwayat penggunaan di Activity TMS
        </p>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <template v-if="isLoading">
      <VRow>
        <VCol cols="12" md="5">
          <VCard class="mb-6">
            <VCardText>
              <VSkeleton type="list-item-two-line" :loading="true" class="mb-2" />
              <VSkeleton type="list-item-two-line" :loading="true" class="mb-2" />
              <VSkeleton type="list-item-two-line" :loading="true" />
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" md="7">
          <VCard>
            <VCardText>
              <VSkeleton type="table" :loading="true" />
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <template v-else-if="sparepart">
      <VRow>
        <!-- ── Kartu Info Sparepart ─────────────────────────────── -->
        <VCol cols="12" md="5">
          <VCard class="mb-6 h-100">
            <VCardItem class="pb-2">
              <template #prepend>
                <VAvatar
                  color="primary"
                  variant="tonal"
                  rounded
                  size="48"
                >
                  <VIcon icon="ri-tools-line" size="24" />
                </VAvatar>
              </template>
              <VCardTitle class="text-h6">{{ sparepart.nama_sparepart }}</VCardTitle>
              <VCardSubtitle>{{ sparepart.spec || "—" }}</VCardSubtitle>
            </VCardItem>

            <VDivider />

            <VCardText>
              <VList density="compact" lines="two">
                <VListItem>
                  <template #prepend>
                    <VIcon icon="ri-map-pin-line" color="primary" class="me-2" />
                  </template>
                  <VListItemTitle class="text-caption text-medium-emphasis">Lokasi</VListItemTitle>
                  <VListItemSubtitle class="font-weight-medium">{{ sparepart.loc || "—" }}</VListItemSubtitle>
                </VListItem>

                <VListItem>
                  <template #prepend>
                    <VIcon icon="ri-price-tag-3-line" color="primary" class="me-2" />
                  </template>
                  <VListItemTitle class="text-caption text-medium-emphasis">Kategori</VListItemTitle>
                  <VListItemSubtitle>
                    <VChip
                      :color="categoryColor(sparepart.category)"
                      size="small"
                      label
                    >
                      {{ sparepart.category || "—" }}
                    </VChip>
                  </VListItemSubtitle>
                </VListItem>

                <VListItem>
                  <template #prepend>
                    <VIcon icon="ri-file-text-line" color="primary" class="me-2" />
                  </template>
                  <VListItemTitle class="text-caption text-medium-emphasis">Remark</VListItemTitle>
                  <VListItemSubtitle>
                    <VChip
                      :color="remarkColor(sparepart.remark)"
                      size="small"
                      label
                    >
                      {{ sparepart.remark || "—" }}
                    </VChip>
                  </VListItemSubtitle>
                </VListItem>
              </VList>
            </VCardText>

            <VDivider />

            <!-- Stok ringkasan -->
            <VCardText>
              <h6 class="text-subtitle-2 text-medium-emphasis mb-3">Ringkasan Stok</h6>
              <VRow dense>
                <VCol cols="6">
                  <VCard variant="tonal" color="primary" rounded="lg" class="pa-3 text-center">
                    <div class="text-h5 font-weight-bold">{{ sparepart.stok ?? 0 }}</div>
                    <div class="text-caption">Stok Awal</div>
                  </VCard>
                </VCol>
                <VCol cols="6">
                  <VCard variant="tonal" color="success" rounded="lg" class="pa-3 text-center">
                    <div class="text-h5 font-weight-bold">{{ sparepart.incoming ?? 0 }}</div>
                    <div class="text-caption">Incoming</div>
                  </VCard>
                </VCol>
                <VCol cols="6">
                  <VCard variant="tonal" color="warning" rounded="lg" class="pa-3 text-center">
                    <div class="text-h5 font-weight-bold">{{ sparepart.usages_sum_qty ?? 0 }}</div>
                    <div class="text-caption">Total Usage</div>
                  </VCard>
                </VCol>
                <VCol cols="6">
                  <VCard variant="tonal" color="info" rounded="lg" class="pa-3 text-center">
                    <div class="text-h5 font-weight-bold">{{ sparepart.end_month_stock ?? 0 }}</div>
                    <div class="text-caption">Stok Akhir</div>
                  </VCard>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Tabel Activity TMS ──────────────────────────────── -->
        <VCol cols="12" md="7">
          <VCard>
            <VCardItem>
              <template #prepend>
                <VAvatar color="secondary" variant="tonal" rounded size="40">
                  <VIcon icon="ri-calendar-check-line" size="22" />
                </VAvatar>
              </template>
              <VCardTitle>Digunakan di Activity TMS</VCardTitle>
              <VCardSubtitle>
                Total {{ activityUsages.length }} activity menggunakan sparepart ini
              </VCardSubtitle>
            </VCardItem>

            <VDivider />

            <!-- kosong -->
            <VCardText v-if="!activityUsages.length" class="text-center py-10">
              <VIcon icon="ri-inbox-line" size="48" color="secondary" class="mb-3 d-block mx-auto" />
              <p class="text-body-1 text-medium-emphasis">
                Sparepart ini belum pernah digunakan di activity TMS manapun.
              </p>
            </VCardText>

            <VDataTable
              v-else
              :headers="activityHeaders"
              :items="activityUsages"
              :items-per-page="10"
              class="text-no-wrap"
            >
              <!-- Tanggal -->
              <template #item.date="{ item }">
                <span class="font-weight-medium">{{ item.date || "—" }}</span>
              </template>

              <!-- Nama Mesin -->
              <template #item.machine_name="{ item }">
                {{ item.item_machine?.name || "—" }}
              </template>

              <!-- Kode -->
              <template #item.machine_code="{ item }">
                <VChip size="small" label color="default">
                  {{ item.item_machine?.code || "—" }}
                </VChip>
              </template>

              <!-- Lokasi -->
              <template #item.machine_location="{ item }">
                {{ item.item_machine?.location || "—" }}
              </template>

              <!-- Scope of Work -->
              <template #item.scope_of_work="{ item }">
                <VChip
                  size="small"
                  :color="item.item_machine?.scope_of_work === 'safety' ? 'error' : 'primary'"
                  label
                >
                  {{ item.item_machine?.scope_of_work || "—" }}
                </VChip>
              </template>

              <!-- Qty -->
              <template #item.qty="{ item }">
                <VChip size="small" color="warning" label>
                  <VIcon start icon="ri-archive-line" size="14" />
                  {{ item.qty }}
                </VChip>
              </template>

              <!-- Aksi -->
              <template #item.actions="{ item }">
                <VTooltip text="Lihat Detail Activity" location="top">
                  <template #activator="{ props }">
                    <IconBtn
                      v-bind="props"
                      size="small"
                      color="primary"
                      @click="goToActivity(item.id)"
                    >
                      <VIcon icon="ri-eye-line" />
                    </IconBtn>
                  </template>
                </VTooltip>
              </template>
            </VDataTable>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <!-- Sparepart tidak ditemukan -->
    <VCard v-else class="text-center py-12">
      <VIcon icon="ri-error-warning-line" size="64" color="error" class="mb-4 d-block mx-auto" />
      <p class="text-h6">Sparepart tidak ditemukan</p>
      <VBtn color="primary" class="mt-4" @click="goBack">Kembali</VBtn>
    </VCard>
  </section>
</template>

<style scoped lang="scss">
section {
  padding: 0;
}
</style>
