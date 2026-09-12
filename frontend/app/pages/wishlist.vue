<script setup lang="ts">
useSeoMeta({ title: 'My Wishlist', robots: 'noindex, nofollow' })

const { authLoaded, isAuthenticated, fetchUser } = useAuth()
const { items, wishlistCount, fetchWishlist, removeFromWishlist } = useWishlist()
const { success: showSuccess, error: showError } = useToast()
const loading = ref(true)
const removingId = ref<number | null>(null)

const money = (value: number | string) => `৳${Number(value || 0).toLocaleString('en-BD', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})}`

const stockText = (item: any) => {
  if (!item.in_stock) return 'Out of stock'
  if (item.stock === null) return 'In stock'
  if (item.stock <= 5) return `Only ${item.stock} left`
  return 'In stock'
}

const removeItem = async (productId: number) => {
  if (removingId.value) return
  removingId.value = productId
  try {
    await removeFromWishlist(productId)
    showSuccess('Wishlist updated', 'Product removed from your wishlist.')
  } catch (error: any) {
    showError('Could not remove product', error?.data?.message ?? 'Please try again.')
  } finally {
    removingId.value = null
  }
}

const shareWishlist = async () => {
  const shareData = { title: 'My Wishlist', text: 'Products saved in my wishlist', url: window.location.href }
  try {
    if (navigator.share) await navigator.share(shareData)
    else {
      await navigator.clipboard.writeText(window.location.href)
      showSuccess('Link copied', 'Wishlist link copied to clipboard.')
    }
  } catch (error: any) {
    if (error?.name !== 'AbortError') showError('Could not share', 'Please try again.')
  }
}

if (!authLoaded.value) await fetchUser().catch(() => null)
if (!isAuthenticated.value) await navigateTo('/login?redirect=/wishlist')

onMounted(async () => {
  try {
    await fetchWishlist(true)
  } catch {
    showError('Wishlist unavailable', 'Could not load your saved products.')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <main>
    <section class="shop-hero wishlist-hero">
      <div class="container text-center"><h1>My Wishlist</h1><p>Saved favourites</p></div>
    </section>
    <div class="shop-breadcrumb">
      <div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><span>Wishlist</span></div>
    </div>

    <section class="wishlist-layout container py-5">
      <div class="wishlist-intro">
        <div>
          <h2>Saved for later <span>{{ wishlistCount }} {{ wishlistCount === 1 ? 'item' : 'items' }}</span></h2>
          <p>Keep track of products you love and open them whenever you’re ready.</p>
        </div>
        <div><button v-if="items.length" type="button" @click="shareWishlist"><i class="bi bi-share"></i> Share wishlist</button></div>
      </div>

      <div v-if="loading" class="wishlist-state"><span class="spinner-border spinner-border-sm"></span><p>Loading your wishlist...</p></div>

      <template v-else-if="items.length">
        <div class="wishlist-table-head"><span>Product</span><span>Price</span><span>Stock status</span><span></span><span></span></div>
        <div class="wishlist-items">
          <article v-for="item in items" :key="item.id" class="wishlist-item">
            <div class="wishlist-product">
              <NuxtLink :to="{ path: '/product', query: { id: item.product_id } }" class="wishlist-image wishlist-dynamic-image"
                :style="{ backgroundImage: item.image_url ? `url(${item.image_url})` : 'none' }"></NuxtLink>
              <div>
                <small>{{ item.category_name }}</small>
                <NuxtLink :to="{ path: '/product', query: { id: item.product_id } }">{{ item.name }}</NuxtLink>
                <button type="button" class="wishlist-mobile-remove" :disabled="removingId === item.product_id" @click="removeItem(item.product_id)">
                  <i class="bi bi-trash3"></i> Remove
                </button>
              </div>
            </div>
            <div class="wishlist-price">{{ money(item.final_price) }} <del v-if="item.has_discount">{{ money(item.regular_price) }}</del></div>
            <div class="wishlist-stock" :class="item.in_stock ? (item.stock !== null && item.stock <= 5 ? 'low-stock' : 'in-stock') : 'out-stock'">
              <i :class="item.in_stock ? 'bi bi-check-circle' : 'bi bi-x-circle'"></i> {{ stockText(item) }}
            </div>
            <NuxtLink class="wishlist-cart" :to="{ path: '/product', query: { id: item.product_id } }">
              <i class="bi bi-eye"></i> View product
            </NuxtLink>
            <button class="wishlist-remove" type="button" :disabled="removingId === item.product_id" aria-label="Remove"
              @click="removeItem(item.product_id)">
              <span v-if="removingId === item.product_id" class="spinner-border spinner-border-sm"></span><i v-else class="bi bi-x-lg"></i>
            </button>
          </article>
        </div>
      </template>

      <div v-else class="wishlist-empty show"><i class="bi bi-heart"></i><h2>Your wishlist is empty</h2>
        <p>Save products you love and find them here anytime.</p><NuxtLink to="/shop">Explore products</NuxtLink>
      </div>

      <div class="wishlist-footer"><NuxtLink to="/shop"><i class="bi bi-arrow-left"></i> Continue shopping</NuxtLink></div>
      <section class="wishlist-benefits"><span><i class="bi bi-truck"></i><b>Fast delivery</b><small>Across Bangladesh</small></span><span><i class="bi bi-shield-check"></i><b>Secure payment</b><small>Protected checkout</small></span><span><i class="bi bi-arrow-counterclockwise"></i><b>Easy returns</b><small>Within 7 days</small></span><span><i class="bi bi-headset"></i><b>Helpful support</b><small>Whenever you need us</small></span></section>
    </section>
  </main>
</template>

<style scoped>
.wishlist-dynamic-image{background-repeat:no-repeat;background-position:center;background-size:contain;background-color:#faf7f4}
.wishlist-state{display:grid;min-height:260px;place-content:center;justify-items:center;gap:12px;color:#78827d}
.wishlist-state p{margin:0}.wishlist-stock.out-stock{color:#dc4d42}.wishlist-mobile-remove{display:none;border:0;background:transparent;color:#d95345;padding:5px 0;font-size:.75rem}
.wishlist-remove:disabled,.wishlist-mobile-remove:disabled{opacity:.55;cursor:wait}

@media(max-width:767.98px){
  .wishlist-hero{padding:30px 0}
  .wishlist-hero h1{font-size:2rem;margin-bottom:3px}
  .wishlist-hero p{margin:0;font-size:.75rem}
  .wishlist-layout{padding-top:28px!important;padding-bottom:35px!important}
  .wishlist-intro{display:grid;gap:15px;margin-bottom:20px}
  .wishlist-intro h2{font-size:1.25rem;line-height:1.25}
  .wishlist-intro h2 span{display:inline-flex;margin-left:5px;padding:4px 8px;border-radius:20px;background:#eef4f1;color:#65736c;font-size:.65rem}
  .wishlist-intro p{max-width:430px;font-size:.73rem;line-height:1.6}
  .wishlist-intro>div:last-child{width:auto}
  .wishlist-intro button{height:38px;padding:0 14px;border-radius:5px;font-size:.65rem}
  .wishlist-items{display:grid;gap:14px}
  .wishlist-item{display:grid;grid-template-columns:minmax(0,1fr) auto;grid-template-areas:"product product" "price stock" "action action";gap:16px 12px;min-height:0;padding:15px;border:1px solid #e2e8e4;border-radius:10px;background:#fff;box-shadow:0 8px 24px rgba(26,48,38,.07)}
  .wishlist-product{grid-area:product;display:grid;grid-template-columns:96px minmax(0,1fr);align-items:center;gap:14px}
  .wishlist-image{width:96px;height:105px;min-width:0;flex-basis:auto;border:1px solid #edf0ee;border-radius:7px}
  .wishlist-product>div{min-width:0;gap:5px}
  .wishlist-product small{font-size:.62rem;text-transform:uppercase;letter-spacing:.05em}
  .wishlist-product a:not(.wishlist-image){font-size:.95rem;line-height:1.35;word-break:break-word}
  .wishlist-mobile-remove{display:inline-flex;align-items:center;gap:5px;width:max-content;margin-top:4px;padding:0;color:#dc574b;font-size:.68rem}
  .wishlist-price{grid-area:price;align-self:center;font-size:1rem;color:#ee5949}
  .wishlist-price del{display:inline;margin-left:5px;font-size:.68rem}
  .wishlist-stock{grid-area:stock;align-self:center;padding:5px 8px;border-radius:20px;background:#edf8f1;font-size:.67rem;white-space:nowrap}
  .wishlist-stock.low-stock{background:#fff5e5}.wishlist-stock.out-stock{background:#fff0ee}
  .wishlist-cart{grid-area:action;display:flex;width:100%;height:43px;align-items:center;justify-content:center;gap:7px;border-radius:5px;background:var(--brand);color:#fff;text-decoration:none}
  .wishlist-cart:hover{background:#e94b3b;color:#fff}
  .wishlist-remove{display:none}
  .wishlist-footer{min-height:0;margin-top:22px;padding:0}
  .wishlist-footer>a{display:inline-flex;align-items:center;gap:7px;font-weight:600}
  .wishlist-benefits{grid-template-columns:repeat(2,minmax(0,1fr));gap:0;margin:28px 0 0;padding:8px;border-radius:10px}
  .wishlist-benefits span{min-height:78px;align-content:center;padding:12px 8px 12px 42px;border-bottom:1px solid rgba(255,255,255,.08)}
  .wishlist-benefits span:nth-last-child(-n+2){border-bottom:0}
  .wishlist-benefits i{left:10px;top:25px;font-size:1.25rem}
  .wishlist-benefits b{font-size:.69rem}.wishlist-benefits small{font-size:.57rem;line-height:1.35}
  .wishlist-empty.show{padding:55px 15px;border:1px solid #e4e9e6;border-radius:10px;background:#fff}
}

@media(max-width:340px){
  .wishlist-layout{padding-left:10px!important;padding-right:10px!important}
  .wishlist-item{padding:12px;grid-template-columns:1fr;grid-template-areas:"product" "price" "stock" "action";gap:11px}
  .wishlist-product{grid-template-columns:78px minmax(0,1fr);gap:10px}.wishlist-image{width:78px;height:88px}
  .wishlist-stock{justify-self:start}.wishlist-benefits{grid-template-columns:1fr}.wishlist-benefits span:nth-last-child(2){border-bottom:1px solid rgba(255,255,255,.08)}
}
</style>