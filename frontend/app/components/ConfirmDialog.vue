<script setup lang="ts">
const props = withDefaults(defineProps<{
  open: boolean
  eyebrow?: string
  title: string
  message: string
  confirmLabel?: string
  cancelLabel?: string
  loading?: boolean
  icon?: string
  loadingLabel?: string
}>(), {
  eyebrow: 'PLEASE CONFIRM',
  confirmLabel: 'Confirm',
  cancelLabel: 'Cancel',
  loading: false,
  icon: 'bi-trash3',
  loadingLabel: 'Deleting...',
})

const emit = defineEmits<{ confirm: []; cancel: [] }>()
const cancel = () => { if (!props.loading) emit('cancel') }
const onKeydown = (event: KeyboardEvent) => { if (props.open && event.key === 'Escape') cancel() }

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition name="confirm-pop">
      <div v-if="open" class="confirm-overlay" @click.self="cancel">
        <div class="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirm-dialog-title">
          <button class="confirm-close" type="button" aria-label="Close" :disabled="loading" @click="cancel"><i class="bi bi-x-lg"></i></button>
          <span class="confirm-icon"><i class="bi" :class="icon"></i></span>
          <div><small>{{ eyebrow }}</small><h3 id="confirm-dialog-title">{{ title }}</h3><p>{{ message }}</p></div>
          <div class="confirm-actions">
            <button type="button" :disabled="loading" @click="cancel">{{ cancelLabel }}</button>
            <button class="danger" type="button" :disabled="loading" @click="emit('confirm')"><span v-if="loading" class="spinner-border spinner-border-sm"></span>{{ loading ? loadingLabel : confirmLabel }}</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.confirm-overlay{position:fixed;z-index:10050;inset:0;display:grid;place-items:center;background:rgba(13,26,20,.48);padding:20px;backdrop-filter:blur(3px)}.confirm-dialog{position:relative;display:grid;width:min(460px,100%);grid-template-columns:52px 1fr;gap:18px;border-radius:14px;background:#fff;padding:26px;box-shadow:0 24px 70px rgba(9,21,15,.24)}.confirm-close{position:absolute;top:14px;right:14px;border:0;background:transparent;color:#84908a}.confirm-close:disabled{opacity:.5}.confirm-icon{display:grid;width:52px;height:52px;border-radius:50%;place-items:center;background:#fff0ed;color:#e64e3b;font-size:1.2rem}.confirm-dialog small{color:#e64e3b;font-size:.62rem;font-weight:850;letter-spacing:.11em}.confirm-dialog h3{margin:5px 28px 7px 0;color:#17251f;font-size:1.12rem}.confirm-dialog p{margin:0;color:#748079;font-size:.76rem;line-height:1.55}.confirm-actions{display:flex;grid-column:1/-1;justify-content:flex-end;gap:10px;margin-top:4px}.confirm-actions button{border:1px solid #dce2de;border-radius:6px;background:#fff;padding:10px 15px;color:#34413b;font-size:.7rem;font-weight:750}.confirm-actions .danger{border-color:#e64e3b;background:#e64e3b;color:#fff}.confirm-actions button:disabled{cursor:not-allowed;opacity:.65}.confirm-pop-enter-active,.confirm-pop-leave-active{transition:.22s ease}.confirm-pop-enter-from,.confirm-pop-leave-to{opacity:0}.confirm-pop-enter-from .confirm-dialog,.confirm-pop-leave-to .confirm-dialog{transform:translateY(10px) scale(.97)}
@media(max-width:575px){.confirm-dialog{grid-template-columns:42px 1fr;padding:21px}}
</style>
