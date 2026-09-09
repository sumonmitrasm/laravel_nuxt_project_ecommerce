<script setup lang="ts">
import type { LocationOption } from '~/composables/useLocations'
import type { UserAddress } from '~/composables/useAddresses'
useSeoMeta({ robots: 'noindex, nofollow' })
definePageMeta({ middleware: 'auth' })

const { user } = useAuth()
const { addresses, defaultAddress, fetchAddresses, createAddress, updateAddress, removeAddress, makeDefaultAddress } = useAddresses()
const { success: showSuccessToast, error: showErrorToast } = useToast()
const { divisions, fetchDivisions, fetchDistricts, fetchUpazilas } = useLocations()
const { shippingMethods, fetchShippingMethods } = useShippingMethods()
const { cart, fetchCart, applyCoupon, removeCoupon } = useCart()
const selectedShippingMethodId = ref<number | null>(null)
const selectedShippingMethod = computed(() => shippingMethods.value.find(method => method.id === selectedShippingMethodId.value) ?? null)
const paymentMethods = [
  { id: 'sslcommerz', name: 'SSLCommerz', description: 'Card, bank, bKash or Nagad', icon: 'bi bi-phone' },
  { id: 'cod', name: 'Cash on delivery', description: 'Pay when your order arrives', icon: 'bi bi-cash-stack' },
] as const
const selectedPaymentMethod = ref<string | null>(null)
const checkoutErrors = reactive({ address: '', shipping: '', payment: '', cart: '' })
const shippingCharge = computed(() => Number(selectedShippingMethod.value?.charge ?? 0))
const checkoutSubtotal = computed(() => Number(cart.value?.summary.subtotal ?? 0))
const couponDiscount = computed(() => Number(cart.value?.summary.discount ?? 0))
const hasFreeShippingCoupon = computed(() => Boolean(cart.value?.summary.free_shipping))
const checkoutShipping = computed(() => hasFreeShippingCoupon.value ? 0 : shippingCharge.value)
const checkoutTotal = computed(() => Math.max(0, checkoutSubtotal.value - couponDiscount.value + checkoutShipping.value))
const checkoutItems = computed(() => cart.value?.items ?? [])
const checkoutItemCount = computed(() => Number(cart.value?.cart_count ?? 0))
const cartLoadedForCheckout = computed(() => cart.value !== null)
const couponCode = ref('')
const couponLoading = ref(false)
const couponError = ref('')
const itemOptions = (options: Array<{ name: string | null; value: string }>) => options.map(option => option.value).filter(Boolean).join('\\u00B7')
const money = (value: number) => `\u09F3${new Intl.NumberFormat('en-BD', { maximumFractionDigits: 2 }).format(value)}`
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

const clearCheckoutError = (field: keyof typeof checkoutErrors) => { checkoutErrors[field] = '' }
const validateCheckout = async () => {
  Object.keys(checkoutErrors).forEach(key => { checkoutErrors[key as keyof typeof checkoutErrors] = '' })
  if (!selectedAddress.value) checkoutErrors.address = 'Select a saved delivery address. If this is a new address, save it first.'
  if (!selectedShippingMethod.value) checkoutErrors.shipping = 'Select a shipping method.'
  if (!selectedPaymentMethod.value || !paymentMethods.some(method => method.id === selectedPaymentMethod.value)) checkoutErrors.payment = 'Select a payment method.'
  if (!checkoutItems.value.length) checkoutErrors.cart = 'Your cart is empty. Add at least one product before checkout.'

  const firstError = (['address', 'shipping', 'payment', 'cart'] as const).find(field => checkoutErrors[field])
  if (firstError) {
    showErrorToast('Checkout incomplete', checkoutErrors[firstError])
    await nextTick()
    document.getElementById(`checkout-${firstError}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    return
  }
  showSuccessToast('Checkout information complete', 'Address, shipping and payment selections are ready.')
}

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
  clearCheckoutError('address')
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


const submitCoupon = async () => {
  const code = couponCode.value.trim()
  if (!code || couponLoading.value) return
  couponLoading.value = true
  couponError.value = ''
  try {
    const response = await applyCoupon(code)
    couponCode.value = ''
    showSuccessToast('Coupon applied', response.message ?? 'Your discount has been applied.')
  } catch (error: any) {
    couponError.value = error?.data?.errors?.coupon?.[0] ?? error?.data?.message ?? 'This coupon could not be applied.'
    showErrorToast('Coupon not applied', couponError.value)
  } finally {
    couponLoading.value = false
  }
}

const clearCoupon = async () => {
  if (couponLoading.value) return
  couponLoading.value = true
  couponError.value = ''
  try {
    const response = await removeCoupon()
    showSuccessToast('Coupon removed', response.message ?? 'The coupon has been removed.')
  } catch (error: any) {
    couponError.value = error?.data?.message ?? 'The coupon could not be removed.'
    showErrorToast('Coupon not removed', couponError.value)
  } finally {
    couponLoading.value = false
  }
}
onMounted(async () => {
  locationsLoading.value = true
  try {
    await Promise.all([fetchAddresses(), fetchDivisions(), fetchShippingMethods(), fetchCart()])
    selectedAddressId.value = null
    clearDelivery()
    resetLocationSelection()
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
                    <form class="checkout-form" id="checkoutForm" novalidate @submit.prevent="validateCheckout">
                        <section id="checkout-address" class="checkout-section" :class="{ 'has-checkout-error': checkoutErrors.address }">
                            <div class="checkout-section-head"><span>1</span>
                                <div>
                                    <h2>Delivery address</h2>
                                    <p>Enter the address where you want your order delivered.</p>
                                </div>
                            </div>
                            <div v-if="addressLoading" class="saved-address-loading"><span class="spinner-border spinner-border-sm"></span> Loading saved addresses...</div>
                            <p v-if="!addressLoading && addresses.length" class="address-selection-help"><i class="bi bi-geo-alt"></i>Select the delivery address for this order.</p>
                            <div v-if="!addressLoading && addresses.length" class="saved-addresses">
                                <article v-for="address in addresses" :key="address.id" role="button" tabindex="0" :aria-pressed="selectedAddressId === address.id" :class="{ selected: selectedAddressId === address.id }" @click="selectSavedAddress(address)" @keydown.enter.prevent="selectSavedAddress(address)">
                                    <i v-if="selectedAddressId === address.id" class="bi bi-check-circle-fill address-selected-check"></i>
                                    <span><strong>{{ address.label }}</strong><small v-if="address.is_default">Default</small></span>
                                    <em>{{ address.recipient_name }} <span>&middot;</span> {{ address.phone }}</em>
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
                            <p v-if="checkoutErrors.address" class="checkout-validation-error"><i class="bi bi-exclamation-circle"></i>{{ checkoutErrors.address }}</p>
                        </section>
                        <section id="checkout-shipping" class="checkout-section" :class="{ 'has-checkout-error': checkoutErrors.shipping }">
                            <div class="checkout-section-head"><span>2</span>
                                <div>
                                    <h2>Shipping method</h2>
                                    <p>Choose how quickly you'd like to receive your order.</p>
                                </div>
                            </div>
                            <div v-if="shippingMethods.length" class="checkout-options">
                                <label v-for="method in shippingMethods" :key="method.id" :class="{ selected: selectedShippingMethodId === method.id }">
                                    <input v-model="selectedShippingMethodId" type="radio" name="checkoutShipping" :value="method.id" @change="clearCheckoutError('shipping')">
                                    <i :class="method.icon || 'bi bi-truck'"></i>
                                    <span><strong>{{ method.name }}</strong><small v-if="method.delivery_time">{{ method.delivery_time }}</small><small v-else-if="method.description">{{ method.description }}</small></span>
                                    <b>{{ Number(method.charge) === 0 ? 'Free' : money(Number(method.charge)) }}</b>
                                </label>
                            </div>
                            <p v-else class="checkout-address-message error">No shipping method is currently available.</p>
                            <p v-if="checkoutErrors.shipping" class="checkout-validation-error"><i class="bi bi-exclamation-circle"></i>{{ checkoutErrors.shipping }}</p>
                        </section>
                        <section id="checkout-payment" class="checkout-section" :class="{ 'has-checkout-error': checkoutErrors.payment }">
                            <div class="checkout-section-head"><span>3</span>
                                <div>
                                    <h2>Payment</h2>
                                    <p>All transactions are secure and encrypted.</p>
                                </div>
                                <div class="payment-logos"><b>VISA</b><b>MC</b></div>
                            </div>
                            <div class="checkout-options payment-methods">
                                <label v-for="method in paymentMethods" :key="method.id" :class="{ selected: selectedPaymentMethod === method.id }">
                                  <input v-model="selectedPaymentMethod" type="radio" name="payment" :value="method.id" @change="clearCheckoutError('payment')">
                                  <i :class="method.icon"></i><span><strong>{{ method.name }}</strong><small>{{ method.description }}</small></span>
                                </label>
                            </div>
                            <p v-if="checkoutErrors.payment" class="checkout-validation-error"><i class="bi bi-exclamation-circle"></i>{{ checkoutErrors.payment }}</p>
                        </section>
                        <button class="place-order-mobile d-lg-none" type="submit"><i class="bi bi-lock"></i> Place
                            order <span>&middot;</span> <span data-checkout-mobile-total="">{{ money(checkoutTotal) }}</span></button>
                    </form>
                </div>
                <div class="col-lg-5">
                    <aside id="checkout-cart" class="checkout-summary" :class="{ 'has-checkout-error': checkoutErrors.cart }">
                        <h2>Order summary <span>{{ checkoutItemCount }} {{ checkoutItemCount === 1 ? 'item' : 'items' }}</span></h2>
                        <div v-if="!cartLoadedForCheckout" class="checkout-products checkout-products-state"><span class="spinner-border spinner-border-sm"></span> Loading your cart...</div>
                        <div v-else-if="checkoutItems.length" class="checkout-products">
                            <div v-for="item in checkoutItems" :key="item.id" class="checkout-product">
                                <NuxtLink :to="{ path: '/product', query: { id: item.product_id } }" class="checkout-product-image dynamic-image">
                                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name"><i v-else class="bi bi-image"></i><b>{{ item.quantity }}</b>
                                </NuxtLink>
                                <div><NuxtLink :to="{ path: '/product', query: { id: item.product_id } }">{{ item.name }}</NuxtLink><small v-if="item.options.length">{{ itemOptions(item.options) }}</small><small v-else-if="item.sku">SKU: {{ item.sku }}</small><small v-else>{{ item.code }}</small></div>
                                <strong>{{ money(item.line_total) }}</strong>
                            </div>
                        </div>
                        <div v-else class="checkout-products checkout-products-empty"><i class="bi bi-cart-x"></i><div><strong>Your cart is empty</strong><small>Add products before continuing to checkout.</small></div><NuxtLink to="/shop">Shop now</NuxtLink></div>
                        <p v-if="checkoutErrors.cart" class="checkout-validation-error"><i class="bi bi-exclamation-circle"></i>{{ checkoutErrors.cart }}</p>
                        <form v-if="!cart?.coupon" class="checkout-coupon" @submit.prevent="submitCoupon">
                            <input v-model="couponCode" name="checkoutCoupon" autocomplete="off" placeholder="Discount code">
                            <button type="submit" :disabled="couponLoading || !couponCode.trim()">
                                <span v-if="couponLoading" class="spinner-border spinner-border-sm"></span>
                                <span v-else>Apply</span>
                            </button>
                        </form>
                        <div v-else class="applied-coupon">
                            <div><i class="bi bi-ticket-perforated"></i><span><strong>{{ cart.coupon.code }}</strong><small>{{ cart.coupon.name }}</small></span></div>
                            <button type="button" :disabled="couponLoading" @click="clearCoupon">Remove</button>
                        </div>
                        <p v-if="couponError" class="coupon-error">{{ couponError }}</p>
                        <p v-if="cart?.coupon" class="coupon-saving">
                            <i class="bi bi-check-circle-fill"></i>
                            {{ cart.coupon.free_shipping ? 'Free shipping applied.' : `You saved ${money(couponDiscount)}.` }}
                        </p>
                        <div class="checkout-totals">
                            <div><span>Subtotal</span><strong>{{ money(checkoutSubtotal) }}</strong></div>
                            <div v-if="couponDiscount > 0" class="coupon-total-row"><span>Coupon discount</span><strong>&minus;{{ money(couponDiscount) }}</strong></div>
                            <div><span>Shipping</span><strong>{{ checkoutShipping === 0 ? 'Free' : money(checkoutShipping) }}</strong></div>
                            <div class="checkout-grand-total"><span>Total <small>BDT</small></span><strong
                                    data-checkout-total="">{{ money(checkoutTotal) }}</strong></div>
                        </div><button class="place-order d-none d-lg-flex" type="submit" form="checkoutForm"><i class="bi bi-lock"></i> Place order</button>
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
    </main><ConfirmDialog
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
.checkout-product-image.dynamic-image { display:flex; position:relative; align-items:center; justify-content:center; overflow:visible; background:#f4f6f5; background-image:none; }
.checkout-product-image.dynamic-image img { width:100%; height:100%; object-fit:contain; }
.checkout-product-image.dynamic-image > i { color:#98a39d; font-size:1.3rem; }
.checkout-product-image.dynamic-image b { position:absolute; top:-7px; right:-7px; z-index:2; display:grid; width:21px; height:21px; place-items:center; border-radius:50%; background:#75827b; color:#fff; font-size:.65rem; }
.checkout-products-state { display:flex; align-items:center; gap:8px; padding:25px 0; color:#748079; font-size:.75rem; }
.checkout-products-empty { display:grid; grid-template-columns:auto 1fr; gap:11px; align-items:center; padding:24px 0; }
.checkout-products-empty > i { color:#8d9892; font-size:1.7rem; }
.checkout-products-empty div { display:flex; flex-direction:column; }
.checkout-products-empty div > strong { color:#17241e; font-size:.82rem; }
.checkout-products-empty small { color:#7b8580; font-size:.68rem; }
.checkout-products-empty > a { grid-column:2; color:#e6513d; font-size:.7rem; font-weight:700; }

.checkout-coupon button:disabled { cursor: not-allowed; opacity: .6; }
.applied-coupon { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:18px; border:1px solid #b9dfc7; background:#f1faf4; padding:11px 13px; }
.applied-coupon > div { display:flex; align-items:center; gap:10px; }
.applied-coupon i { color:#267647; font-size:1.1rem; }
.applied-coupon span { display:flex; flex-direction:column; }
.applied-coupon strong { color:#183d27; font-size:.76rem; letter-spacing:.04em; }
.applied-coupon small { color:#6a7b70; font-size:.63rem; }
.applied-coupon button { border:0; background:transparent; color:#d84b39; font-size:.68rem; font-weight:750; }
.coupon-error { margin:9px 0 0; color:#b5362c; font-size:.68rem; line-height:1.45; }
.coupon-saving { display:flex; align-items:center; gap:6px; margin:9px 0 0; color:#267647; font-size:.68rem; font-weight:700; }
.coupon-total-row strong { color:#267647 !important; }
.checkout-section.has-checkout-error,.checkout-summary.has-checkout-error{border-color:#e45b4c;box-shadow:0 0 0 3px rgba(228,91,76,.08)}
.checkout-validation-error{display:flex;align-items:center;gap:7px;margin:12px 0 0;padding:10px 12px;border-left:3px solid #d94b3d;background:#fff0ee;color:#a93226;font-size:.7rem;font-weight:650}
.address-selection-help{display:flex;align-items:center;gap:7px;margin:0 0 12px;color:#596a62;font-size:.72rem;font-weight:650}
.address-selection-help i{color:#e6513d}
.saved-addresses article{outline:none}
.saved-addresses article:focus-visible{border-color:#ff5941;box-shadow:0 0 0 3px rgba(255,89,65,.14)}
.address-selected-check{position:absolute;top:10px;right:12px;bottom:auto;color:#27804b;font-size:1rem}
</style>
