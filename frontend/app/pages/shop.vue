<script setup>
const route = useRoute()
const config = useRuntimeConfig()

const categoryUrl = computed(() => {
    const value = route.query.category

    if (Array.isArray(value)) {
        return value[0] ?? ''
    }

    return value?.toString() ?? ''
})

const currentPage = computed(() => {
    const value = Array.isArray(route.query.page) ? route.query.page[0] : route.query.page
    const page = Number.parseInt(value?.toString() ?? '1', 10)

    return Number.isInteger(page) && page > 0 ? page : 1
})

const {
    data,
    status,
    error,
    refresh
} = await useAsyncData(
    'shop-products',

    async () => {
        return await $fetch(
            categoryUrl.value
                ? `/listing/${encodeURIComponent(categoryUrl.value)}`
                : '/products',
            {
                baseURL: config.public.apiBase,
                query: { page: currentPage.value }
            }
        )
    },

    {
        watch: [categoryUrl, currentPage]
    }
)

const products = computed(() =>
    data.value?.products ?? []
)

const category = computed(() =>
    data.value?.categoryDetails ?? null
)

const breadcrumbs = computed(() =>
    data.value?.breadcrumbs ?? []
)

const pagination = computed(() => data.value?.pagination ?? {
    current_page: 1,
    last_page: 1,
    total: 0,
    from: null,
    to: null
})

const visiblePages = computed(() => {
    const last = pagination.value.last_page
    const current = pagination.value.current_page
    const start = Math.max(1, Math.min(current - 2, last - 4))
    const end = Math.min(last, start + 4)

    return Array.from({ length: Math.max(0, end - start + 1) }, (_, index) => start + index)
})

const pageLink = page => ({
    path: '/shop',
    query: {
        ...(categoryUrl.value ? { category: categoryUrl.value } : {}),
        ...(page > 1 ? { page } : {})
    }
})

const formatPrice = value => new Intl.NumberFormat('en-BD', {
    maximumFractionDigits: 2
}).format(Number(value) || 0)

const productBadge = product => {
    const createdAt = new Date(product.created_at).getTime()
    const thirtyDays = 30 * 24 * 60 * 60 * 1000

    if (Number.isFinite(createdAt) && Date.now() - createdAt <= thirtyDays) {
        return { text: 'New', className: 'new' }
    }

    if (String(product.is_featured).toLowerCase() === 'yes') {
        return { text: 'Top', className: 'top' }
    }

    if (Number(product.effective_discount) > 0) {
        return { text: 'Sale', className: 'sale' }
    }

    return null
}

// watch(products, (newProducts) => {
//   console.log("Updated products:", newProducts)
// }, { immediate: true })
</script>
<template>
    <main>
        <section class="shop-hero">
            <div class="container text-center">
                <h1>Grid 4 Columns</h1>
                <p>Shop</p>
            </div>
        </section>
        <div class="shop-breadcrumb">
            <div class="container">
                <NuxtLink to="/">Home</NuxtLink>
                <!-- <i class="bi bi-chevron-right"></i> -->
                <!-- <NuxtLink to="/shop">Shop</NuxtLink> -->
                <template v-for="(item, index) in breadcrumbs" :key="item.id">
                    <i class="bi bi-chevron-right"></i>
                    <span v-if="index === breadcrumbs.length - 1">{{ item.category_name }}</span>
                    <NuxtLink v-else :to="{ path: '/shop', query: { category: item.url } }">
                        {{ item.category_name }}
                    </NuxtLink>
                </template>
            </div>
        </div>
        <section class="shop-catalog container py-4 py-lg-5">
            <div class="row g-4">
                <aside class="col-lg-3 shop-sidebar" id="shopFilters">
                    <div class="shop-filter-head"><strong>Filters</strong><button type="reset">Clean All</button></div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterCategory"
                            aria-expanded="true">
                            Category <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterCategory">
                            <label><input type="checkbox" /> Electronics <span>24</span></label><label><input
                                    type="checkbox" /> Fashion <span>18</span></label><label><input type="checkbox" />
                                Home &amp; Living <span>12</span></label><label><input type="checkbox" /> Accessories
                                <span>9</span></label><label><input type="checkbox" /> Sports <span>7</span></label>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterSubcategory"
                            aria-expanded="true">
                            Subcategory <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterSubcategory">
                            <label><input type="checkbox" /> Smartphones <span>8</span></label><label><input
                                    type="checkbox" /> Computers <span>6</span></label><label><input type="checkbox" />
                                Audio <span>5</span></label><label><input type="checkbox" /> Cameras
                                <span>4</span></label><label><input type="checkbox" /> Wearables <span>6</span></label>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterBrand"
                            aria-expanded="true">
                            Brand <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterBrand">
                            <label><input type="checkbox" /> HP <span>8</span></label>
                            <label><input type="checkbox" /> Dell <span>6</span></label>
                            <label><input type="checkbox" /> Lenovo <span>5</span></label>
                            <label><input type="checkbox" /> Asus <span>4</span></label>
                            <label><input type="checkbox" /> Apple <span>6</span></label>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterPrice"
                            aria-expanded="true">
                            Price <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterPrice">
                            <div class="dual-price-filter" data-price-filter data-min="0" data-max="100000">
                                <strong class="price-filter-selection" data-price-output>৳0 – ৳100,000</strong>
                                <div class="dual-range">
                                    <div class="dual-range-rail"></div>
                                    <div class="dual-range-progress" data-price-progress></div>
                                    <input type="range" min="0" max="100000" step="500" value="0" data-price-min
                                        aria-label="Minimum price" />
                                    <input type="range" min="0" max="100000" step="500" value="100000" data-price-max
                                        aria-label="Maximum price" />
                                </div>
                                <button class="price-filter-reset" type="button" data-price-reset>
                                    Reset price range
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterRating">
                            Customer rating <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse" id="filterRating">
                            <label><input type="checkbox" /> <b class="filter-stars">★★★★★</b></label><label><input
                                    type="checkbox" /> <b class="filter-stars">★★★★</b> &amp; up</label>
                        </div>
                    </div>
                </aside>
                <div class="col-lg-9">
                    <div class="shop-toolbar">
                        <button class="mobile-filter-btn d-lg-none" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#mobileShopFilters">
                            <i class="bi bi-sliders"></i> Filters
                        </button>
                        <div>Showing <strong>{{ pagination.from ?? 0 }}–{{ pagination.to ?? 0 }}</strong> of
                            <strong>{{ pagination.total }}</strong> products</div>
                        <div class="shop-sort">
                            <label for="sortProducts">Sort by:</label><select id="sortProducts">
                                <option>Most Popular</option>
                                <option>Newest</option>
                                <option>Price: Low to High</option>
                                <option>Price: High to Low</option>
                            </select><button class="active" aria-label="Grid view">
                                <i class="bi bi-grid-3x3-gap-fill"></i></button><button aria-label="List view"><i
                                    class="bi bi-list"></i></button>
                        </div>
                    </div>
                    <div v-if="status === 'pending'">Loading products...</div>
                    <div v-else-if="error">Products could not be loaded.</div>
                    <div v-else-if="!products.length">No products found.</div>
                    <div v-else class="shop-products">
                        <article v-for="product in products" :key="product.id" class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" :style="{
                                    backgroundImage: product.image_url ? `url(${product.image_url})` : 'none',
                                    backgroundSize: 'contain',
                                    backgroundPosition: 'center',
                                    backgroundColor: '#faf7f4'
                                }"></span><em v-if="productBadge(product)" class="product-label"
                                    :class="productBadge(product).className">{{ productBadge(product).text }}</em><button class="shop-heart" type="button"><i
                                        class="bi bi-heart"></i></button><span class="shop-cart"><i
                                        class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>{{ product.category?.category_name ?? category?.category_name }}</small>
                                <h2>
                                    <NuxtLink :to="{ path: '/product', query: { id: product.id } }">{{ product.product_name }}</NuxtLink>
                                </h2>
                                <div class="shop-price">৳{{ formatPrice(product.final_price) }}
                                    <del v-if="Number(product.effective_discount) > 0">৳{{ formatPrice(product.product_price) }}</del>
                                </div>
                                <div class="shop-rating">★★★★★ <span>{{ product.brand?.name ?? '' }}</span></div>
                            </div>
                        </article>
                    </div>
                    <nav v-if="pagination.last_page > 1" class="shop-pagination" aria-label="Product pages">
                        <span v-if="pagination.current_page === 1" class="disabled" aria-disabled="true"><i
                                class="bi bi-chevron-left"></i></span>
                        <NuxtLink v-else :to="pageLink(pagination.current_page - 1)" aria-label="Previous page"><i
                                class="bi bi-chevron-left"></i></NuxtLink>
                        <NuxtLink v-for="page in visiblePages" :key="page" :class="{ active: page === pagination.current_page }"
                            :to="pageLink(page)">{{ page }}</NuxtLink>
                        <span v-if="pagination.current_page === pagination.last_page" class="disabled"
                            aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
                        <NuxtLink v-else :to="pageLink(pagination.current_page + 1)" aria-label="Next page"><i
                                class="bi bi-chevron-right"></i></NuxtLink>
                    </nav>
                </div>
            </div>
        </section>
    </main>

    <div class="offcanvas offcanvas-start shop-filter-drawer" id="mobileShopFilters">
        <div class="offcanvas-header">
            <h5>Filters</h5>
            <button class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="shop-filter-head"><strong>Filters</strong><button type="reset">Clean All</button></div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel0"
                    aria-expanded="true">
                    Category <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel0">
                    <label><input type="checkbox" /> Electronics <span>24</span></label><label><input type="checkbox" />
                        Fashion <span>18</span></label><label><input type="checkbox" /> Home &amp; Living
                        <span>12</span></label><label><input type="checkbox" /> Accessories
                        <span>9</span></label><label><input type="checkbox" /> Sports <span>7</span></label>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel1"
                    aria-expanded="true">
                    Subcategory <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel1">
                    <label><input type="checkbox" /> Smartphones <span>8</span></label><label><input type="checkbox" />
                        Computers <span>6</span></label><label><input type="checkbox" /> Audio
                        <span>5</span></label><label><input type="checkbox" /> Cameras
                        <span>4</span></label><label><input type="checkbox" /> Wearables <span>6</span></label>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel5"
                    aria-expanded="true">
                    Brand <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel5">
                    <label><input type="checkbox" /> HP <span>8</span></label>
                    <label><input type="checkbox" /> Dell <span>6</span></label>
                    <label><input type="checkbox" /> Lenovo <span>5</span></label>
                    <label><input type="checkbox" /> Asus <span>4</span></label>
                    <label><input type="checkbox" /> Apple <span>6</span></label>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel2"
                    aria-expanded="true">
                    Price <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel2">
                    <div class="dual-price-filter" data-price-filter data-min="0" data-max="100000">
                        <strong class="price-filter-selection" data-price-output>৳0 – ৳100,000</strong>
                        <div class="dual-range">
                            <div class="dual-range-rail"></div>
                            <div class="dual-range-progress" data-price-progress></div>
                            <input type="range" min="0" max="100000" step="500" value="0" data-price-min
                                aria-label="Minimum price" />
                            <input type="range" min="0" max="100000" step="500" value="100000" data-price-max
                                aria-label="Maximum price" />
                        </div>
                        <button class="price-filter-reset" type="button" data-price-reset>Reset price range</button>
                    </div>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel3">
                    Customer rating <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="mobileFilterPanel3">
                    <label><input type="checkbox" /> <b class="filter-stars">★★★★★</b></label><label><input
                            type="checkbox" /> <b class="filter-stars">★★★★</b> &amp; up</label>
                </div>
            </div>
        </div>
    </div>
</template>
