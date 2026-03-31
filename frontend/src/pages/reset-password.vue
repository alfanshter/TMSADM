<script setup>
import { useGenerateImageVariant } from "@/@core/composable/useGenerateImageVariant";
import { ENDPOINTS } from "@/config/api";
import authV2LoginIllustrationBorderedDark from "@images/pages/auth-v2-login-illustration-bordered-dark.png";
import authV2LoginIllustrationBorderedLight from "@images/pages/auth-v2-login-illustration-bordered-light.png";
import authV2LoginIllustrationDark from "@images/pages/auth-v2-login-illustration-dark.png";
import authV2LoginIllustrationLight from "@images/pages/auth-v2-login-illustration-light.png";
import authV2LoginMaskDark from "@images/pages/auth-v2-login-mask-dark.png";
import authV2LoginMaskLight from "@images/pages/auth-v2-login-mask-light.png";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import { themeConfig } from "@themeConfig";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";

definePage({
  meta: {
    layout: "blank",
    public: true,
  },
});

const route = useRoute();
const router = useRouter();

const token = computed(() => route.query.token || "");
const emailFromQuery = computed(() => route.query.email || "");

const form = ref({
  password: "",
  password_confirmation: "",
});
const isPasswordVisible = ref(false);
const isConfirmPasswordVisible = ref(false);
const isLoading = ref(false);
const isSuccess = ref(false);
const errorMessage = ref("");

const authV2LoginMask = useGenerateImageVariant(authV2LoginMaskLight, authV2LoginMaskDark);
const authV2LoginIllustration = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true
);

onMounted(() => {
  if (!token.value || !emailFromQuery.value) {
    errorMessage.value = "Link reset password tidak valid atau sudah kadaluarsa.";
  }
});

const handleResetPassword = async () => {
  if (!form.value.password || !form.value.password_confirmation) {
    errorMessage.value = "Password tidak boleh kosong.";
    return;
  }
  if (form.value.password.length < 8) {
    errorMessage.value = "Password minimal 8 karakter.";
    return;
  }
  if (form.value.password !== form.value.password_confirmation) {
    errorMessage.value = "Konfirmasi password tidak cocok.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";

  try {
    await axios.post(ENDPOINTS.resetPassword, {
      token: token.value,
      email: emailFromQuery.value,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    });
    isSuccess.value = true;
  } catch (error) {
    const msg = error.response?.data?.message;
    errorMessage.value = msg || "Token tidak valid atau sudah kadaluarsa. Silakan minta link baru.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <a href="javascript:void(0)">
    <div class="app-logo auth-logo">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="app-logo-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </a>

  <VRow no-gutters class="auth-wrapper">
    <VCol md="8" class="d-none d-md-flex align-center justify-center position-relative">
      <div class="d-flex align-center justify-center pa-10">
        <img :src="authV2LoginIllustration" class="auth-illustration w-100" alt="auth-illustration" />
      </div>
      <VImg :src="authV2LoginMask" class="d-none d-md-flex auth-footer-mask" alt="auth-mask" />
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
      style="background-color: rgb(var(--v-theme-surface))"
    >
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-5 pa-lg-7">
        <VCardText>
          <h4 class="text-h4 mb-1">Atur Password Baru 🔒</h4>
          <p class="mb-0">
            Password baru Anda harus berbeda dari password yang pernah digunakan sebelumnya.
          </p>
        </VCardText>

        <VCardText v-if="isSuccess">
          <VAlert type="success" variant="tonal" class="mb-4">
            Password Anda berhasil diubah! Silakan login dengan password baru Anda.
          </VAlert>
          <VBtn block @click="router.push('/login')">
            Masuk Sekarang
          </VBtn>
        </VCardText>

        <VCardText v-else>
          <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable @click:close="errorMessage = ''">
            {{ errorMessage }}
          </VAlert>

          <VForm @submit.prevent="handleResetPassword">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password Baru"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  autofocus
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.password_confirmation"
                  label="Konfirmasi Password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="!token || !emailFromQuery"
                >
                  Simpan Password Baru
                </VBtn>
              </VCol>

              <VCol cols="12" class="text-center">
                <RouterLink to="/login" class="text-primary d-inline-flex align-center gap-1">
                  <VIcon icon="ri-arrow-left-s-line" />
                  Kembali ke Login
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core/scss/template/pages/page-auth";
</style>
