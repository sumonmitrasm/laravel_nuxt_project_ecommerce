<script setup lang="ts">
import type { AddressPayload, UserAddress } from '~/composables/useAddresses'

const { addresses, addressesLoaded, fetchAddresses, createAddress, updateAddress, removeAddress, makeDefaultAddress } = useAddresses()
const { success: showSuccessToast, error: showErrorToast } = useToast()

const emptyForm = (): AddressPayload => ({
  label: 'Home', recipient_name: '', phone: '', alternative_phone: '', division: '', district: '',
  upazila: '', area: '', postal_code: '', address_line: '', is_default: false,
})

const form = reactive<AddressPayload>(emptyForm())
const editingId = ref<number | null>(null)
const showForm = ref(false)
const saving = ref(false)
const loading = ref(false)
const deletingId = ref<number | null>(null)
const pendingDeleteAddress = ref<UserAddress | null>(null)
const message = ref('')
const errorMessage = ref('')
const errors = ref<Record<string, string[]>>({})

const resetForm = () => {
  Object.assign(form, emptyForm())
  editingId.value = null
  errors.value = {}
  errorMessage.value = ''
}

const openCreate = () => {
  resetForm()
  form.recipient_name = useAuth().user.value?.name ?? ''
  showForm.value = true
}

const openEdit = (address: UserAddress) => {
  Object.assign(form, {
    label: address.label, recipient_name: address.recipient_name, phone: address.phone,
    alternative_phone: address.alternative_phone ?? '', division: address.division,
    district: address.district, upazila: address.upazila, area: address.area ?? '',
    postal_code: address.postal_code ?? '', address_line: address.address_line,
    is_default: address.is_default,
  })
  editingId.value = address.id
  errors.value = {}
  errorMessage.value = ''
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const submit = async () => {
  if (saving.value) return
  saving.value = true
  errors.value = {}
  errorMessage.value = ''
  message.value = ''

  try {
    const payload = { ...form }
    const response = editingId.value
      ? await updateAddress(editingId.value, payload)
      : await createAddress(payload)
    message.value = response.message
    closeForm()
  } catch (error: any) {
    if ((error?.statusCode ?? error?.status) === 422) {
      errors.value = error?.data?.errors ?? {}
      errorMessage.value = 'Please correct the highlighted fields.'
    } else {
      errorMessage.value = error?.data?.message ?? 'The address could not be saved. Please try again.'
    }
  } finally {
    saving.value = false
  }
}

const requestRemove = (address: UserAddress) => {
  pendingDeleteAddress.value = address
}

const remove = async () => {
  const address = pendingDeleteAddress.value
  if (!address) return
  deletingId.value = address.id
  message.value = ''
  errorMessage.value = ''
  try {
    const response = await removeAddress(address.id)
    pendingDeleteAddress.value = null
    showSuccessToast('Address removed', response.message)
  } catch (error: any) {
    errorMessage.value = error?.data?.message ?? 'The address could not be removed.'
    showErrorToast('Address not removed', errorMessage.value)
  } finally { deletingId.value = null }
}

const setDefault = async (address: UserAddress) => {
  if (address.is_default) return
  try {
    const response = await makeDefaultAddress(address.id)
    message.value = response.message
  } catch (error: any) {
    errorMessage.value = error?.data?.message ?? 'The default address could not be changed.'
  }
}


onMounted(async () => {
  loading.value = true
  try { await fetchAddresses() }
  catch (error: any) { errorMessage.value = error?.data?.message ?? 'Saved addresses could not be loaded.' }
  finally { loading.value = false }
})
</script>

<template>
  <div class="address-manager">
    <div class="address-toolbar">
      <div><small>Delivery details</small><h2>Saved addresses</h2><p>Add, edit and select your delivery locations.</p></div>
      <button class="primary" type="button" @click="openCreate"><i class="bi bi-plus-lg"></i> Add address</button>
    </div>

    <p v-if="message" class="notice success"><i class="bi bi-check-circle-fill"></i> {{ message }}</p>
    <p v-if="errorMessage && !showForm" class="notice error"><i class="bi bi-exclamation-circle-fill"></i> {{ errorMessage }}</p>
    <div v-if="loading && !addressesLoaded" class="address-loading"><span class="spinner-border spinner-border-sm"></span> Loading addresses...</div>

    <form v-if="showForm" class="address-form" @submit.prevent="submit">
      <div class="form-title"><div><small>{{ editingId ? 'Update location' : 'New location' }}</small><h3>{{ editingId ? 'Edit address' : 'Add delivery address' }}</h3></div><button type="button" aria-label="Close" @click="closeForm"><i class="bi bi-x-lg"></i></button></div>
      <p v-if="errorMessage" class="notice error">{{ errorMessage }}</p>
      <div class="fields">
        <label><span>Address label</span><input v-model.trim="form.label" maxlength="30" placeholder="Home or Office" required><em v-if="errors.label">{{ errors.label[0] }}</em></label>
        <label><span>Recipient name</span><input v-model.trim="form.recipient_name" maxlength="100" autocomplete="name" required><em v-if="errors.recipient_name">{{ errors.recipient_name[0] }}</em></label>
        <label><span>Mobile number</span><input v-model.trim="form.phone" type="tel" maxlength="20" placeholder="01XXXXXXXXX" required><em v-if="errors.phone">{{ errors.phone[0] }}</em></label>
        <label><span>Alternative mobile <small>(optional)</small></span><input v-model.trim="form.alternative_phone" type="tel" maxlength="20"><em v-if="errors.alternative_phone">{{ errors.alternative_phone[0] }}</em></label>
        <label><span>Division</span><input v-model.trim="form.division" maxlength="100" required><em v-if="errors.division">{{ errors.division[0] }}</em></label>
        <label><span>District</span><input v-model.trim="form.district" maxlength="100" required><em v-if="errors.district">{{ errors.district[0] }}</em></label>
        <label><span>Upazila / Thana</span><input v-model.trim="form.upazila" maxlength="100" required><em v-if="errors.upazila">{{ errors.upazila[0] }}</em></label>
        <label><span>Area <small>(optional)</small></span><input v-model.trim="form.area" maxlength="150"></label>
        <label><span>Postal code <small>(optional)</small></span><input v-model.trim="form.postal_code" maxlength="20"><em v-if="errors.postal_code">{{ errors.postal_code[0] }}</em></label>
        <label class="full"><span>Full address</span><textarea v-model.trim="form.address_line" maxlength="500" rows="3" placeholder="House, road, block and nearby landmark" required></textarea><em v-if="errors.address_line">{{ errors.address_line[0] }}</em></label>
      </div>
      <label class="default-check"><input v-model="form.is_default" type="checkbox"> Use as my default delivery address</label>
      <div class="form-actions"><button type="button" class="secondary" @click="closeForm">Cancel</button><button type="submit" class="primary" :disabled="saving"><span v-if="saving" class="spinner-border spinner-border-sm"></span>{{ saving ? 'Saving...' : 'Save address' }}</button></div>
    </form>

    <div v-if="!loading && addresses.length === 0 && !showForm" class="address-empty"><i class="bi bi-geo-alt"></i><h3>No saved address yet</h3><p>Add an address now to make checkout faster.</p><button class="primary" type="button" @click="openCreate">Add your first address</button></div>

    <div v-else class="address-grid-real">
      <article v-for="address in addresses" :key="address.id" :class="{ selected: address.is_default }">
        <div class="card-head"><span class="address-icon"><i :class="address.label.toLowerCase().includes('office') ? 'bi bi-building' : 'bi bi-house'"></i></span><span v-if="address.is_default" class="default-badge">Default</span></div>
        <h3>{{ address.label }}</h3><strong>{{ address.recipient_name }}</strong>
        <p>{{ address.address_line }}<br><span v-if="address.area">{{ address.area }}, </span>{{ address.upazila }}, {{ address.district }}<span v-if="address.postal_code"> {{ address.postal_code }}</span><br>{{ address.division }}<br>{{ address.phone }}<span v-if="address.alternative_phone"><br>{{ address.alternative_phone }}</span></p>
        <div class="card-actions"><button type="button" @click="openEdit(address)"><i class="bi bi-pencil"></i> Edit</button><button v-if="!address.is_default" type="button" @click="setDefault(address)">Make default</button><button type="button" class="danger" :disabled="deletingId === address.id" @click="requestRemove(address)">{{ deletingId === address.id ? 'Removing...' : 'Remove' }}</button></div>
      </article>
    </div>
  </div>
  <ConfirmDialog
    :open="Boolean(pendingDeleteAddress)"
    eyebrow="REMOVE SAVED ADDRESS"
    :title="`Delete ${pendingDeleteAddress?.label ?? ''} address?`"
    message="This address will be permanently removed from your saved delivery locations."
    confirm-label="Delete address"
    :loading="deletingId !== null"
    @cancel="pendingDeleteAddress = null"
    @confirm="remove"
  /></template>

<style scoped>
.address-toolbar,.form-title,.form-actions,.card-head,.card-actions{display:flex;align-items:center;justify-content:space-between;gap:16px}.address-toolbar{margin-bottom:24px}.address-toolbar small,.form-title small{color:var(--brand);font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.address-toolbar h2{margin:5px 0;font-size:2rem}.address-toolbar p{margin:0;color:#77817c}.primary{border:0;background:var(--brand);padding:13px 19px;color:#fff;font-size:.72rem;font-weight:800;text-transform:uppercase}.primary:disabled{opacity:.65}.notice{margin:0 0 18px;padding:12px 14px;font-size:.76rem}.notice.success{border-left:3px solid #27804b;background:#edf8f1;color:#21653e}.notice.error{border-left:3px solid #d94b3d;background:#fff0ee;color:#a93226}.address-loading,.address-empty{border:1px dashed #d9dfdb;background:#fff;padding:45px;text-align:center;color:#748079}.address-empty i{font-size:2rem;color:var(--brand)}.address-empty h3{margin:12px 0 5px;color:var(--ink)}.address-empty p{margin:0 0 18px}.address-form{margin-bottom:24px;border:1px solid #dfe5e1;background:#fff;padding:26px}.form-title{margin-bottom:20px}.form-title h3{margin:4px 0 0}.form-title>button{border:0;background:transparent}.fields{display:grid;grid-template-columns:repeat(2,1fr);gap:17px}.fields label,.fields span{display:block}.fields label>span{margin-bottom:7px;font-size:.74rem;font-weight:700}.fields label>span small{color:#929b96;font-weight:400}.fields input,.fields textarea{width:100%;border:1px solid #dce2de;background:#fbfcfb;padding:12px 13px;outline:0}.fields input:focus,.fields textarea:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(255,89,65,.08)}.fields textarea{resize:vertical}.fields .full{grid-column:1/-1}.fields em{display:block;margin-top:5px;color:#bf392d;font-size:.68rem;font-style:normal}.default-check{display:flex;align-items:center;gap:8px;margin-top:17px;font-size:.75rem}.form-actions{justify-content:flex-end;margin-top:20px}.secondary{border:1px solid #d9dfdb;background:#fff;padding:12px 18px;font-size:.72rem;font-weight:700}.address-grid-real{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}.address-grid-real article{border:1px solid #e0e5e2;background:#fff;padding:24px;box-shadow:0 8px 24px rgba(20,36,29,.03)}.address-grid-real article.selected{border-color:#ffb4a8;box-shadow:inset 0 3px 0 var(--brand)}.address-icon{display:grid;width:42px;height:42px;place-items:center;background:#fff0ec;color:var(--brand);font-size:1.1rem}.default-badge{background:#e8f7ed;padding:5px 9px;color:#267647;font-size:.63rem;font-weight:800}.address-grid-real h3{margin:17px 0 8px}.address-grid-real strong{font-size:.82rem}.address-grid-real p{min-height:115px;margin:7px 0 17px;color:#75807b;font-size:.75rem;line-height:1.75}.card-actions{justify-content:flex-start;flex-wrap:wrap;border-top:1px solid #edf0ee;padding-top:14px}.card-actions button{border:0;background:transparent;padding:0;color:var(--brand);font-size:.7rem;font-weight:700}.card-actions .danger{margin-left:auto;color:#b83c31}@media(max-width:767px){.address-toolbar{align-items:flex-start;flex-direction:column}.fields,.address-grid-real{grid-template-columns:1fr}.fields .full{grid-column:auto}.address-form{padding:20px}.card-actions .danger{margin-left:0}}
</style>
