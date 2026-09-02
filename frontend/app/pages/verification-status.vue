<script setup lang="ts">
const route = useRoute()
const isInvalid = computed(() => route.query.state === 'invalid')

useSeoMeta({
  title: 'Email Verification | NovaCart',
  robots: 'noindex, nofollow',
})
</script>

<template>
  <main class="status-page">
    <section class="status-card">
      <NuxtLink to="/" class="brand">NOVA<span>CART</span></NuxtLink>
      <div class="status-icon"><i class="bi bi-link-45deg"></i></div>
      <small>EMAIL VERIFICATION</small>
      <h1>{{ isInvalid ? 'This verification link is no longer valid' : 'Unable to verify this email' }}</h1>
      <p>The link may have expired, already been used, or been copied incorrectly. If your account is already active, you can sign in normally.</p>

      <div class="status-note">
        <i class="bi bi-shield-check"></i>
        <span>Your account security has not been affected.</span>
      </div>

      <NuxtLink to="/login" class="primary-action">Continue to sign in <i class="bi bi-arrow-right"></i></NuxtLink>
      <NuxtLink to="/verify-email" class="secondary-action">Request another verification email</NuxtLink>
    </section>
  </main>
</template>

<style scoped>
.status-page{min-height:calc(100vh - 70px);display:grid;place-items:center;padding:32px 16px;background:linear-gradient(145deg,#f5f7f6,#fff)}
.status-card{width:min(520px,100%);padding:48px 46px;background:#fff;box-shadow:0 24px 70px rgba(23,33,29,.11);text-align:center}
.brand{display:inline-block;margin-bottom:30px;color:#111815;font-size:22px;font-weight:900;text-decoration:none}.brand span{color:#ff5a45}
.status-icon{width:76px;height:76px;display:grid;place-items:center;margin:0 auto 22px;border-radius:50%;background:#fff0ec;color:#ff5a45;font-size:32px}
.status-card>small{color:#ff5a45;font-size:10px;font-weight:800;letter-spacing:.16em}.status-card h1{margin:10px 0 15px;font-size:28px;line-height:1.2;letter-spacing:-.04em}.status-card>p{margin:0;color:#748079;font-size:13px;line-height:1.75}
.status-note{display:flex;align-items:center;justify-content:center;gap:8px;margin:25px 0;padding:12px;background:#f2f8f4;color:#3c7658;font-size:12px}.primary-action{height:50px;padding:0 18px;display:flex;align-items:center;justify-content:space-between;background:#ff5a45;color:#fff;font-size:12px;font-weight:700;text-decoration:none;text-transform:uppercase}.secondary-action{display:block;margin-top:18px;color:#4d5953;font-size:12px}
@media(max-width:480px){.status-card{padding:38px 22px}.status-card h1{font-size:24px}}
</style>
