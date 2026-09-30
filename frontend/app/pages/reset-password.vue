<script setup lang="ts">
useSeoMeta({ title: 'Reset password', robots: 'noindex, nofollow' })

const route = useRoute()
const { resetPassword, isAuthenticated } = useAuth()
const { data: catalogData } = useCatalogMenu()
const site = computed(() => catalogData.value?.site ?? {})
const siteName = computed(() => site.value.name || 'Store')
const siteInitials = computed(() => siteName.value.split(/\s+/).map(word => word[0]).join('').slice(0, 2).toUpperCase())
const email = computed(() => typeof route.query.email === 'string' ? route.query.email : '')
const token = computed(() => typeof route.query.token === 'string' ? route.query.token : '')
const form = reactive({ password: '', password_confirmation: '' })
const loading = ref(false)
const showPassword = ref(false)
const message = ref('')
const errors = ref<Record<string, string[]>>({})
const linkIsValid = computed(() => Boolean(email.value && token.value))

const submit = async () => {
  if (!linkIsValid.value) return
  errors.value = {}
  message.value = ''
  if (form.password !== form.password_confirmation) {
    errors.value = { password_confirmation: ['The passwords do not match.'] }
    return
  }
  loading.value = true
  try {
    await resetPassword({ email: email.value, token: token.value, ...form })
    await navigateTo('/login?reset=1')
  } catch (error: any) {
    errors.value = error?.data?.errors ?? {}
    message.value = errors.value.email?.[0] ?? error?.data?.message ?? 'This reset link is invalid or has expired.'
  } finally {
    loading.value = false
  }
}

onMounted(() => { if (isAuthenticated.value) navigateTo('/account') })
</script>

<template>
  <main class="account-main"><div class="account-shell">
    <aside class="account-visual"><div class="account-visual-content">
      <NuxtLink to="/" class="account-mark">{{ siteInitials }}</NuxtLink><small>Account security</small>
      <h1>Create a new password.</h1><p>Choose a strong password that you do not use on other websites.</p>
      <div class="account-benefit-list"><span><i class="bi bi-shield-lock"></i><b>Your account stays protected</b><small>Use at least eight characters</small></span></div>
    </div><div class="account-visual-footer"><span><i class="bi bi-shield-check"></i> Secure &amp; private</span></div></aside>
    <section class="account-form-panel"><div class="account-form-wrap">
      <NuxtLink class="account-mobile-logo" to="/"><img v-if="site.logo" :src="site.logo" :alt="siteName"><span v-else>{{ siteName }}</span></NuxtLink>
      <div class="account-title"><small>Password reset</small><h2>Set a new password</h2><p v-if="linkIsValid">Create a password for {{ email }}.</p><p v-else>Your reset link is incomplete or invalid.</p></div>
      <div v-if="message" class="reset-alert" role="alert">{{ message }}</div>
      <form v-if="linkIsValid" class="account-form" @submit.prevent="submit">
        <label class="account-field" :class="{ invalid: errors.password }"><span>New password</span><div><i class="bi bi-lock"></i><input v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="At least 8 characters" minlength="8" required><button type="button" data-password-toggle @click="showPassword = !showPassword"><i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i></button></div><small v-if="errors.password" class="field-error">{{ errors.password[0] }}</small></label>
        <label class="account-field" :class="{ invalid: errors.password_confirmation }"><span>Confirm new password</span><div><i class="bi bi-shield-lock"></i><input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Repeat your password" minlength="8" required></div><small v-if="errors.password_confirmation" class="field-error">{{ errors.password_confirmation[0] }}</small></label>
        <button class="account-submit" :disabled="loading"><span>{{ loading ? 'Saving password...' : 'Save new password' }}</span><i class="bi bi-arrow-right"></i></button>
      </form>
      <p class="account-switch"><NuxtLink to="/login"><i class="bi bi-arrow-left"></i> Back to sign in</NuxtLink></p>
    </div></section>
  </div></main>
</template>

<style scoped>
.reset-alert{margin-bottom:16px;padding:11px 13px;border-left:3px solid #dc4c3f;background:#fff1ef;color:#a6382f;font-size:.72rem;line-height:1.5}.invalid>div{border-color:#dc4c3f}.field-error{color:#c94337;font-size:.64rem}.account-submit:disabled{opacity:.65;cursor:wait}
</style>
