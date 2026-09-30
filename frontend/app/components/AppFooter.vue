<script setup>
const config = useRuntimeConfig()
const { data } = useCatalogMenu()
const sections = computed(() => data.value?.categories ?? [])
const site = computed(() => data.value?.site ?? {})
const siteName = computed(() => site.value.name || 'Store')
const siteDescription = computed(() => site.value.description || `Shop with ${siteName.value}.`)
const copyrightYear = computed(() => site.value.copyright_year || new Date().getFullYear())
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

const handleMobileMenuClick = (event) => {
    if (event.target instanceof Element && event.target.closest('a')) {
        closeMobileMenu()
    }
}

const handleCategoryMenuClick = (event) => {
    if (event.target instanceof Element && event.target.closest('a')) {
        document.querySelector('#categories [data-bs-dismiss="offcanvas"]')?.click()
    }
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
    <footer class="footer site-footer">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-5">
                    <NuxtLink class="footer-brand" to="/" :aria-label="`${siteName} home`">
                        <img v-if="site.logo" :src="site.logo" :alt="siteName">
                        <strong v-else>{{ siteName }}</strong>
                    </NuxtLink>
                    <p class="footer-description">{{ siteDescription }}</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Shop</h6>
                    <NuxtLink class="d-block my-2" to="/shop">All products</NuxtLink>
                    <NuxtLink class="d-block my-2" to="/wishlist">Wishlist</NuxtLink>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Account</h6>
                    <NuxtLink class="d-block my-2" to="/login">Login</NuxtLink>
                    <NuxtLink class="d-block my-2" to="/register">Register</NuxtLink>
                </div>
                <div class="col-lg-3">
                    <h6>Contact</h6>
                    <a v-if="site.phone" class="footer-contact" :href="`tel:${site.phone}`"><i class="bi bi-telephone"></i>{{ site.phone }}</a>
                    <a v-if="site.email" class="footer-contact" :href="`mailto:${site.email}`"><i class="bi bi-envelope"></i>{{ site.email }}</a>
                    <p v-if="site.address" class="footer-contact"><i class="bi bi-geo-alt"></i>{{ site.address }}</p>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© {{ copyrightYear }} {{ siteName }}. All rights reserved.</span>
                <NuxtLink to="/contact">Contact us</NuxtLink>
            </div>
        </div>
    </footer>
    <div class="offcanvas offcanvas-start" id="menu" @click="handleMobileMenuClick">
        <div class="mobile-menu-head">
            <NuxtLink class="mobile-menu-brand" to="/">NOVA<span>CART</span></NuxtLink><button class="btn-close"
                type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="mobile-menu-body">
            <form class="mobile-search" @submit.prevent="submitMobileSearch">
                <input v-model="mobileSearchText" type="search" autocomplete="off" placeholder="Search products here..."
                    aria-label="Search products">
                <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
            </form>
            <div v-if="mobileSearchOpen" class="mobile-search-results">
                <div v-if="mobileSearching" class="mobile-search-message">Searching...</div>
                <template v-else-if="mobileSuggestions.length">
                    <NuxtLink v-for="product in mobileSuggestions" :key="product.id"
                        :to="`/product/${product.id}/${product.slug}`" class="mobile-search-result">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name">
                        <span v-else class="mobile-search-image"><i class="bi bi-image"></i></span>
                        <span><small>{{ product.category_name }}</small><strong>{{ product.name }}</strong></span>
                        <b>{{ money(product.final_price) }}</b>
                    </NuxtLink>
                    <button class="mobile-search-all" type="button" @click="submitMobileSearch">View all results <i
                            class="bi bi-arrow-right"></i></button>
                </template>
                <div v-else class="mobile-search-message">No matching products found.</div>
            </div>
            <div class="mobile-quick">
                <NuxtLink to="/login"><i class="bi bi-person"></i><span>Account</span></NuxtLink>
                <NuxtLink to="/wishlist"><i class="bi bi-heart"></i><span>Wishlist</span></NuxtLink>
                <NuxtLink to="/cart"><i class="bi bi-cart3"></i><span>Cart</span></NuxtLink>
            </div>
            <div class="mobile-nav-label">Shop by section</div>
            <nav class="mobile-nav">
                <div v-for="(section, index) in sections" :key="section.id" class="mobile-nav-group">
                    <button type="button" data-bs-toggle="collapse" :data-bs-target="`#${mobileSectionId(section)}`"
                        :aria-expanded="index === 0"><span>
                            <img v-if="section.image_url" :src="section.image_url" alt="" loading="lazy" decoding="async">
                            <i v-else class="bi bi-grid"></i> {{ section.name }}</span><i
                            class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse" :class="{ show: index === 0 }" :id="mobileSectionId(section)">
                        <template v-for="category in section.categories" :key="category.id">
                            <NuxtLink :to="{ path: '/shop', query: { category: category.url } }">{{
                                category.category_name }}</NuxtLink>
                            <NuxtLink v-for="subcategory in category.subcategories" :key="subcategory.id"
                                :to="{ path: '/shop', query: { category: subcategory.url } }">— {{
                                subcategory.category_name }}</NuxtLink>
                        </template>
                    </div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileElectronics" aria-expanded="true"><span><i class="bi bi-phone"></i>
                            Electronics</span><i class="bi bi-chevron-down"></i></button>
                    <div class="collapse show" id="mobileElectronics">
                        <NuxtLink to="/shop">Smartphones</NuxtLink>
                        <NuxtLink to="/shop">Computers &amp; Laptop</NuxtLink>
                        <NuxtLink to="/shop">TV &amp; Audio</NuxtLink>
                        <NuxtLink to="/shop">Cameras</NuxtLink>
                    </div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileFashion"><span><i class="bi bi-bag"></i> Fashion &amp; Lifestyle</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileFashion">
                        <NuxtLink to="/shop">Women's Fashion</NuxtLink>
                        <NuxtLink to="/shop">Men's Fashion</NuxtLink>
                        <NuxtLink to="/shop">Watches</NuxtLink>
                        <NuxtLink to="/shop">Bags
                            &amp; Accessories</NuxtLink>
                    </div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileHome"><span><i class="bi bi-house-heart"></i> Home &amp; Living</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileHome">
                        <NuxtLink to="/shop">Furniture</NuxtLink>
                        <NuxtLink to="/shop">Kitchen
                            Appliances</NuxtLink>
                        <NuxtLink to="/shop">Home Decor</NuxtLink>
                        <NuxtLink to="/shop">Lighting</NuxtLink>
                    </div>
                </div>
                <div v-if="!sections.length" class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileMore"><span><i class="bi bi-grid"></i> More Categories</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileMore">
                        <NuxtLink to="/shop">Sports &amp; Outdoor</NuxtLink>
                        <NuxtLink to="/shop">Baby &amp; Kids</NuxtLink>
                        <NuxtLink to="/shop">Automotive</NuxtLink>
                        <NuxtLink to="/shop">Books &amp; Media</NuxtLink>
                    </div>
                </div>
            </nav>
            <div class="mobile-nav-label">Discover</div>
            <div class="mobile-menu-links">
                <NuxtLink :to="{ path: '/shop', query: { sort: 'newest' } }">New Arrivals <i
                        class="bi bi-arrow-right"></i>
                </NuxtLink>
                <NuxtLink :to="{ path: '/shop', query: { sort: 'best_selling' } }">Best Sellers <i
                        class="bi bi-arrow-right"></i>
                </NuxtLink>
                <NuxtLink to="/shop">Shop <i class="bi bi-arrow-right"></i></NuxtLink>
                <!-- <NuxtLink to="/compare">Compare <i class="bi bi-arrow-right"></i></NuxtLink> -->
            </div>
        </div>
        <div class="mobile-menu-footer"><i class="bi bi-headset"></i>
            <div><small>Need help?</small><strong>Customer support</strong></div>
        </div>
    </div>
    <div class="offcanvas offcanvas-start" id="categories" @click="handleCategoryMenuClick">
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
            <div class="toast-header"><strong class="me-auto">{{ siteName }}</strong><button class="btn-close"
                    data-bs-dismiss="toast"></button></div>
            <div class="toast-body">Added</div>
        </div>
    </div>
</template>

<style scoped>
.site-footer {
    padding: 52px 0 22px;
    border-top: 3px solid var(--brand);
}

.footer-brand {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    color: #fff;
    font-size: 1.55rem;
    text-decoration: none;
}

.footer-brand img {
    display: block;
    width: auto;
    max-width: 210px;
    height: 52px;
    object-fit: contain;
}

.footer-description {
    max-width: 410px;
    margin: 15px 0 0;
    color: #b9c5bf;
    font-size: .9rem;
    line-height: 1.7;
}

.site-footer h6 {
    margin-bottom: 14px;
    color: #fff;
    font-size: .9rem;
    font-weight: 700;
}

.site-footer a {
    font-size: .86rem;
}

.footer-contact {
    display: flex;
    gap: 9px;
    align-items: flex-start;
    margin: 0 0 11px;
    color: #c9d1cd;
    font-size: .86rem;
    line-height: 1.5;
    text-decoration: none;
    word-break: break-word;
}

.footer-contact i {
    flex: 0 0 auto;
    margin-top: 3px;
    color: var(--brand);
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin-top: 36px;
    padding-top: 18px;
    border-top: 1px solid rgba(201, 209, 205, .18);
    color: #9eaaa4;
    font-size: .78rem;
}

.footer-bottom a {
    color: #fff;
    font-weight: 600;
}

@media (max-width: 575.98px) {
    .site-footer {
        padding: 34px 0 18px;
        text-align: center;
    }

    .footer-brand {
        justify-content: center;
        width: 100%;
    }

    .footer-brand img {
        max-width: 175px;
        height: 44px;
    }

    .footer-description {
        margin: 12px auto 0;
        font-size: .82rem;
    }

    .site-footer h6 {
        margin-top: 14px;
    }

    .footer-contact {
        justify-content: center;
        font-size: .8rem;
    }

    .footer-bottom {
        flex-direction: column;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
        padding-top: 15px;
        font-size: .72rem;
    }
}

.mobile-nav-group>button span img {
    width: 20px;
    height: 20px;
    margin-right: 1px;
    border-radius: 4px;
    object-fit: cover
}

.mobile-search-results {
    max-height: 260px;
    margin: -10px 0 14px;
    overflow-y: auto;
    border: 1px solid #e5e9e7;
    border-radius: 6px;
    background: #fff;
    box-shadow: 0 10px 25px rgba(20, 35, 29, .12)
}

.mobile-search-result {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr) auto;
    gap: 9px;
    align-items: center;
    padding: 8px;
    border-bottom: 1px solid #eef1ef;
    color: #17211d;
    text-decoration: none
}

.mobile-search-result img,
.mobile-search-image {
    width: 42px;
    height: 42px;
    object-fit: contain;
    border-radius: 4px;
    background: #f4f6f5
}

.mobile-search-image {
    display: grid;
    place-items: center
}

.mobile-search-result small,
.mobile-search-result strong {
    display: block
}

.mobile-search-result small {
    color: #919a96;
    font-size: .58rem
}

.mobile-search-result strong {
    overflow: hidden;
    font-size: .68rem;
    text-overflow: ellipsis;
    white-space: nowrap
}

.mobile-search-result b {
    color: #ff5a3c;
    font-size: .64rem;
    white-space: nowrap
}

.mobile-search-message {
    padding: 18px;
    text-align: center;
    color: #7d8782;
    font-size: .68rem
}

.mobile-search-all {
    width: 100%;
    padding: 10px;
    border: 0;
    background: #f5f8f6;
    color: #17211d;
    font-size: .65rem;
    font-weight: 700
}
</style>
