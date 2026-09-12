<script setup>
const config = useRuntimeConfig()
const { data } = await useCatalogMenu()
const sections = computed(() => data.value?.categories ?? [])
const mobileSectionId = section => `mobile-section-${section.id}`
const mobileSearchText = ref('')
const mobileSuggestions = ref([])
const mobileSearching = ref(false)
const mobileSearchOpen = ref(false)
let mobileSearchTimer

const money = value => `\u09F3${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})}`

const closeMobileMenu = () => {
    mobileSearchOpen.value = false
    document.querySelector('#menu [data-bs-dismiss="offcanvas"]')?.click()
}

watch(mobileSearchText, term => {
    clearTimeout(mobileSearchTimer)
    const query = term.trim()

    if (query.length < 2) {
        mobileSuggestions.value = []
        mobileSearchOpen.value = false
        mobileSearching.value = false
        return
    }

    mobileSearchOpen.value = true
    mobileSearching.value = true

    mobileSearchTimer = setTimeout(async () => {
        try {
            const response = await $fetch('/search', {
                baseURL: config.public.apiBase,
                query: { q: query }
            })
            mobileSuggestions.value = response.products ?? []
        } catch {
            mobileSuggestions.value = []
        } finally {
            mobileSearching.value = false
        }
    }, 350)
})

const submitMobileSearch = () => {
    const query = mobileSearchText.value.trim()

    if (!query) {
        return
    }

    navigateTo({ path: '/shop', query: { q: query } })
    closeMobileMenu()
}

onBeforeUnmount(() => clearTimeout(mobileSearchTimer))
</script>

<template>
 <footer class="footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h3 class="text-white">NOVA<span class="brand-dot">CART</span></h3>
                    <p class="col-lg-8">An original Bootstrap 5 commerce UI foundation, ready for your backend.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Shop</h6><NuxtLink class="d-block my-2" to="/shop">All products</NuxtLink><NuxtLink
                        class="d-block my-2" to="/wishlist">Wishlist</NuxtLink>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Account</h6><NuxtLink class="d-block my-2" to="/login">Login</NuxtLink><NuxtLink
                        class="d-block my-2" to="/register">Register</NuxtLink>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white">Newsletter</h6>
                    <div class="input-group"><input class="form-control" placeholder="Email address"><button
                            class="btn btn-brand">Join</button></div>
                </div>
            </div>
        </div>
    </footer>
    <div class="offcanvas offcanvas-start" id="menu">
        <div class="mobile-menu-head"><NuxtLink class="mobile-menu-brand" to="/">NOVA<span>CART</span></NuxtLink><button
                class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
        <div class="mobile-menu-body">
      <form class="mobile-search" @submit.prevent="submitMobileSearch">
                <input v-model="mobileSearchText" type="search" autocomplete="off" placeholder="Search products here..." aria-label="Search products">
                <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
            </form>
            <div v-if="mobileSearchOpen" class="mobile-search-results">
                <div v-if="mobileSearching" class="mobile-search-message">Searching...</div>
                <template v-else-if="mobileSuggestions.length">
                    <NuxtLink v-for="product in mobileSuggestions" :key="product.id"
                        :to="{ path: '/product', query: { id: product.id } }" class="mobile-search-result" @click="closeMobileMenu">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name">
                        <span v-else class="mobile-search-image"><i class="bi bi-image"></i></span>
                        <span><small>{{ product.category_name }}</small><strong>{{ product.name }}</strong></span>
                        <b>{{ money(product.final_price) }}</b>
                    </NuxtLink>
                    <button class="mobile-search-all" type="button" @click="submitMobileSearch">View all results <i class="bi bi-arrow-right"></i></button>
                </template>
                <div v-else class="mobile-search-message">No matching products found.</div>
            </div>
            <div class="mobile-quick"><NuxtLink to="/login"><i class="bi bi-person"></i><span>Account</span></NuxtLink><NuxtLink
                    to="/wishlist"><i class="bi bi-heart"></i><span>Wishlist</span></NuxtLink><NuxtLink to="/cart"><i
                        class="bi bi-cart3"></i><span>Cart</span></NuxtLink></div>
            <div class="mobile-nav-label">Shop by section</div>
            <nav class="mobile-nav">
                <div v-for="(section, index) in sections" :key="section.id" class="mobile-nav-group">
                    <button type="button" data-bs-toggle="collapse" :data-bs-target="`#${mobileSectionId(section)}`"
                        :aria-expanded="index === 0"><span>
                            <img v-if="section.image_url" :src="section.image_url" alt="">
                            <i v-else class="bi bi-grid"></i> {{ section.name }}</span><i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse" :class="{ show: index === 0 }" :id="mobileSectionId(section)">
                        <template v-for="category in section.categories" :key="category.id">
                            <NuxtLink :to="{ path: '/shop', query: { category: category.url } }">{{ category.category_name }}</NuxtLink>
                            <NuxtLink v-for="subcategory in category.subcategories" :key="subcategory.id"
                                :to="{ path: '/shop', query: { category: subcategory.url } }">— {{ subcategory.category_name }}</NuxtLink>
                        </template>
                    </div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileElectronics" aria-expanded="true"><span><i class="bi bi-phone"></i>
                            Electronics</span><i class="bi bi-chevron-down"></i></button>
                    <div class="collapse show" id="mobileElectronics"><NuxtLink to="/shop">Smartphones</NuxtLink><NuxtLink
                            to="/shop">Computers &amp; Laptop</NuxtLink><NuxtLink to="/shop">TV &amp; Audio</NuxtLink><NuxtLink
                            to="/shop">Cameras</NuxtLink></div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileFashion"><span><i class="bi bi-bag"></i> Fashion &amp; Lifestyle</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileFashion"><NuxtLink to="/shop">Women's Fashion</NuxtLink><NuxtLink
                            to="/shop">Men's Fashion</NuxtLink><NuxtLink to="/shop">Watches</NuxtLink><NuxtLink to="/shop">Bags
                            &amp; Accessories</NuxtLink></div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileHome"><span><i class="bi bi-house-heart"></i> Home &amp; Living</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileHome"><NuxtLink to="/shop">Furniture</NuxtLink><NuxtLink to="/shop">Kitchen
                            Appliances</NuxtLink><NuxtLink to="/shop">Home Decor</NuxtLink><NuxtLink to="/shop">Lighting</NuxtLink></div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileMore"><span><i class="bi bi-grid"></i> More Categories</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileMore"><NuxtLink to="/shop">Sports &amp; Outdoor</NuxtLink><NuxtLink
                            to="/shop">Baby &amp; Kids</NuxtLink><NuxtLink to="/shop">Automotive</NuxtLink><NuxtLink
                            to="/shop">Books &amp; Media</NuxtLink></div>
                </div>
            </nav>
            <div class="mobile-nav-label">Discover</div>
            <div class="mobile-menu-links"><NuxtLink to="/shop">New Arrivals <i class="bi bi-arrow-right"></i></NuxtLink><NuxtLink
                    to="/shop">Best Sellers <i class="bi bi-arrow-right"></i></NuxtLink><NuxtLink to="/shop">Offers <i
                        class="bi bi-arrow-right"></i></NuxtLink><NuxtLink to="/compare">Compare <i
                        class="bi bi-arrow-right"></i></NuxtLink></div>
        </div>
        <div class="mobile-menu-footer"><i class="bi bi-headset"></i>
            <div><small>Need help?</small><strong>Customer support</strong></div>
        </div>
    </div>
    <div class="offcanvas offcanvas-start" id="categories">
        <div class="offcanvas-header">
            <h5>Categories</h5><button class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <template v-for="section in sections" :key="section.id">
                <h6>{{ section.name }}</h6>
                <NuxtLink v-for="category in section.categories" :key="category.id" class="d-block p-2"
                    :to="{ path: '/shop', query: { category: category.url } }">{{ category.category_name }}</NuxtLink>
                <hr>
            </template>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast">
            <div class="toast-header"><strong class="me-auto">NovaCart</strong><button class="btn-close"
                    data-bs-dismiss="toast"></button></div>
            <div class="toast-body">Added</div>
        </div>
    </div>
</template>

<style scoped>
.mobile-nav-group>button span img{width:20px;height:20px;margin-right:1px;border-radius:4px;object-fit:cover}
.mobile-search-results{max-height:260px;margin:-10px 0 14px;overflow-y:auto;border:1px solid #e5e9e7;border-radius:6px;background:#fff;box-shadow:0 10px 25px rgba(20,35,29,.12)}
.mobile-search-result{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:9px;align-items:center;padding:8px;border-bottom:1px solid #eef1ef;color:#17211d;text-decoration:none}
.mobile-search-result img,.mobile-search-image{width:42px;height:42px;object-fit:contain;border-radius:4px;background:#f4f6f5}
.mobile-search-image{display:grid;place-items:center}
.mobile-search-result small,.mobile-search-result strong{display:block}.mobile-search-result small{color:#919a96;font-size:.58rem}.mobile-search-result strong{overflow:hidden;font-size:.68rem;text-overflow:ellipsis;white-space:nowrap}.mobile-search-result b{color:#ff5a3c;font-size:.64rem;white-space:nowrap}
.mobile-search-message{padding:18px;text-align:center;color:#7d8782;font-size:.68rem}.mobile-search-all{width:100%;padding:10px;border:0;background:#f5f8f6;color:#17211d;font-size:.65rem;font-weight:700}
</style>
