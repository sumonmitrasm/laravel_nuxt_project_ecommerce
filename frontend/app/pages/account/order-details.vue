<script setup lang="ts">
type OrderOption = { name: string | null; value: string; color_code?: string | null }
type OrderItem = { id: number; product_id: number | null; product_name: string; product_code: string | null; sku: string | null; image_url: string | null; options: OrderOption[]; quantity: number; unit_price: string; line_total: string }
type OrderAddress = { recipient_name: string; phone: string; alternative_phone: string | null; division_name: string | null; district_name: string | null; upazila_name: string | null; area: string | null; postal_code: string | null; address_line: string }
type ReturnRequest = { status: string; reason: string; admin_note: string | null; refund_amount: string | null; requested_at: string }
type OrderDetail = { id: number; order_number: string; order_status: string; payment_status: string; payment_method: string; shipping_method_name: string; courier_name: string | null; tracking_number: string | null; shipped_at: string | null; subtotal: string; discount_amount: string; shipping_charge: string; tax_amount: string; grand_total: string; currency: string; customer_note: string | null; cancellation_reason?: string | null; cancelled_at?: string | null; placed_at: string; items: OrderItem[]; address: OrderAddress | null; payment: { method: string; status: string; transaction_id: string | null; paid_at: string | null } | null; status_histories: { status: string; note: string | null; created_at: string }[]; return_request: ReturnRequest | null }

useSeoMeta({ robots: 'noindex, nofollow' })
const route = useRoute()
const config = useRuntimeConfig()
const order = ref<OrderDetail | null>(null)
const loading = ref(true)
const errorMessage = ref('')
const cancelReason = ref('')
const cancelDialogOpen = ref(false)
const cancelling = ref(false)
const paymentStarting = ref(false)
const returnReason = ref('')
const returnStarting = ref(false)
const trackingCopied = ref(false)
const { startSslCommerzPayment } = useOrders()
const xsrfToken = useCookie<string | null>('XSRF-TOKEN')
const { success: showSuccess, error: showError } = useToast()
const cancellationReasons = ['Ordered by mistake', 'Want to change product or address', 'Found a better price', 'Delivery is taking too long', 'Other']
const orderNumber = computed(() => Array.isArray(route.query.order) ? route.query.order[0] : String(route.query.order || ''))

const money = (value: number | string) => `${String.fromCharCode(0x09F3)}${new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value))}`
const formatDate = (value: string) => new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(value))
const formatDateTime = (value: string) => new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short', hour: 'numeric', minute: '2-digit' }).format(new Date(value))
const titleCase = (value: string) => value.replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase())
const paymentLabel = computed(() => order.value?.payment_method === 'cod' ? 'Cash on delivery' : 'SSLCommerz')
const itemOptions = (item: OrderItem) => item.options.map(option => option.value).filter(Boolean).join(' / ')
const statusRank: Record<string, number> = { pending: 0, confirmed: 1, processing: 2, shipped: 3, delivered: 4 }
const currentRank = computed(() => statusRank[order.value?.order_status ?? 'pending'] ?? 0)
const steps = [
  { status: 'pending', label: 'Order placed', icon: 'bi-receipt', fallback: 'Order received' },
  { status: 'confirmed', label: 'Confirmed', icon: 'bi-check-lg', fallback: 'Awaiting confirmation' },
  { status: 'processing', label: 'Processing', icon: 'bi-box-seam', fallback: 'Preparing your items' },
  { status: 'shipped', label: 'Shipped', icon: 'bi-truck', fallback: 'Waiting for courier' },
  { status: 'delivered', label: 'Delivered', icon: 'bi-house-check', fallback: 'Delivery pending' },
]
const stepNote = (step: typeof steps[number]) => {
  const history = order.value?.status_histories.find(entry => entry.status === step.status)
  return history ? formatDateTime(history.created_at) : step.fallback
}
const stepClass = (index: number) => ({ done: currentRank.value > index, active: currentRank.value === index })
const statusMessage = computed(() => {
  const status = order.value?.order_status
  if (status === 'delivered') return 'Your order has been delivered'
  if (status === 'shipped') return 'Your order is on the way'
  if (status === 'cancelled' || status === 'canceled') return 'This order has been cancelled'
  if (status === 'processing') return 'Your order is being prepared'
  if (status === 'confirmed') return 'Your order has been confirmed'
  if (status === 'pending') return 'We have received your order'
  return 'We have received your order'
})

const canCancel = computed(() => ['pending', 'confirmed'].includes(order.value?.order_status ?? ''))
const canPayOnline = computed(() => order.value?.payment_method === 'sslcommerz' && order.value?.payment_status !== 'paid' && !['cancelled', 'canceled', 'delivered'].includes(order.value?.order_status ?? ''))
const canRequestReturn = computed(() => order.value?.order_status === 'delivered' && !order.value.return_request)

useHead(() => ({ title: order.value ? `Order ${order.value.order_number}` : 'Order details' }))

const fetchOrder = async () => {
  if (!orderNumber.value) { errorMessage.value = 'No order number was provided.'; loading.value = false; return }
  try {
    const response = await $fetch<{ status: boolean; order: OrderDetail }>(`/auth/orders/${encodeURIComponent(orderNumber.value)}`, { baseURL: config.public.apiBase, credentials: 'include' })
    order.value = response.order
  } catch (error: any) {
    errorMessage.value = error?.status === 404 ? 'Order not found or you do not have permission to view it.' : (error?.data?.message ?? 'Order details could not be loaded.')
  } finally { loading.value = false }
}

const requestCancellation = () => {
  if (!cancelReason.value) {
    showError('Reason required', 'Please select why you want to cancel this order.')
    return
  }
  cancelDialogOpen.value = true
}

const cancelOrder = async () => {
  if (!order.value || !canCancel.value || cancelling.value) return
  cancelling.value = true
  try {
    await $fetch('/sanctum/csrf-cookie', { baseURL: config.public.backendBase, credentials: 'include' })
    refreshCookie('XSRF-TOKEN')
    const response = await $fetch<{ status: boolean; message: string; order: Pick<OrderDetail, 'order_status' | 'payment_status' | 'cancellation_reason' | 'cancelled_at'> }>(`/auth/orders/${encodeURIComponent(order.value.order_number)}/cancel`, {
      baseURL: config.public.apiBase,
      method: 'PATCH',
      credentials: 'include',
      headers: xsrfToken.value ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) } : {},
      body: { reason: cancelReason.value },
    })
    Object.assign(order.value, response.order)
    order.value.status_histories.push({ status: 'cancelled', note: cancelReason.value, created_at: new Date().toISOString() })
    cancelDialogOpen.value = false
    showSuccess('Order cancelled', response.message)
  } catch (error: any) {
    cancelDialogOpen.value = false
    showError('Cancellation failed', error?.data?.errors?.order?.[0] ?? error?.data?.message ?? 'The order could not be cancelled.')
    await fetchOrder()
  } finally {
    cancelling.value = false
  }
}

const retryPayment = async () => {
  if (!order.value || !canPayOnline.value || paymentStarting.value) return
  paymentStarting.value = true
  try {
    const response = await startSslCommerzPayment(order.value.order_number)
    window.location.assign(response.gateway_url)
  } catch (error: any) {
    showError('Payment not started', error?.data?.errors?.payment?.[0] ?? error?.data?.message ?? 'Please try again.')
    paymentStarting.value = false
  }
}

const requestReturn = async () => {
  if (!order.value || !canRequestReturn.value || !returnReason.value || returnStarting.value) return
  returnStarting.value = true
  try {
    await $fetch('/sanctum/csrf-cookie', { baseURL: config.public.backendBase, credentials: 'include' })
    refreshCookie('XSRF-TOKEN')
    const response = await $fetch<{ message: string; return_request: ReturnRequest }>(`/auth/orders/${encodeURIComponent(order.value.order_number)}/return`, {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
      headers: xsrfToken.value ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) } : {},
      body: { reason: returnReason.value },
    })
    order.value.return_request = response.return_request
    showSuccess('Return request sent', response.message)
  } catch (error: any) {
    showError('Request failed', error?.data?.errors?.order?.[0] ?? error?.data?.message ?? 'Please try again.')
  } finally { returnStarting.value = false }
}

onMounted(async () => {
  await fetchOrder()
  const paymentState = Array.isArray(route.query.payment) ? route.query.payment[0] : route.query.payment
  if (paymentState === 'success') showSuccess('Payment successful', 'Your SSLCommerz payment was verified securely.')
  if (paymentState === 'failed') showError('Payment failed', 'The payment was not completed. You can try again below.')
  if (paymentState === 'cancelled') showError('Payment cancelled', 'You cancelled the gateway payment. Your order is still unpaid.')
  if (paymentState === 'invalid') showError('Payment not verified', 'We could not verify this transaction. Please contact support if money was deducted.')
})
const printInvoice = () => window.print()

const copyTrackingNumber = async () => {
  if (!order.value?.tracking_number) return

  await navigator.clipboard.writeText(order.value.tracking_number)
  trackingCopied.value = true
  window.setTimeout(() => { trackingCopied.value = false }, 2000)
}
</script>

<template>
  <main class="order-page">
    <section v-if="loading" class="order-state"><i class="bi bi-arrow-repeat"></i><h1>Loading order...</h1></section>
    <section v-else-if="errorMessage || !order" class="order-state error"><i class="bi bi-exclamation-circle"></i><h1>Order unavailable</h1><p>{{ errorMessage }}</p><NuxtLink to="/account">Back to my account</NuxtLink></section>
    <template v-else>
      <section class="order-hero"><div class="container"><div><small>Order details</small><h1>#{{ order.order_number }}</h1><p>Placed on {{ formatDate(order.placed_at) }}</p></div><span :class="order.order_status"><i class="bi bi-clock"></i> {{ titleCase(order.order_status) }}</span></div></section>
      <div class="shop-breadcrumb"><div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><NuxtLink to="/account">My Account</NuxtLink><i class="bi bi-chevron-right"></i><span>Order #{{ order.order_number }}</span></div></div>

      <section class="container order-content">
        <div class="order-actions"><NuxtLink to="/account"><i class="bi bi-arrow-left"></i> Back to my orders</NuxtLink><div><button type="button" @click="printInvoice"><i class="bi bi-printer"></i> Print invoice</button><NuxtLink to="/contact"><i class="bi bi-headset"></i> Get help</NuxtLink></div></div>
        <div class="order-layout">
          <div class="order-main">
            <section class="order-box"><header><div><small>Delivery progress</small><h2>{{ statusMessage }}</h2></div><span>{{ order.shipping_method_name }}</span></header><div v-if="order.order_status === 'cancelled' || order.order_status === 'canceled'" class="cancelled-note">This order will not continue through delivery.</div><div v-else class="timeline"><div v-for="(step, index) in steps" :key="step.status" :class="stepClass(index)"><i class="bi" :class="step.icon"></i><span><b>{{ step.label }}</b><small>{{ stepNote(step) }}</small></span></div></div><section v-if="order.courier_name || order.tracking_number" class="tracking-box" aria-label="Courier tracking"><div class="tracking-icon"><i class="bi bi-truck"></i></div><div class="tracking-details"><small>Courier tracking</small><strong>{{ order.courier_name || 'Courier information' }}</strong><div class="tracking-number"><span>{{ order.tracking_number || 'Tracking number will be added soon' }}</span><button v-if="order.tracking_number" type="button" @click="copyTrackingNumber"><i class="bi" :class="trackingCopied ? 'bi-check-lg' : 'bi-copy'"></i> {{ trackingCopied ? 'Copied' : 'Copy' }}</button></div></div></section><div v-if="canCancel" class="cancel-order"><div><strong>Need to cancel?</strong><small>Cancellation is available before processing starts.</small></div><select v-model="cancelReason" aria-label="Cancellation reason"><option value="" disabled>Select a reason</option><option v-for="reason in cancellationReasons" :key="reason" :value="reason">{{ reason }}</option></select><button type="button" @click="requestCancellation">Cancel order</button></div></section>
            <section class="order-box"><header><div><small>{{ order.items.reduce((sum, item) => sum + item.quantity, 0) }} items</small><h2>Products in this order</h2></div></header><article v-for="item in order.items" :key="item.id"><div class="product-picture"><img v-if="item.image_url" :src="item.image_url" :alt="item.product_name"><i v-else class="bi bi-image"></i></div><div><NuxtLink v-if="item.product_id" :to="{ path: '/product', query: { id: item.product_id } }">{{ item.product_name }}</NuxtLink><strong v-else>{{ item.product_name }}</strong><small v-if="itemOptions(item)">{{ itemOptions(item) }}</small><span>Quantity: {{ item.quantity }}<template v-if="item.sku"> · SKU: {{ item.sku }}</template></span></div><strong>{{ money(item.line_total) }}</strong></article></section>
            <section class="order-box return-box"><i class="bi bi-arrow-counterclockwise"></i><div v-if="order.return_request"><h3>Return request: {{ titleCase(order.return_request.status) }}</h3><p>{{ order.return_request.reason }}<template v-if="order.return_request.admin_note"> — {{ order.return_request.admin_note }}</template></p></div><div v-else-if="canRequestReturn"><h3>Need to return this order?</h3><select v-model="returnReason" aria-label="Return reason"><option value="" disabled>Select a reason</option><option>Wrong product received</option><option>Product damaged</option><option>Product does not match description</option><option>Other</option></select></div><div v-else><h3>Need help with this order?</h3><p>A return can be requested after delivery.</p></div><button v-if="canRequestReturn" type="button" :disabled="!returnReason || returnStarting" @click="requestReturn">{{ returnStarting ? 'Sending...' : 'Request return' }}</button><NuxtLink v-else-if="!order.return_request" to="/contact">Request support</NuxtLink></section>
          </div>
          <aside>
            <section class="order-box summary"><header><div><small>Payment summary</small><h2>Order total</h2></div></header><div><span>Subtotal</span><b>{{ money(order.subtotal) }}</b></div><div v-if="Number(order.discount_amount) > 0"><span>Discount</span><b>−{{ money(order.discount_amount) }}</b></div><div><span>Shipping</span><b>{{ Number(order.shipping_charge) ? money(order.shipping_charge) : 'Free' }}</b></div><div v-if="Number(order.tax_amount) > 0"><span>Tax</span><b>{{ money(order.tax_amount) }}</b></div><div class="grand"><span>Total <small>{{ order.currency }}</small></span><strong>{{ money(order.grand_total) }}</strong></div><p><i class="bi bi-credit-card"></i> {{ paymentLabel }} · {{ titleCase(order.payment_status) }}</p><button v-if="canPayOnline" class="pay-again" type="button" :disabled="paymentStarting" @click="retryPayment">{{ paymentStarting ? 'Opening payment...' : 'Pay securely with SSLCommerz' }}</button></section>
            <section v-if="order.address" class="order-box address"><header><div><small>Delivery details</small><h2>Shipping address</h2></div></header><address><b>{{ order.address.recipient_name }}</b><span>{{ order.address.address_line }}</span><span>{{ [order.address.area, order.address.upazila_name, order.address.district_name].filter(Boolean).join(', ') }}</span><span>{{ [order.address.division_name, order.address.postal_code].filter(Boolean).join(' - ') }}</span><span>{{ order.address.phone }}</span><span v-if="order.address.alternative_phone">Alternative: {{ order.address.alternative_phone }}</span></address></section>
          </aside>
        </div>
      </section>
      <ConfirmDialog
        :open="cancelDialogOpen"
        eyebrow="ORDER CANCELLATION"
        title="Cancel this order?"
        :message="`Order ${order.order_number} will be cancelled and cannot be restored. Reason: ${cancelReason}`"
        confirm-label="Yes, cancel order"
        cancel-label="Keep order"
        loading-label="Cancelling..."
        icon="bi-x-circle"
        :loading="cancelling"
        @confirm="cancelOrder"
        @cancel="cancelDialogOpen = false"
      />
    </template>
  </main>
</template>
<style scoped>
.order-page{background:#f7f9f7}.order-hero{background:#17211d;color:#fff}.order-hero>.container{display:flex;justify-content:space-between;align-items:center;min-height:195px}.order-hero small,.order-box header small{color:var(--brand);font-size:.65rem;font-weight:800;text-transform:uppercase}.order-hero h1{margin:7px 0 3px;font-size:clamp(2.2rem,4vw,3.6rem)}.order-hero p{margin:0;color:#aeb9b3}.order-hero>.container>span{border-radius:30px;background:#fff2d8;padding:10px 15px;color:#986100;font-size:.7rem;font-weight:700}.order-content{padding-top:32px;padding-bottom:70px}.order-actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}.order-actions a{color:var(--ink);font-size:.72rem;text-decoration:none}.order-actions>div{display:flex;gap:9px}.order-actions button,.order-actions>div a{border:1px solid #dce2de;background:#fff;padding:9px 13px;font-weight:700}.order-actions i{margin-right:5px}.order-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(290px,.65fr);align-items:start;gap:22px}.order-main,aside{display:grid;gap:20px}.order-box{border:1px solid #e1e6e2;background:#fff;padding:25px}.order-box header{display:flex;justify-content:space-between;align-items:start;gap:15px;margin-bottom:22px}.order-box h2{margin:3px 0 0;font-size:1.2rem}.order-box header>span{background:#eef3ef;padding:7px 9px;color:#647069;font-size:.65rem}.timeline{display:grid;grid-template-columns:repeat(5,1fr)}.timeline>div{position:relative;display:grid;justify-items:center;text-align:center}.timeline>div:before{position:absolute;top:20px;right:50%;left:-50%;height:2px;background:#e2e7e4;content:""}.timeline>div:first-child:before{display:none}.timeline .done:before,.timeline .active:before{background:var(--brand)}.timeline i{z-index:1;display:grid;width:42px;height:42px;border:2px solid #dfe5e1;border-radius:50%;place-items:center;background:#fff;color:#9da6a1}.timeline .done i,.timeline .active i{border-color:var(--brand);background:var(--brand);color:#fff}.timeline span,.timeline b,.timeline small{display:block}.timeline span{margin-top:10px}.timeline b{font-size:.7rem}.timeline small{margin-top:3px;color:#98a19c;font-size:.6rem}.order-box article{display:grid;grid-template-columns:76px 1fr auto;align-items:center;gap:16px;padding:16px 0;border-top:1px solid #e9edea}.product-picture{display:grid;width:76px;height:76px;place-items:center;background:#f0f4f1}.product-picture img{width:64px;height:64px;object-fit:contain}.order-box article a{color:var(--ink);font-size:.82rem;font-weight:700;text-decoration:none}.order-box article small,.order-box article span{display:block;margin-top:4px;color:#929b96;font-size:.65rem}.return-box{display:flex;align-items:center;gap:14px}.return-box>i{display:grid;width:43px;height:43px;place-items:center;background:#fff0ec;color:var(--brand)}.return-box div{flex:1}.return-box h3{margin:0 0 3px;font-size:.95rem}.return-box p{margin:0;color:#818b85;font-size:.7rem}.return-box a{color:var(--brand);font-size:.7rem;font-weight:700}.summary>div{display:flex;justify-content:space-between;padding:9px 0;color:#717b76;font-size:.74rem}.summary .grand{margin-top:7px;border-top:1px solid #e2e7e4;padding-top:16px;color:var(--ink)}.grand small{display:block}.grand strong{color:var(--brand);font-size:1.15rem}.summary>p{margin:12px 0 0;padding-top:13px;border-top:1px solid #e2e7e4;color:#7c8680;font-size:.65rem}.address address{display:grid;gap:4px;margin:0;color:#717b76;font-size:.73rem;font-style:normal}.address b{margin-bottom:3px;color:var(--ink)}
@media(max-width:991px){.order-layout{grid-template-columns:1fr}aside{grid-template-columns:1fr 1fr}}
@media(max-width:767px){.order-hero>.container{min-height:160px}.order-actions{align-items:flex-start;flex-direction:column;gap:15px}.timeline{grid-template-columns:1fr}.timeline>div{grid-template-columns:42px 1fr;justify-items:start;gap:12px;padding-bottom:22px;text-align:left}.timeline>div:before{top:-22px;right:auto;left:20px;width:2px;height:22px}.timeline span{margin-top:2px}aside{grid-template-columns:1fr}}
@media(max-width:480px){.order-hero>.container>span{display:none}.order-box{padding:19px}.order-box header{flex-direction:column}.order-box article{grid-template-columns:65px 1fr}.product-picture{width:65px;height:65px}.order-box article>strong{grid-column:2}.return-box{align-items:flex-start;flex-wrap:wrap}.return-box a{margin-left:57px}}

.order-state{display:grid;min-height:65vh;place-content:center;justify-items:center;padding:40px;text-align:center}.order-state>i{color:var(--brand);font-size:2rem}.order-state h1{margin:12px 0 5px}.order-state p{color:#747e79}.order-state a{margin-top:12px;background:var(--ink);padding:11px 18px;color:#fff;text-decoration:none}.order-state.error>i{color:var(--brand)}.cancelled-note{border-left:3px solid var(--brand);background:#fff2ef;padding:14px;color:#a33b30;font-size:.78rem}.product-picture>i{color:#a0aaa4;font-size:1.5rem}.order-hero>.container>span.cancelled,.order-hero>.container>span.canceled{background:#ffe8e4;color:#b33228}@media print{.order-actions,.shop-breadcrumb,.return-box{display:none!important}.order-page{background:#fff}.order-content{padding-top:20px}.order-hero>.container{min-height:120px}}.cancel-order{display:grid;grid-template-columns:1fr minmax(190px,240px) auto;align-items:end;gap:12px;margin-top:24px;border-top:1px solid #e6ebe7;padding-top:18px}.cancel-order div,.cancel-order strong,.cancel-order small{display:block}.cancel-order small{margin-top:3px;color:#84908a;font-size:.65rem}.cancel-order select{height:40px;border:1px solid #dce2de;background:#fff;padding:0 10px;color:#34413b;font-size:.7rem}.cancel-order button{height:40px;border:1px solid #d94b3d;background:#fff;color:#d94b3d;padding:0 14px;font-size:.7rem;font-weight:750}@media(max-width:767px){.cancel-order{grid-template-columns:1fr}.cancel-order select,.cancel-order button{width:100%}}
.pay-again{width:100%;margin-top:14px;border:0;background:var(--brand);padding:12px;color:#fff;font-size:.72rem;font-weight:750}.pay-again:disabled{opacity:.65}.tracking-box{display:flex;align-items:center;gap:14px;margin-top:22px;border:1px solid #d8e6dd;border-left:4px solid var(--brand);padding:15px 17px;background:#f6faf7}.tracking-icon{display:grid;width:42px;height:42px;flex:0 0 42px;place-items:center;border-radius:50%;background:var(--brand);color:#fff}.tracking-icon i{font-size:1.1rem}.tracking-details{min-width:0;flex:1}.tracking-details>small{display:block;margin-bottom:3px;color:#718078;font-size:.62rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.tracking-details>strong{display:block;color:var(--ink);font-size:.9rem}.tracking-number{display:flex;align-items:center;gap:10px;margin-top:8px}.tracking-number span{overflow:hidden;color:#526059;font-family:monospace;font-size:.78rem;font-weight:700;letter-spacing:.03em;text-overflow:ellipsis;white-space:nowrap}.tracking-number button{margin-left:auto;border:1px solid #cbd8d0;background:#fff;padding:6px 9px;color:#3d5045;font-size:.66rem;font-weight:700;white-space:nowrap}.tracking-number button i{margin-right:3px;color:var(--brand)}.return-box select,.return-box button{border:1px solid #dce2de;background:#fff;padding:9px 10px;color:#34413b;font-size:.7rem}.return-box button{border-color:var(--brand);background:var(--brand);color:#fff;font-weight:700}.return-box button:disabled{opacity:.6}@media(max-width:480px){.tracking-box{align-items:flex-start;padding:14px}.tracking-number{align-items:flex-start;flex-direction:column;gap:7px}.tracking-number button{margin-left:0}}
</style>
