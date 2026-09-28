<script setup lang="ts">
useSeoMeta({ title: 'Forgot password', robots: 'noindex, nofollow' })

const { forgotPassword, isAuthenticated } = useAuth()
const email = ref('')
const loading = ref(false)
const message = ref('')
const errorMessage = ref('')

const submit = async () => {
  loading.value = true
  message.value = ''
  errorMessage.value = ''

  try {
    const response = await forgotPassword(email.value.trim())
    message.value = response.message
  } catch (error: any) {
    errorMessage.value = error?.data?.errors?.email?.[0] ?? error?.data?.message ?? 'We could not send the reset link.'
  } finally {
    loading.value = false
  }
}

onMounted(() => { if (isAuthenticated.value) navigateTo('/account') })
</script>

<template>
  <main class="account-main"><div class="account-shell">
    <aside class="account-visual"><div class="account-visual-content">
      <NuxtLink to="/" class="account-mark">N<span>C</span></NuxtLink><small>Account security</small>
      <h1>Reset your password securely.</h1><p>We will email you a link to create a new password for your account.</p>
      <div class="account-benefit-list"><span><i class="bi bi-envelope-check"></i><b>Secure email link</b><small>Only you can access your reset link</small></span><span><i class="bi bi-clock-history"></i><b>Time limited</b><small>The link expires after one hour</small></span></div>
    </div><div class="account-visual-footer"><span><i class="bi bi-shield-check"></i> Secure &amp; private</span></div></aside>
    <section class="account-form-panel"><div class="account-form-wrap">
      <NuxtLink class="account-mobile-logo" to="/">NOVA<span>CART</span></NuxtLink>
      <div class="account-title"><small>Password help</small><h2>Forgot your password?</h2><p>Enter your email and we will send a reset link.</p></div>
      <div v-if="message" class="reset-alert success" role="status">{{ message }}</div>
      <div v-if="errorMessage" class="reset-alert" role="alert">{{ errorMessage }}</div>
      <form v-if="!message" class="account-form" @submit.prevent="submit">
        <label class="account-field"><span>Email address</span><div><i class="bi bi-envelope"></i><input v-model="email" type="email" autocomplete="email" placeholder="you@example.com" required></div></label>
        <button class="account-submit" :disabled="loading"><span>{{ loading ? 'Sending link...' : 'Send reset link' }}</span><i class="bi bi-arrow-right"></i></button>
      </form>
      <p class="account-switch"><NuxtLink to="/login"><i class="bi bi-arrow-left"></i> Back to sign in</NuxtLink></p>
    </div></section>
  </div></main>
</template>

<style scoped>
.reset-alert{margin-bottom:16px;padding:11px 13px;border-left:3px solid #dc4c3f;background:#fff1ef;color:#a6382f;font-size:.72rem;line-height:1.5}.reset-alert.success{border-color:#3d9664;background:#edf8f1;color:#287349}.account-submit:disabled{opacity:.65;cursor:wait}
</style>
