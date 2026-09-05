<script setup lang="ts">
useSeoMeta({ robots: 'noindex, nofollow' })
const route = useRoute()
const { register, isAuthenticated } = useAuth()
const form = reactive({ name: '', email: '', password: '', password_confirmation: '', terms: false })
const loading = ref(false)
const showPassword = ref(false)
const message = ref('')
const errors = ref<Record<string, string[]>>({})
const fieldError = (field: string) => errors.value[field]?.[0] ?? ''
const redirect = computed(() => typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') && !route.query.redirect.startsWith('//') ? route.query.redirect : '/account')

const submit = async () => {
  errors.value = {}
  message.value = ''
  if (!form.terms) return void (message.value = 'Please accept the Terms & Conditions to continue.')
  if (form.password !== form.password_confirmation) return void (errors.value = { password_confirmation: ['The passwords do not match.'] })
  loading.value = true
  try {
    const response = await register({ name: form.name.trim(), email: form.email.trim(), password: form.password, password_confirmation: form.password_confirmation })
    await navigateTo({ path: '/verify-email', query: { email: response.email, redirect: redirect.value } })
  } catch (error: any) {
    errors.value = error?.data?.errors ?? {}
    message.value = error?.data?.message ?? error?.message ?? 'We could not create your account.'
  } finally { loading.value = false }
}

onMounted(() => { if (isAuthenticated.value) navigateTo(redirect.value) })
</script>

<template>
  <main class="account-main"><div class="account-shell register-shell">
    <aside class="account-visual register-visual"><div class="account-visual-content">
      <NuxtLink to="/" class="account-mark">N<span>C</span></NuxtLink><small>Join NovaCart today</small>
      <h1>Your next great find starts here.</h1><p>Create a secure account to shop, track orders and check out faster.</p>
      <div class="account-benefit-list"><span><i class="bi bi-envelope-check"></i><b>Verified membership</b><small>We verify every new email address</small></span><span><i class="bi bi-box-seam"></i><b>Order tracking</b><small>Follow purchases from one place</small></span><span><i class="bi bi-shield-check"></i><b>Secure checkout</b><small>Your account stays protected</small></span></div>
    </div><div class="account-visual-footer"><span><i class="bi bi-shield-check"></i> Secure &amp; private</span><span>Trusted shopping</span></div></aside>
    <section class="account-form-panel"><div class="account-form-wrap register-wrap">
      <NuxtLink class="account-mobile-logo" to="/">NOVA<span>CART</span></NuxtLink>
      <div class="account-title"><small>Start shopping smarter</small><h2>Create your account</h2><p>We will send a verification link to your email.</p></div>
      <div v-if="message" class="form-alert"><i class="bi bi-exclamation-circle"></i>{{ message }}</div>
      <form class="account-form" novalidate @submit.prevent="submit">
        <label class="account-field" :class="{ invalid: fieldError('name') }"><span>Full name <small>({{ form.name.length }}/20)</small></span><div><i class="bi bi-person"></i><input v-model="form.name" type="text" autocomplete="name" placeholder="Enter your full name" minlength="2" maxlength="20"></div><small v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</small></label>
        <label class="account-field" :class="{ invalid: fieldError('email') }"><span>Email address</span><div><i class="bi bi-envelope"></i><input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com"></div><small v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</small></label>
        <label class="account-field" :class="{ invalid: fieldError('password') }"><span>Password <small>(minimum 8 characters)</small></span><div><i class="bi bi-lock"></i><input v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Create a strong password"><button type="button" data-password-toggle @click="showPassword = !showPassword"><i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i></button></div><small v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</small></label>
        <label class="account-field" :class="{ invalid: fieldError('password_confirmation') }"><span>Confirm password</span><div><i class="bi bi-shield-lock"></i><input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Repeat your password"></div><small v-if="fieldError('password_confirmation')" class="field-error">{{ fieldError('password_confirmation') }}</small></label>
        <label class="account-terms"><input v-model="form.terms" type="checkbox"><span>I agree to the <NuxtLink to="/terms">Terms</NuxtLink> and <NuxtLink to="/privacy">Privacy Policy</NuxtLink>.</span></label>
        <button class="account-submit" :disabled="loading"><span>{{ loading ? 'Creating account...' : 'Create account' }}</span><i class="bi bi-arrow-right"></i></button>
      </form>
      <p class="account-switch">Already verified? <NuxtLink :to="{ path: '/login', query: { redirect } }">Sign in</NuxtLink></p>
    </div></section>
  </div></main>
</template>

<style scoped>
.register-shell{min-height:720px}.form-alert{display:flex;gap:8px;margin-bottom:16px;padding:11px 13px;border-left:3px solid #dc4c3f;background:#fff1ef;color:#a6382f;font-size:.72rem}.invalid>div{border-color:#dc4c3f}.field-error{color:#c94337;font-size:.64rem}.account-submit:disabled{opacity:.65;cursor:wait}
</style>
