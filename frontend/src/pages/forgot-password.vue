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
import { useRouter } from "vue-router";

definePage({
  meta: {
    layout: "blank",
    public: true,
  },
});

const router = useRouter();
const email = ref("");
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

const handleForgotPassword = async () => {
  if (!email.value) {
    errorMessage.value = "Email tidak boleh kosong.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";

  try {
    await axios.post(ENDPOINTS.forgotPassword, { email: email.value });
    isSuccess.value = true;
  } catch (error) {
    const msg = error.response?.data?.message;
    errorMessage.value = msg || "Gagal mengirim link reset password. Periksa email Anda.";
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
          <h4 class="text-h4 mb-1">Lupa Password? 🔑</h4>
          <p class="mb-0">
            Masukkan email akun Anda dan kami akan mengirimkan link untuk mengatur ulang password.
          </p>
        </VCardText>

        <VCardText v-if="isSuccess">
          <VAlert type="success" variant="tonal" class="mb-4">
            Link reset password telah dikirim ke <strong>{{ email }}</strong>. Silakan cek inbox email Anda.
          </VAlert>
          <VBtn block variant="tonal" @click="router.push('/login')">
            Kembali ke Login
          </VBtn>
        </VCardText>

        <VCardText v-else>
          <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable @click:close="errorMessage = ''">
            {{ errorMessage }}
          </VAlert>

          <VForm @submit.prevent="handleForgotPassword">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="email"
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                  autofocus
                />
              </VCol>

              <VCol cols="12">
                <VBtn block type="submit" :loading="isLoading">
                  Kirim Link Reset Password
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
