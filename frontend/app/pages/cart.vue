<script setup lang="ts">
useSeoMeta({ robots: 'noindex, nofollow' })
const { cart, fetchCart, updateCartItem, removeCartItem, clearCart } = useCart()
const loading = ref(!cart.value)
const errorMessage = ref('')
const successMessage = ref('')
const working = ref<number[]>([])
const clearing = ref(false)
const showClearConfirm = ref(false)

useHead({ title: 'Shopping Cart | NOVACART' })
const busy = (id: number) => working.value.includes(id)
const money = (value: number | string) => Number(value || 0).toLocaleString('en-BD', { maximumFractionDigits: 2 })
const optionText = (item: { options: Array<{ name: string | null; value: string }> }) =>
    item.options.filter(option => option.name && option.value).map(option => `${option.name}: ${option.value}`).join(' · ')

let toastTimer: ReturnType<typeof setTimeout> | undefined
watch([successMessage, errorMessage], ([success, error]) => {
    if (!success && !error) return
    if (toastTimer) clearTimeout(toastTimer)
    toastTimer = setTimeout(() => { successMessage.value = ''; errorMessage.value = '' }, 3500)
})
onBeforeUnmount(() => { if (toastTimer) clearTimeout(toastTimer) })

onMounted(async () => {
    try { await fetchCart() }
    catch (error: any) { showError(error, 'Cart could not be loaded.') }
    finally { loading.value = false }
})

async function changeQuantity(item: NonNullable<typeof cart.value>['items'][number], change: number) {
    const quantity = item.quantity + change
    if (quantity < 1 || quantity > item.maximum_quantity || busy(item.id)) return
    working.value.push(item.id); errorMessage.value = ''; successMessage.value = ''
    try {
        const response = await updateCartItem(item.id, quantity)
        successMessage.value = response.message ?? 'Cart updated successfully.'
    } catch (error: any) {
        showError(error, 'Quantity could not be updated.')
        await fetchCart(true).catch(() => undefined)
    } finally { working.value = working.value.filter(id => id !== item.id) }
}

async function setQuantity(item: NonNullable<typeof cart.value>['items'][number], event: Event) {
    const input = event.target as HTMLInputElement
    const quantity = Number(input.value)
    if (!Number.isInteger(quantity) || quantity < 1 || quantity > item.maximum_quantity) {
        input.value = String(item.quantity); return
    }
    if (quantity !== item.quantity) await changeQuantity(item, quantity - item.quantity)
}

async function removeItem(id: number) {
    if (busy(id)) return
    working.value.push(id); errorMessage.value = ''; successMessage.value = ''
    try {
        const response = await removeCartItem(id)
        successMessage.value = response.message ?? 'Product removed from cart.'
    } catch (error: any) { showError(error, 'Product could not be removed.') }
    finally { working.value = working.value.filter(itemId => itemId !== id) }
}

async function removeAll() {
    if (clearing.value) return
    clearing.value = true; errorMessage.value = ''; successMessage.value = ''
    try {
        const response = await clearCart()
        successMessage.value = response.message ?? 'Cart cleared successfully.'
        showClearConfirm.value = false
    } catch (error: any) { showError(error, 'Cart could not be cleared.') }
    finally { clearing.value = false }
}

function showError(error: any, fallback: string) {
    const errors = error?.data?.errors
    errorMessage.value = errors ? Object.values(errors).flat().join(' ') : error?.data?.message ?? fallback
}
</script>

<template>
    <main>
        <section class="shop-hero cart-hero"><div class="container text-center"><h1>Shopping Cart</h1><p>Shop</p></div></section>
        <div class="shop-breadcrumb"><div class="container">
            <NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i>
            <NuxtLink to="/shop">Shop</NuxtLink><i class="bi bi-chevron-right"></i><span>Shopping Cart</span>
        </div></div>

        <section class="cart-layout container py-5">
            <Transition name="cart-toast">
                <div v-if="successMessage || errorMessage" class="cart-toast" :class="errorMessage ? 'error' : 'success'" role="status">
                    <span class="cart-toast-icon"><i :class="errorMessage ? 'bi bi-exclamation-lg' : 'bi bi-check-lg'"></i></span>
                    <div><strong>{{ errorMessage ? 'Something went wrong' : 'Cart updated' }}</strong><small>{{ errorMessage || successMessage }}</small></div>
                    <button type="button" aria-label="Close message" @click="successMessage = ''; errorMessage = ''"><i class="bi bi-x-lg"></i></button>
                </div>
            </Transition>

            <div v-if="loading" class="text-center py-5"><span class="spinner-border"></span><p class="mt-3">Loading your cart...</p></div>
            <div v-else-if="!cart?.items.length" class="cart-empty show">
                <i class="bi bi-cart-x"></i><h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything yet.</p><NuxtLink to="/shop">Start shopping</NuxtLink>
            </div>

            <div v-else class="row g-4 g-xl-5">
                <div class="col-lg-8">
                    <div class="cart-table-head"><span>Product</span><span>Price</span><span>Quantity</span><span>Total</span><span></span></div>
                    <div class="cart-items">
                        <article v-for="item in cart.items" :key="item.id" class="cart-item">
                            <div class="cart-product">
                                <NuxtLink :to="{ path: '/product', query: { id: item.product_id } }">
                                    <span class="cart-product-image" :style="item.image_url ? { backgroundImage: `url(${item.image_url})`, backgroundSize: 'contain', backgroundPosition: 'center' } : undefined"></span>
                                </NuxtLink>
                                <div>
                                    <NuxtLink :to="{ path: '/product', query: { id: item.product_id } }">{{ item.name }}</NuxtLink>
                                    <small v-if="optionText(item)">{{ optionText(item) }}</small>
                                    <small v-if="item.sku">SKU: {{ item.sku }}</small>
                                    <small v-if="!item.available" class="text-danger">Currently unavailable</small>
                                </div>
                            </div>
                            <div class="cart-unit-price" data-label="Price">
                                <del v-if="item.regular_price > item.unit_price" class="d-block text-muted">৳{{ money(item.regular_price) }}</del>
                                ৳{{ money(item.unit_price) }}
                            </div>
                            <div class="cart-quantity qty">
                                <button type="button" :disabled="item.quantity <= 1 || busy(item.id)" @click="changeQuantity(item, -1)">−</button>
                                <input :value="item.quantity" type="number" min="1" :max="item.maximum_quantity" :disabled="busy(item.id)" @change="setQuantity(item, $event)">
                                <button type="button" :disabled="item.quantity >= item.maximum_quantity || busy(item.id)" @click="changeQuantity(item, 1)">+</button>
                            </div>
                            <div class="cart-line-total" data-label="Total">৳{{ money(item.line_total) }}</div>
                            <button class="cart-remove" type="button" :disabled="busy(item.id)" aria-label="Remove product" @click="removeItem(item.id)">
                                <span v-if="busy(item.id)" class="spinner-border spinner-border-sm"></span><i v-else class="bi bi-x-lg"></i>
                            </button>
                        </article>
                    </div>
                    <div class="cart-tools"><span></span><button type="button" :disabled="clearing" @click="showClearConfirm = true">
                        Clear cart <i class="bi bi-trash"></i>
                    </button></div>
                    <NuxtLink class="continue-shopping" to="/shop"><i class="bi bi-arrow-left"></i> Continue shopping</NuxtLink>
                </div>

                <div class="col-lg-4"><aside class="cart-summary">
                    <h2>Cart Total</h2>
                    <div class="summary-row"><span>Subtotal ({{ cart.cart_count }} items)</span><strong>৳{{ money(cart.summary.subtotal) }}</strong></div>
                    <div class="cart-shipping-note"><i class="bi bi-truck"></i><span><strong>Shipping</strong><small>Calculated at checkout after you add your delivery address.</small></span></div>
                    <div class="summary-total"><span>Estimated total</span><strong>৳{{ money(cart.summary.total) }}</strong></div>
                    <NuxtLink class="cart-checkout" to="/checkout"><i class="bi bi-lock"></i> Proceed to checkout</NuxtLink>
                    <div class="secure-note"><i class="bi bi-shield-check"></i> Secure checkout · Easy returns</div>
                </aside></div>
            </div>
        </section>

        <Teleport to="body">
            <Transition name="clear-modal">
                <div v-if="showClearConfirm" class="clear-modal-backdrop" @click.self="showClearConfirm = false">
                    <section class="clear-modal" role="dialog" aria-modal="true" aria-labelledby="clear-cart-title">
                        <button class="clear-modal-close" type="button" aria-label="Close" @click="showClearConfirm = false"><i class="bi bi-x-lg"></i></button>
                        <span class="clear-modal-icon"><i class="bi bi-trash3"></i></span>
                        <h2 id="clear-cart-title">Clear your cart?</h2>
                        <p>This will remove every product currently saved in your cart.</p>
                        <div class="clear-modal-actions">
                            <button type="button" class="cancel" :disabled="clearing" @click="showClearConfirm = false">Keep items</button>
                            <button type="button" class="confirm" :disabled="clearing" @click="removeAll">
                                <span v-if="clearing" class="spinner-border spinner-border-sm"></span>
                                <template v-else><i class="bi bi-trash3"></i> Clear cart</template>
                            </button>
                        </div>
                    </section>
                </div>
            </Transition>
        </Teleport>
    </main>
</template>

<style scoped>
.cart-product > a { display: block; flex: 0 0 86px; }
.cart-product-image { display: block; background-repeat: no-repeat; }
.cart-item button:disabled, .cart-tools button:disabled { cursor: not-allowed; opacity: .55; }
.cart-toast { position: fixed; z-index: 1090; top: 24px; right: 24px; display: grid; grid-template-columns: 42px minmax(190px, 1fr) 28px; align-items: center; gap: 12px; width: min(390px, calc(100vw - 32px)); padding: 14px 14px 14px 16px; border: 1px solid #e1e7e3; border-radius: 12px; background: #fff; box-shadow: 0 18px 45px rgba(27, 39, 33, .16); }
.cart-toast-icon { display: grid; width: 42px; height: 42px; border-radius: 50%; place-items: center; }
.cart-toast.success .cart-toast-icon { background: #e9f8ef; color: #18864b; }
.cart-toast.error .cart-toast-icon { background: #fff0ed; color: #e6533d; }
.cart-toast div { display: grid; gap: 2px; }
.cart-toast strong { color: #25332c; font-size: .86rem; }
.cart-toast small { color: #748079; font-size: .75rem; }
.cart-toast > button { border: 0; background: transparent; color: #9aa39e; }
.cart-toast-enter-active, .cart-toast-leave-active { transition: .25s ease; }
.cart-toast-enter-from, .cart-toast-leave-to { opacity: 0; transform: translateY(-12px); }
.cart-shipping-note { display: flex; align-items: center; gap: 12px; padding: 18px 0; border-bottom: 1px solid #e1e5e2; }
.cart-shipping-note > i { display: grid; width: 38px; height: 38px; border-radius: 50%; place-items: center; background: #f0f5f2; color: var(--brand); }
.cart-shipping-note span { display: grid; gap: 3px; }
.cart-shipping-note strong { color: var(--ink); font-size: .8rem; }
.cart-shipping-note small { color: #8b9490; font-size: .68rem; line-height: 1.5; }
@media (max-width: 575px) { .cart-toast { top: 16px; right: 16px; } }

.clear-modal-backdrop { position: fixed; z-index: 1100; inset: 0; display: grid; padding: 20px; place-items: center; background: rgba(18, 27, 22, .55); backdrop-filter: blur(3px); }
.clear-modal { position: relative; width: min(430px, 100%); padding: 32px; border-radius: 18px; background: #fff; box-shadow: 0 28px 80px rgba(14, 23, 18, .3); text-align: center; }
.clear-modal-close { position: absolute; top: 15px; right: 15px; display: grid; width: 34px; height: 34px; border: 0; border-radius: 50%; place-items: center; background: #f3f5f4; color: #7b8580; }
.clear-modal-icon { display: grid; width: 62px; height: 62px; margin: 0 auto 18px; border-radius: 50%; place-items: center; background: #fff0ed; color: #e6533d; font-size: 1.35rem; }
.clear-modal h2 { margin: 0 0 8px; color: #202d26; font-size: 1.45rem; }
.clear-modal p { max-width: 320px; margin: 0 auto 25px; color: #7b8580; font-size: .82rem; line-height: 1.65; }
.clear-modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.clear-modal-actions button { min-height: 46px; border-radius: 8px; font-weight: 700; font-size: .78rem; }
.clear-modal-actions .cancel { border: 1px solid #dce2de; background: #fff; color: #445149; }
.clear-modal-actions .confirm { border: 1px solid var(--brand); background: var(--brand); color: #fff; }
.clear-modal-actions button:disabled { cursor: not-allowed; opacity: .6; }
.clear-modal-enter-active, .clear-modal-leave-active { transition: opacity .2s ease; }
.clear-modal-enter-active .clear-modal, .clear-modal-leave-active .clear-modal { transition: transform .2s ease, opacity .2s ease; }
.clear-modal-enter-from, .clear-modal-leave-to { opacity: 0; }
.clear-modal-enter-from .clear-modal, .clear-modal-leave-to .clear-modal { opacity: 0; transform: translateY(12px) scale(.97); }
@media (max-width: 480px) { .clear-modal { padding: 28px 20px 20px; } .clear-modal-actions { grid-template-columns: 1fr; } }
</style>



