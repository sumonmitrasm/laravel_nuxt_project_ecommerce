<script setup lang="ts">
const { toast, dismissToast } = useToast()
let timer: ReturnType<typeof setTimeout> | null = null

const icon = computed(() => ({ success: 'bi-check-lg', error: 'bi-exclamation-lg', info: 'bi-info-lg' }[toast.value?.tone ?? 'info']))

watch(() => toast.value?.id, () => {
  if (timer) clearTimeout(timer)
  const current = toast.value
  if (current && current.duration > 0) timer = setTimeout(() => dismissToast(current.id), current.duration)
})

onBeforeUnmount(() => { if (timer) clearTimeout(timer) })
</script>

<template>
  <Teleport to="body">
    <Transition name="app-toast">
      <div v-if="toast" class="app-toast" :class="`is-${toast.tone}`" role="status" aria-live="polite">
        <span class="app-toast__icon"><i class="bi" :class="icon"></i></span>
        <div class="app-toast__copy"><strong>{{ toast.title }}</strong><small>{{ toast.message }}</small></div>
        <button type="button" aria-label="Close notification" @click="dismissToast(toast.id)"><i class="bi bi-x"></i></button>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.app-toast{position:fixed;z-index:10060;top:22px;right:22px;display:flex;width:min(370px,calc(100vw - 32px));align-items:center;gap:12px;border:1px solid #cfe8d8;border-radius:10px;background:#fff;padding:14px 15px;box-shadow:0 15px 45px rgba(15,34,24,.16)}
.app-toast__icon{display:grid;width:36px;height:36px;flex:0 0 36px;border-radius:50%;place-items:center;background:#e7f7ed;color:#24804a}.app-toast__copy{display:grid;gap:2px}.app-toast strong{color:#17251f;font-size:.78rem}.app-toast small{color:#748079;font-size:.68rem;line-height:1.4}.app-toast button{margin-left:auto;border:0;background:transparent;color:#8a948f;font-size:1.05rem}.app-toast.is-error{border-color:#f0cbc7}.app-toast.is-error .app-toast__icon{background:#fff0ed;color:#d94b3d}.app-toast.is-info{border-color:#cbdde9}.app-toast.is-info .app-toast__icon{background:#edf6fc;color:#347aa5}
.app-toast-enter-active,.app-toast-leave-active{transition:.22s ease}.app-toast-enter-from,.app-toast-leave-to{opacity:0;transform:translateX(25px)}
@media(max-width:575px){.app-toast{top:14px;right:16px}}
</style>
