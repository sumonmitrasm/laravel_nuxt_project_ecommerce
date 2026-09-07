<script setup lang="ts">
import type { LocationOption } from '~/composables/useLocations'
useSeoMeta({ robots: 'noindex, nofollow' })
definePageMeta({ middleware: 'auth' })

const { user } = useAuth()
const { addresses, defaultAddress, fetchAddresses, createAddress, updateAddress, removeAddress, makeDefaultAddress } = useAddresses()
const { success: showSuccessToast, error: showErrorToast } = useToast()
const { divisions, fetchDivisions, fetchDistricts, fetchUpazilas } = useLocations()
const districts = ref<LocationOption[]>([])
const upazilas = ref<LocationOption[]>([])
const selectedDivisionId = ref<number | null>(null)
const selectedDistrictId = ref<number | null>(null)
const selectedUpazilaId = ref<number | null>(null)
const locationsLoading = ref(false)
const selectedAddressId = ref<number | null>(null)
const addressLoading = ref(true)
const addressSaving = ref(false)
const addressDeletingId = ref<number | null>(null)
const pendingDeleteAddress = ref<UserAddress | null>(null)
const addressMessage = ref('')
const addressError = ref('')
const addressErrors = ref<Record<string, string[]>>({})
const delivery = reactive({ recipient_name: '', address_line: '', area: '', upazila: '', district: '', division: '', postal_code: '', phone: '' })
const selectedAddress = computed(() => addresses.value.find(address => address.id === selectedAddressId.value) ?? null)

const resetLocationSelection = () => {
  selectedDivisionId.value = null
  selectedDistrictId.value = null
  selectedUpazilaId.value = null
  districts.value = []
  upazilas.value = []
}

const syncLocationSelection = async (divisionId: number, districtId: number, upazilaId: number) => {
  resetLocationSelection()
  const division = divisions.value.find(item => item.id === divisionId)
  if (!division) return
  selectedDivisionId.value = division.id
  districts.value = await fetchDistricts(division.id)
  const district = districts.value.find(item => item.id === districtId)
  if (!district) return
  selectedDistrictId.value = district.id
  upazilas.value = await fetchUpazilas(district.id)
  selectedUpazilaId.value = upazilas.value.find(item => item.id === upazilaId)?.id ?? null
}

const onDivisionChange = async () => {
  delivery.division = divisions.value.find(item => item.id === selectedDivisionId.value)?.name ?? ''
  delivery.district = ''
  delivery.upazila = ''
  selectedDistrictId.value = null
  selectedUpazilaId.value = null
  districts.value = []
  upazilas.value = []
  if (selectedDivisionId.value) districts.value = await fetchDistricts(selectedDivisionId.value)
}

const onDistrictChange = async () => {
  delivery.district = districts.value.find(item => item.id === selectedDistrictId.value)?.name ?? ''
  delivery.upazila = ''
  selectedUpazilaId.value = null
  upazilas.value = []
  if (selectedDistrictId.value) upazilas.value = await fetchUpazilas(selectedDistrictId.value)
}

const onUpazilaChange = () => {
  delivery.upazila = upazilas.value.find(item => item.id === selectedUpazilaId.value)?.name ?? ''
}

const clearDelivery = () => Object.assign(delivery, { recipient_name: user.value?.name ?? '', address_line: '', area: '', upazila: '', district: '', division: '', postal_code: '', phone: '' })
const clearAddressFeedback = () => { addressMessage.value = ''; addressError.value = ''; addressErrors.value = {} }

const selectSavedAddress = async (address: UserAddress) => {
  selectedAddressId.value = address.id
  clearAddressFeedback()
  Object.assign(delivery, {
    recipient_name: address.recipient_name, address_line: address.address_line,
    area: address.area ?? '', upazila: address.upazila_name ?? '', district: address.district_name ?? '',
    division: address.division_name ?? '', postal_code: address.postal_code ?? '', phone: address.phone,
  })
  await syncLocationSelection(address.division, address.district, address.upazila)
}

const startNewAddress = () => {
  selectedAddressId.value = null
  clearAddressFeedback()
  clearDelivery()
  resetLocationSelection()
}

const saveDeliveryAddress = async () => {
  if (addressSaving.value) return
  addressSaving.value = true
  clearAddressFeedback()

  const current = selectedAddress.value
  const payload = {
    label: current?.label ?? (addresses.value.length ? 'Other' : 'Home'),
    recipient_name: delivery.recipient_name.trim(), phone: delivery.phone.trim(),
    alternative_phone: current?.alternative_phone ?? '', division: selectedDivisionId.value ?? 0,
    district: selectedDistrictId.value ?? 0, upazila: selectedUpazilaId.value ?? 0, area: delivery.area.trim(),
    postal_code: delivery.postal_code.trim(), address_line: delivery.address_line.trim(),
    is_default: current?.is_default ?? addresses.value.length === 0,
  }

  try {
    const response = current ? await updateAddress(current.id, payload) : await createAddress(payload)
    if (response.address) await selectSavedAddress(response.address)
    addressMessage.value = response.message
  } catch (error: any) {
    if ((error?.statusCode ?? error?.status) === 422) {
      addressErrors.value = error?.data?.errors ?? {}
      addressError.value = 'Please correct the highlighted address fields.'
    } else addressError.value = error?.data?.message ?? 'The delivery address could not be saved.'
  } finally { addressSaving.value = false }
}

const requestDeleteAddress = (address: UserAddress) => {
  pendingDeleteAddress.value = address
}

const deleteSavedAddress = async () => {
  const address = pendingDeleteAddress.value
  if (!address) return
  addressDeletingId.value = address.id
  clearAddressFeedback()
  try {
    const response = await removeAddress(address.id)
    pendingDeleteAddress.value = null
    const next = defaultAddress.value ?? addresses.value[0]
    if (next) await selectSavedAddress(next); else startNewAddress()
    addressMessage.value = response.message
    showSuccessToast('Address removed', response.message)
  } catch (error: any) {
    addressError.value = error?.data?.message ?? 'The address could not be removed.'
    showErrorToast('Address not removed', addressError.value)
  }
  finally { addressDeletingId.value = null }
}

const setCheckoutDefault = async (address: UserAddress) => {
  clearAddressFeedback()
  try { const response = await makeDefaultAddress(address.id); await selectSavedAddress(addresses.value.find(item => item.id === address.id) ?? address); addressMessage.value = response.message }
  catch (error: any) { addressError.value = error?.data?.message ?? 'The default address could not be changed.' }
}


onMounted(async () => {
  locationsLoading.value = true
  try {
    await Promise.all([fetchAddresses(), fetchDivisions()])
    const initial = defaultAddress.value ?? addresses.value[0]
    if (initial) await selectSavedAddress(initial); else { clearDelivery(); resetLocationSelection() }
  } finally {
    addressLoading.value = false
    locationsLoading.value = false
  }
})
</script>
<template>
  <main class="checkout-main">
        <div class="container">
            <div class="checkout-back"><a href="cart.html"><i class="bi bi-arrow-left"></i> Return to cart</a><span>Need
                    help? <a href="#">Contact support</a></span></div>
            <div class="row g-4 g-xl-5">
                <div class="col-lg-7">
                    <form class="checkout-form" id="checkoutForm" data-checkout-form="">
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>1</span>
                                <div>
                                    <h2>Delivery address</h2>
                                    <p>Enter the address where you want your order delivered.</p>
                                </div>
                            </div>
                            <div v-if="addressLoading" class="saved-address-loading"><span class="spinner-border spinner-border-sm"></span> Loading saved addresses...</div>
                            <div v-else-if="addresses.length" class="saved-addresses">
                                <article v-for="address in addresses" :key="address.id" :class="{ selected: selectedAddressId === address.id }" @click="selectSavedAddress(address)">
                                    <span><strong>{{ address.label }}</strong><small v-if="address.is_default">Default</small></span>
                                    <em>{{ address.recipient_name }} · {{ address.phone }}</em>
                                    <p>{{ address.address_line }}, {{ address.upazila_name }}, {{ address.district_name }}</p>
                                    <div class="saved-address-actions">
                                        <button type="button" @click.stop="selectSavedAddress(address)"><i class="bi bi-pencil"></i> Edit</button>
                                        <button v-if="!address.is_default" type="button" @click.stop="setCheckoutDefault(address)">Make default</button>
                                        <button type="button" class="danger" :disabled="addressDeletingId === address.id" @click.stop="requestDeleteAddress(address)">{{ addressDeletingId === address.id ? 'Deleting...' : 'Delete' }}</button>
                                    </div>
                                </article>
                            </div>
                            <button v-if="addresses.length && selectedAddressId !== null" class="new-address-button" type="button" @click="startNewAddress"><i class="bi bi-plus-lg"></i> Add another address</button>
                            <div class="field-grid">
                                <label class="field"><span>Recipient name</span><input v-model.trim="delivery.recipient_name" name="recipientName" autocomplete="name" required></label>
                                <label class="field"><span>Phone</span><input v-model.trim="delivery.phone" name="phone" type="tel" placeholder="01XXXXXXXXX" required></label>
                                <label class="field"><span>Division</span><select v-model="selectedDivisionId" name="division" :disabled="locationsLoading" required @change="onDivisionChange"><option :value="null" disabled>Select division</option><option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option></select></label>
                                <label class="field"><span>District</span><select v-model="selectedDistrictId" name="district" :disabled="!selectedDivisionId" required @change="onDistrictChange"><option :value="null" disabled>Select district</option><option v-for="district in districts" :key="district.id" :value="district.id">{{ district.name }}</option></select></label>
                                <label class="field"><span>Upazila / Thana</span><select v-model="selectedUpazilaId" name="upazila" :disabled="!selectedDistrictId" required @change="onUpazilaChange"><option :value="null" disabled>Select upazila / thana</option><option v-for="upazila in upazilas" :key="upazila.id" :value="upazila.id">{{ upazila.name }}</option></select></label>
                                <label class="field"><span>Area</span><input v-model.trim="delivery.area" name="area"></label>
                                <label class="field"><span>Postal code <small>(optional)</small></span><input v-model.trim="delivery.postal_code" name="postcode"></label>
                                <label class="field full"><span>Full address</span><input v-model.trim="delivery.address_line" name="address" placeholder="House number, road and block" autocomplete="street-address" required><i class="bi bi-geo-alt"></i></label>
                            </div>
                            <p v-if="addressError" class="checkout-address-message error">{{ addressError }}</p>
                            <p v-if="addressMessage" class="checkout-address-message success"><i class="bi bi-check-circle-fill"></i> {{ addressMessage }}</p>
                            <button class="save-address-button" type="button" :disabled="addressSaving" @click="saveDeliveryAddress">
                                <span v-if="addressSaving" class="spinner-border spinner-border-sm"></span>
                                <i v-else class="bi bi-bookmark-check"></i> {{ addressSaving ? 'Saving...' : (selectedAddressId ? 'Update selected address' : 'Save new address') }}
                            </button>
                        </section>
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>2</span>
                                <div>
                                    <h2>Shipping method</h2>
                                    <p>Choose how quickly you'd like to receive your order.</p>
                                </div>
                            </div>
                            <div class="checkout-options"><label class="selected"><input type="radio"
                                        name="checkoutShipping" value="0" checked><i
                                        class="bi bi-truck"></i><span><strong>Free delivery</strong><small>4-6 business days</small></span><b>Free</b></label><label><input type="radio"
                                        name="checkoutShipping" value="120"><i
                                        class="bi bi-box-seam"></i><span><strong>Standard delivery</strong><small>2-4 business days</small></span><b>৳120</b></label><label><input type="radio"
                                        name="checkoutShipping" value="350"><i
                                        class="bi bi-lightning-charge"></i><span><strong>Express
                                            delivery</strong><small>Next business day</small></span><b>৳350</b></label>
                            </div>
                        </section>
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>3</span>
                                <div>
                                    <h2>Payment</h2>
                                    <p>All transactions are secure and encrypted.</p>
                                </div>
                                <div class="payment-logos"><b>VISA</b><b>MC</b></div>
                            </div>
                            <div class="checkout-options payment-methods"><label class="selected"><input type="radio"
                                        name="payment" value="card" checked><i
                                        class="bi bi-credit-card"></i><span><strong>Credit or debit
                                            card</strong><small>Visa, Mastercard</small></span></label>
                                <div class="card-fields"><label class="field full"><span>Card number</span><input
                                            inputmode="numeric" placeholder="0000 0000 0000 0000"><i
                                            class="bi bi-lock"></i></label><label class="field"><span>Expiry
                                            date</span><input placeholder="MM / YY"></label><label
                                        class="field"><span>Security code</span><input inputmode="numeric"
                                            placeholder="CVV"><i class="bi bi-question-circle"></i></label><label
                                        class="field full"><span>Name on card</span><input></label></div><label><input
                                        type="radio" name="payment" value="mobile"><i
                                        class="bi bi-phone"></i><span><strong>Mobile banking</strong><small>bKash or
                                            Nagad</small></span></label><label><input type="radio" name="payment"
                                        value="cod"><i class="bi bi-cash-stack"></i><span><strong>Cash on
                                            delivery</strong><small>Pay when your order arrives</small></span></label>
                            </div>
                        </section>
                        <button class="place-order-mobile d-lg-none" type="submit"><i class="bi bi-lock"></i> Place
                            order · <span data-checkout-mobile-total="">৳19,230</span></button>
                    </form>
                </div>
                <div class="col-lg-5">
                    <aside class="checkout-summary">
                        <h2>Order summary <span>3 items</span></h2>
                        <div class="checkout-products">
                            <div class="checkout-product"><span class="checkout-product-image"
                                    style="--x:2;--y:0"><b>1</b></span>
                                <div><a href="product.html">Pulse Wireless Headphones</a><small>Midnight</small></div>
                                <strong>৳8,490</strong>
                            </div>
                            <div class="checkout-product"><span class="checkout-product-image"
                                    style="--x:3;--y:0"><b>1</b></span>
                                <div><a href="product.html">Orbit Smart Watch S2</a><small>Graphite</small></div>
                                <strong>৳6,990</strong>
                            </div>
                            <div class="checkout-product"><span class="checkout-product-image"
                                    style="--x:2;--y:2"><b>1</b></span>
                                <div><a href="product.html">Aero Cycling Helmet</a><small>Medium</small></div>
                                <strong>৳3,750</strong>
                            </div>
                        </div>
                        <form class="checkout-coupon" data-checkout-coupon=""><input name="checkoutCoupon"
                                placeholder="Gift card or discount code"><button>Apply</button></form>
                        <div class="checkout-discount" data-checkout-discount=""></div>
                        <div class="checkout-totals">
                            <div><span>Subtotal</span><strong>৳19,230</strong></div>
                            <div><span>Shipping</span><strong data-checkout-shipping="">Free</strong></div>
                            <div class="checkout-grand-total"><span>Total <small>BDT</small></span><strong
                                    data-checkout-total="">৳19,230</strong></div>
                        </div><button class="place-order d-none d-lg-flex" type="submit" form="checkoutForm"
                            data-place-order=""><i class="bi bi-lock"></i> Place order</button>
                        <p class="checkout-terms">By placing your order, you agree to our <a href="#">Terms</a> and <a
                                href="#">Privacy Policy</a>.</p>
                        <div class="checkout-trust"><span><i class="bi bi-shield-check"></i><b>Secure
                                    payment</b><small>256-bit encryption</small></span><span><i
                                    class="bi bi-arrow-counterclockwise"></i><b>Easy returns</b><small>Within 7
                                    days</small></span><span><i class="bi bi-headset"></i><b>Here to
                                    help</b><small>Friendly support</small></span></div>
                    </aside>
                </div>
            </div>
        </div>
    </main>
    <div class="checkout-success" data-checkout-success="">
        <div><i class="bi bi-check2-circle"></i>
            <h2>Order placed successfully</h2>
            <p>Thank you! Your confirmation number is <strong>NC-20481</strong>.</p><a href="index.html">Continue
                shopping</a>
        </div>
    </div>

    <ConfirmDialog
      :open="Boolean(pendingDeleteAddress)"
      eyebrow="REMOVE SAVED ADDRESS"
      :title="`Delete ${pendingDeleteAddress?.label ?? ''} address?`"
      message="This address will be permanently removed from your saved delivery locations."
      confirm-label="Delete address"
      :loading="addressDeletingId !== null"
      @cancel="pendingDeleteAddress = null"
      @confirm="deleteSavedAddress"
    />
</template>
<style scoped>
.saved-address-loading { margin-bottom: 20px; border: 1px solid #e1e7e3; border-radius: 8px; background: #f8faf9; padding: 16px; color: #748079; font-size: .76rem; }
.saved-addresses { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
.saved-addresses article { position: relative; min-height: 108px; border: 1px solid #dfe5e1; border-radius: 8px; background: #fff; padding: 11px 14px; cursor: pointer; transition: border-color .2s, box-shadow .2s, transform .2s; }
.saved-addresses article:hover { border-color: #ff9f91; transform: translateY(-1px); }
.saved-addresses article.selected { border-color: #ff5941; box-shadow: 0 0 0 3px rgba(255, 89, 65, .09); }
.saved-addresses article > span { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.saved-addresses strong { color: #15251e; font-size: .82rem; }
.saved-addresses small { border-radius: 999px; background: #e8f7ed; padding: 4px 8px; color: #267647; font-size: .58rem; font-weight: 800; }
.saved-addresses em { display: block; margin-top: 5px; color: #35423c; font-size: .7rem; font-style: normal; }
.saved-addresses p { margin: 4px 0 0; color: #7b8580; font-size: .68rem; line-height: 1.55; }
.saved-address-actions { display: flex; align-items: center; gap: 10px; margin-top: 7px; border-top: 1px solid #edf0ee; padding-top: 6px; }
.saved-addresses .saved-address-actions button { border: 0; background: transparent; padding: 0; color: #e6513d; font-size: .65rem; font-weight: 700; }
.saved-addresses .saved-address-actions button.danger { margin-left: auto; color: #b5362c; }
.saved-addresses .saved-address-actions button:disabled { opacity: .55; }
.new-address-button { margin: 0 0 20px; border: 1px dashed #bfcac4; border-radius: 6px; background: #fafcfb; padding: 11px 14px; color: #26342e; font-size: .7rem; font-weight: 750; }
.checkout-signed-in { color: #267647; font-size: .72rem; font-weight: 700; }
.save-address-button { display: inline-flex; align-items: center; gap: 8px; margin-top: 18px; border: 0; border-radius: 4px; background: #15251e; padding: 13px 18px; color: #fff; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
.save-address-button:disabled { opacity: .65; }
.checkout-address-message { margin: 14px 0 0; padding: 11px 13px; font-size: .72rem; }
.checkout-address-message.error { border-left: 3px solid #d94b3d; background: #fff0ee; color: #a93226; }
.checkout-address-message.success { border-left: 3px solid #27804b; background: #edf8f1; color: #21653e; }
@media (max-width: 575px) { .saved-addresses { grid-template-columns: 1fr; } }
</style>