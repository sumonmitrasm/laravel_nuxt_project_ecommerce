<script setup lang="ts">
const route = useRoute()
const { login, isAuthenticated } = useAuth()
const form = reactive({ email: '', password: '', remember: false })
const loading = ref(false)
const showPassword = ref(false)
const message = ref(route.query.verified === '1' ? 'Email verified successfully. You can now sign in.' : '')
const success = ref(route.query.verified === '1')
const errors = ref<Record<string, string[]>>({})
const redirect = computed(() => typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') && !route.query.redirect.startsWith('//') ? route.query.redirect : '/account')

const submit = async () => {
  loading.value = true
  errors.value = {}
  message.value = ''
  success.value = false

  try {
    await login(form)
    await navigateTo(redirect.value)
  } catch (error: any) {
    errors.value = error?.data?.errors ?? {}
    message.value = errors.value.email?.[0]
      ?? error?.data?.message
      ?? error?.message
      ?? 'Unable to sign in.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (isAuthenticated.value) navigateTo(redirect.value)
})
</script>

<template><main class="account-main"><div class="account-shell">
  <aside class="account-visual"><div class="account-visual-content"><NuxtLink to="/" class="account-mark">N<span>C</span></NuxtLink><small>Your shopping, simplified</small><h1>Welcome back to NovaCart.</h1><p>Sign in to track orders, save favourites and continue securely.</p><div class="account-benefit-list"><span><i class="bi bi-box-seam"></i><b>Track every order</b><small>Updates from checkout to delivery</small></span><span><i class="bi bi-heart"></i><b>Save favourites</b><small>Keep products ready for later</small></span><span><i class="bi bi-lightning-charge"></i><b>Checkout faster</b><small>Continue where you left off</small></span></div></div><div class="account-visual-footer"><span><i class="bi bi-shield-check"></i> Secure &amp; private</span></div></aside>
  <section class="account-form-panel"><div class="account-form-wrap"><NuxtLink class="account-mobile-logo" to="/">NOVA<span>CART</span></NuxtLink><div class="account-title"><small>Welcome back</small><h2>Sign in to your account</h2><p>Only verified accounts can sign in.</p></div>
    <div v-if="message" class="login-alert" :class="{ success }" role="alert">{{ message }}</div>
    <form class="account-form" @submit.prevent="submit">
      <label class="account-field" :class="{ invalid: errors.email }"><span>Email address</span><div><i class="bi bi-envelope"></i><input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com" required></div></label>
      <label class="account-field"><span>Password</span><div><i class="bi bi-lock"></i><input v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="Enter your password" required><button type="button" data-password-toggle @click="showPassword = !showPassword"><i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i></button></div></label>
      <div class="account-form-row"><label><input v-model="form.remember" type="checkbox"> Remember me</label><NuxtLink to="/forgot-password">Forgot password?</NuxtLink></div>
      <button class="account-submit" :disabled="loading"><span>{{ loading ? 'Signing in...' : 'Sign in' }}</span><i class="bi bi-arrow-right"></i></button>
    </form><p class="account-switch">Don’t have an account? <NuxtLink :to="{ path: '/register', query: { redirect } }">Create one</NuxtLink></p>
  </div></section>
</div></main></template>

<style scoped>.login-alert{margin-bottom:16px;padding:11px 13px;border-left:3px solid #dc4c3f;background:#fff1ef;color:#a6382f;font-size:.72rem;line-height:1.5}.login-alert.success{border-color:#3d9664;background:#edf8f1;color:#287349}.invalid>div{border-color:#dc4c3f}.account-submit:disabled{opacity:.65;cursor:wait}</style>
