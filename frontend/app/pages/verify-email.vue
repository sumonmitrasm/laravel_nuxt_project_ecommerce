<script setup lang="ts">
useSeoMeta({ robots: 'noindex, nofollow' })
const route = useRoute()
const { resendVerification } = useAuth()
const email = computed(() => typeof route.query.email === 'string' ? route.query.email : '')
const loading = ref(false)
const message = ref('')
const resend = async () => {
  if (!email.value) return
  loading.value = true
  try { message.value = (await resendVerification(email.value)).message }
  catch (error: any) { message.value = error?.data?.message ?? 'Please wait before requesting another email.' }
  finally { loading.value = false }
}
</script>

<template><main class="verify-page"><section class="verify-card"><div class="verify-icon"><i class="bi bi-envelope-check"></i></div><small>ONE MORE STEP</small><h1>Check your email</h1><p>We sent a verification link to</p><strong>{{ email || 'your email address' }}</strong><p class="hint">Open the email and click the link. After verification, you can sign in securely.</p><div v-if="message" class="verify-message">{{ message }}</div><button :disabled="loading || !email" @click="resend">{{ loading ? 'Sending...' : 'Resend verification email' }}</button><NuxtLink :to="{path:'/login',query:route.query.redirect?{redirect:route.query.redirect}:{}}">Back to sign in</NuxtLink><span class="security"><i class="bi bi-shield-check"></i> Never share your verification link.</span></section></main></template>

<style scoped>.verify-page{min-height:calc(100vh - 70px);display:grid;place-items:center;padding:30px;background:#f7f8f7}.verify-card{width:min(480px,100%);padding:52px 45px;background:#fff;box-shadow:0 22px 65px rgba(23,33,29,.1);text-align:center}.verify-icon{width:72px;height:72px;display:grid;place-items:center;margin:0 auto 22px;border-radius:50%;background:#fff0ec;color:#ff5a45;font-size:1.8rem}.verify-card>small{color:#ff5a45;font-weight:800;letter-spacing:.15em}.verify-card h1{margin:8px 0 16px}.verify-card p{margin:0;color:#7e8883;font-size:.78rem}.verify-card strong{display:block;margin:7px 0 20px}.verify-card .hint{line-height:1.7}.verify-message{margin:18px 0 0;padding:10px;background:#edf8f1;color:#287349;font-size:.7rem}.verify-card button{width:100%;height:48px;margin:22px 0 15px;border:0;background:#ff5a45;color:#fff;font-weight:700}.verify-card>a{display:block;color:#222;font-size:.72rem}.security{display:block;margin-top:25px;color:#9aa39e;font-size:.62rem}@media(max-width:480px){.verify-card{padding:38px 22px}}</style>
