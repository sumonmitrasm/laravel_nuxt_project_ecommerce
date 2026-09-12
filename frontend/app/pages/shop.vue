<script setup>
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { isAuthenticated } = useAuth()
const { fetchWishlist, toggleWishlist, hasProduct } = useWishlist()
const { success: showSuccess, error: showError } = useToast()
const wishlistBusyId = ref(null)

const changeWishlist = async (productId) => {
    if (!isAuthenticated.value) {
        await navigateTo({ path: '/login', query: { redirect: '/shop' } })
        return
    }
    if (wishlistBusyId.value) return
    wishlistBusyId.value = productId
    const wasSaved = hasProduct(productId)
    try {
        await toggleWishlist(productId)
        showSuccess('Wishlist updated', wasSaved ? 'Product removed from your wishlist.' : 'Product saved to your wishlist.')
    } catch (error) {
        showError('Wishlist unavailable', error?.data?.message ?? 'Please try again.')
    } finally {
        wishlistBusyId.value = null
    }
}

onMounted(() => {
    if (isAuthenticated.value) fetchWishlist().catch(() => null)
})
const { data: catalogData } = await useCatalogMenu()
const siteName = computed(() => catalogData.value?.site?.name || 'NovaCart')
const searchTerm = computed(() => {
    const value = Array.isArray(route.query.q) ? route.query.q[0] : route.query.q
    return value?.toString().trim() ?? ''
})

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

const selectedBrandIds = computed(() => {
    const value = Array.isArray(route.query.brand)
        ? route.query.brand.join(',')
        : route.query.brand?.toString() ?? ''

    return [...new Set(value.split(',')
        .map(id => Number.parseInt(id, 10))
        .filter(id => Number.isInteger(id) && id > 0))]
})

const brandQuery = computed(() => selectedBrandIds.value.join(','))

const selectedAttributeValueIds = computed(() => {
    const value = Array.isArray(route.query.attribute)
        ? route.query.attribute.join(',')
        : route.query.attribute?.toString() ?? ''

    return [...new Set(value.split(',')
        .map(id => Number.parseInt(id, 10))
        .filter(id => Number.isInteger(id) && id > 0))]
})

const attributeQuery = computed(() => selectedAttributeValueIds.value.join(','))

const queryPrice = name => computed(() => {
    const value = Array.isArray(route.query[name]) ? route.query[name][0] : route.query[name]
    const number = Number(value)
    return Number.isFinite(number) && number >= 0 ? number : null
})

const selectedMinPrice = queryPrice('min_price')
const selectedMaxPrice = queryPrice('max_price')
const allowedSorts = ['popular', 'newest', 'best_selling', 'price_asc', 'price_desc']
const selectedSort = computed(() => {
    const value = Array.isArray(route.query.sort) ? route.query.sort[0] : route.query.sort
    return allowedSorts.includes(value) ? value : 'popular'
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
                query: {
                    page: currentPage.value,
                    ...(brandQuery.value ? { brand: brandQuery.value } : {}),
                    ...(attributeQuery.value ? { attribute: attributeQuery.value } : {}),
                    ...(selectedMinPrice.value !== null ? { min_price: selectedMinPrice.value } : {}),
                    ...(selectedMaxPrice.value !== null ? { max_price: selectedMaxPrice.value } : {}),
                    ...(selectedSort.value !== 'popular' ? { sort: selectedSort.value } : {}),
                    ...(searchTerm.value ? { q: searchTerm.value } : {})
                }
            }
        )
    },

    {
        watch: [categoryUrl, currentPage, brandQuery, attributeQuery, selectedMinPrice, selectedMaxPrice, selectedSort, searchTerm]
    }
)

const pageSeo = computed(() => data.value?.seo)

usePageSeo(pageSeo)

const products = computed(() =>
    data.value?.products ?? []
)

const category = computed(() =>
    data.value?.categoryDetails ?? null
)

const catalogSections = computed(() => catalogData.value?.categories ?? [])
const rootCategories = computed(() => catalogSections.value.flatMap(section => section.categories ?? []))

const categoryProductCount = item => Number(item.products_count ?? 0)
    + (item.subcategories ?? []).reduce((total, child) => total + categoryProductCount(child), 0)

const treeContainsUrl = (items, url) => items.some(item => item.url === url || treeContainsUrl(item.subcategories ?? [], url))
const activeSection = computed(() => catalogSections.value.find(section => treeContainsUrl(section.categories ?? [], categoryUrl.value)) ?? null)
const filterCategories = computed(() => activeSection.value?.categories ?? rootCategories.value)

const findCategoryContext = (items, url, parent = null) => {
    for (const item of items) {
        if (item.url === url) return { item, parent }
        const found = findCategoryContext(item.subcategories ?? [], url, item)
        if (found) return found
    }
    return null
}

const selectedCategoryContext = computed(() => findCategoryContext(filterCategories.value, categoryUrl.value))
const subcategories = computed(() => {
    const context = selectedCategoryContext.value
    if (context?.item?.subcategories?.length) return context.item.subcategories
    if (context?.parent?.subcategories?.length) return context.parent.subcategories
    return []
})

const showAllCategories = ref(false)
const showAllSubcategories = ref(false)
const visibleCategories = computed(() => showAllCategories.value ? filterCategories.value : filterCategories.value.slice(0, 5))
const visibleSubcategories = computed(() => showAllSubcategories.value ? subcategories.value : subcategories.value.slice(0, 5))
const hiddenCategoryCount = computed(() => Math.max(0, filterCategories.value.length - 5))
const hiddenSubcategoryCount = computed(() => Math.max(0, subcategories.value.length - 5))

watch(categoryUrl, () => {
    showAllCategories.value = false
    showAllSubcategories.value = false
})

const isCategorySelected = url => categoryUrl.value === url
const selectCategory = async url => {
    await replaceFilterQuery({ category: isCategorySelected(url) ? undefined : url, page: undefined })
}
const heroTitle = computed(() => {
    if (category.value) return category.value.category_name
    if (selectedSort.value === 'newest') return 'New Arrivals'
    if (selectedSort.value === 'best_selling') return 'Best Sellers'
    return 'Explore the Collection'
})
const heroSubtitle = computed(() => {
    if (category.value) return `Discover products selected from ${category.value.category_name}`
    if (selectedSort.value === 'newest') return 'Products added during the last 30 days'
    if (selectedSort.value === 'best_selling') return 'Products customers are buying most'
    return 'Fresh finds, thoughtful choices and something new for every day'
})

const breadcrumbs = computed(() =>
    data.value?.breadcrumbs ?? []
)

const brands = computed(() => data.value?.filters?.brands ?? [])
const attributeFilters = computed(() => data.value?.filters?.attributes ?? [])
const availablePrice = computed(() => data.value?.filters?.price ?? { min: 0, max: 0, step: 1 })
const priceFloor = computed(() => Number(availablePrice.value.min) || 0)
const priceStep = computed(() => Math.max(1, Number(availablePrice.value.step) || 1))
const priceCeiling = computed(() => Math.max(
    priceFloor.value + priceStep.value,
    Number(availablePrice.value.max) || 0
))
const draftMinPrice = ref(0)
const draftMaxPrice = ref(0)
let priceFilterTimer

watch(
    [priceFloor, priceCeiling, selectedMinPrice, selectedMaxPrice],
    ([floor, ceiling, minimum, maximum]) => {
        draftMinPrice.value = Math.min(ceiling, Math.max(floor, minimum ?? floor))
        draftMaxPrice.value = Math.max(draftMinPrice.value + priceStep.value, Math.min(ceiling, maximum ?? ceiling))
    },
    { immediate: true }
)

const priceProgressStyle = computed(() => {
    const range = Math.max(1, priceCeiling.value - priceFloor.value)
    return {
        '--range-left': `${((draftMinPrice.value - priceFloor.value) / range) * 100}%`,
        '--range-right': `${100 - ((draftMaxPrice.value - priceFloor.value) / range) * 100}%`
    }
})

const priceQueryParts = () => ({
    ...(draftMinPrice.value > priceFloor.value ? { min_price: draftMinPrice.value } : {}),
    ...(draftMaxPrice.value < priceCeiling.value ? { max_price: draftMaxPrice.value } : {})
})

const replaceFilterQuery = extra => router.replace({
    path: '/shop',
    query: {
        ...(categoryUrl.value ? { category: categoryUrl.value } : {}),
        ...(brandQuery.value ? { brand: brandQuery.value } : {}),
        ...(attributeQuery.value ? { attribute: attributeQuery.value } : {}),
        ...priceQueryParts(),
        ...(selectedSort.value !== 'popular' ? { sort: selectedSort.value } : {}),
        ...extra
    }
})

const updatePriceFilter = changed => {
    if (changed === 'min' && draftMaxPrice.value - draftMinPrice.value < priceStep.value) {
        draftMinPrice.value = draftMaxPrice.value - priceStep.value
    }
    if (changed === 'max' && draftMaxPrice.value - draftMinPrice.value < priceStep.value) {
        draftMaxPrice.value = draftMinPrice.value + priceStep.value
    }

    clearTimeout(priceFilterTimer)
    priceFilterTimer = setTimeout(() => replaceFilterQuery({}), 350)
}

const resetPriceFilter = async () => {
    clearTimeout(priceFilterTimer)
    draftMinPrice.value = priceFloor.value
    draftMaxPrice.value = priceCeiling.value
    await replaceFilterQuery({ min_price: undefined, max_price: undefined })
}

onBeforeUnmount(() => clearTimeout(priceFilterTimer))

const isBrandSelected = brandId => selectedBrandIds.value.includes(Number(brandId))

const toggleBrand = async brandId => {
    const id = Number(brandId)
    const next = isBrandSelected(id)
        ? selectedBrandIds.value.filter(selectedId => selectedId !== id)
        : [...selectedBrandIds.value, id]

    await router.replace({
        path: '/shop',
        query: {
            ...(categoryUrl.value ? { category: categoryUrl.value } : {}),
            ...(next.length ? { brand: next.join(',') } : {}),
            ...(attributeQuery.value ? { attribute: attributeQuery.value } : {}),
            ...priceQueryParts(),
            ...(selectedSort.value !== 'popular' ? { sort: selectedSort.value } : {})
        }
    })
}

const isAttributeValueSelected = valueId => selectedAttributeValueIds.value.includes(Number(valueId))

const toggleAttributeValue = async valueId => {
    const id = Number(valueId)
    const next = isAttributeValueSelected(id)
        ? selectedAttributeValueIds.value.filter(selectedId => selectedId !== id)
        : [...selectedAttributeValueIds.value, id]

    await router.replace({
        path: '/shop',
        query: {
            ...(categoryUrl.value ? { category: categoryUrl.value } : {}),
            ...(brandQuery.value ? { brand: brandQuery.value } : {}),
            ...(next.length ? { attribute: next.join(',') } : {}),
            ...priceQueryParts(),
            ...(selectedSort.value !== 'popular' ? { sort: selectedSort.value } : {})
        }
    })
}

const clearFilters = async () => {
    await router.replace({
        path: '/shop',
        query: {
            ...(categoryUrl.value ? { category: categoryUrl.value } : {}),
            ...(selectedSort.value !== 'popular' ? { sort: selectedSort.value } : {})
        }
    })
}

const updateSort = async event => {
    const sort = allowedSorts.includes(event.target.value) ? event.target.value : 'popular'
    await replaceFilterQuery({
        sort: sort === 'popular' ? undefined : sort,
        page: undefined
    })
}

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
        ...(brandQuery.value ? { brand: brandQuery.value } : {}),
        ...(attributeQuery.value ? { attribute: attributeQuery.value } : {}),
        ...(selectedMinPrice.value !== null ? { min_price: selectedMinPrice.value } : {}),
        ...(selectedMaxPrice.value !== null ? { max_price: selectedMaxPrice.value } : {}),
        ...(selectedSort.value !== 'popular' ? { sort: selectedSort.value } : {}),
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
        <section class="shop-hero ocean-hero">
            <div class="ocean-glow ocean-glow-one"></div><div class="ocean-glow ocean-glow-two"></div>
            <div class="ocean-bubble bubble-one"></div><div class="ocean-bubble bubble-two"></div><div class="ocean-bubble bubble-three"></div>
            <div class="container text-center ocean-content">
                <span class="ocean-kicker">{{ siteName }} COLLECTION</span>
                <h1>{{ heroTitle }}</h1>
                <p>{{ heroSubtitle }}</p>
            </div>
            <div class="ocean-wave wave-back"></div><div class="ocean-wave wave-front"></div>
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
                    <div class="shop-filter-head"><strong>Filters</strong><button type="button"
                            @click="clearFilters">Clean
                            All</button></div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterCategory" aria-expanded="true">Category <i class="bi bi-chevron-down"></i></button>
                        <div class="collapse show" id="filterCategory">
                            <label v-for="item in visibleCategories" :key="`desktop-category-${item.id}`">
                                <input type="checkbox" :checked="isCategorySelected(item.url)" @change="selectCategory(item.url)"> {{ item.category_name }}
                                <span>{{ categoryProductCount(item) }}</span>
                            </label>
                            <small v-if="!filterCategories.length" class="filter-empty">No category available</small>
                    <button v-if="hiddenCategoryCount" type="button" class="filter-more" @click="showAllCategories = !showAllCategories">{{ showAllCategories ? 'Show less' : `+ ${hiddenCategoryCount} more` }}</button>
                            <button v-if="hiddenCategoryCount" type="button" class="filter-more" @click="showAllCategories = !showAllCategories">{{ showAllCategories ? 'Show less' : `+ ${hiddenCategoryCount} more` }}</button>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterSubcategory" aria-expanded="true">Subcategory <i class="bi bi-chevron-down"></i></button>
                        <div class="collapse show" id="filterSubcategory">
                            <label v-for="item in visibleSubcategories" :key="`desktop-subcategory-${item.id}`">
                                <input type="checkbox" :checked="isCategorySelected(item.url)" @change="selectCategory(item.url)"> {{ item.category_name }}
                                <span>{{ categoryProductCount(item) }}</span>
                            </label>
                            <small v-if="!subcategories.length" class="filter-empty">No subcategory available</small>
                    <button v-if="hiddenSubcategoryCount" type="button" class="filter-more" @click="showAllSubcategories = !showAllSubcategories">{{ showAllSubcategories ? 'Show less' : `+ ${hiddenSubcategoryCount} more` }}</button>
                            <button v-if="hiddenSubcategoryCount" type="button" class="filter-more" @click="showAllSubcategories = !showAllSubcategories">{{ showAllSubcategories ? 'Show less' : `+ ${hiddenSubcategoryCount} more` }}</button>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterBrand"
                            aria-expanded="true">
                            Brand <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterBrand">
                            <label v-for="brand in brands" :key="`desktop-brand-${brand.id}`">
                                <input type="checkbox" :checked="isBrandSelected(brand.id)"
                                    @change="toggleBrand(brand.id)" />
                                {{ brand.name }}
                                <span>{{ brand.product_count }}</span>
                            </label>
                        </div>
                    </div>
                    <div v-for="attribute in attributeFilters" :key="`desktop-attribute-${attribute.id}`"
                        class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse"
                            :data-bs-target="`#filterAttribute${attribute.id}`" aria-expanded="true">
                            {{ attribute.name }} <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" :id="`filterAttribute${attribute.id}`">
                            <label v-for="value in attribute.values" :key="`desktop-value-${value.id}`">
                                <input type="checkbox" :checked="isAttributeValueSelected(value.id)"
                                    @change="toggleAttributeValue(value.id)" /> {{ value.value }}
                                <span>{{ value.product_count }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterPrice"
                            aria-expanded="true">
                            Price <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterPrice">
                            <div class="dual-price-filter">
                                <strong class="price-filter-selection">৳{{ formatPrice(draftMinPrice) }} – ৳{{
                                    formatPrice(draftMaxPrice) }}</strong>
                                <div class="dual-range">
                                    <div class="dual-range-rail"></div>
                                    <div class="dual-range-progress" :style="priceProgressStyle"></div>
                                    <input v-model.number="draftMinPrice" type="range" :min="priceFloor"
                                        :max="priceCeiling" :step="priceStep" aria-label="Minimum price"
                                        @input="updatePriceFilter('min')" />
                                    <input v-model.number="draftMaxPrice" type="range" :min="priceFloor"
                                        :max="priceCeiling" :step="priceStep" aria-label="Maximum price"
                                        @input="updatePriceFilter('max')" />
                                </div>
                                <button class="price-filter-reset" type="button" @click="resetPriceFilter">
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
                            <strong>{{ pagination.total }}</strong> products
                        </div>
                        <div class="shop-sort">
                            <label for="sortProducts">Sort by:</label><select id="sortProducts" :value="selectedSort"
                                @change="updateSort">
                                <option value="popular">Most Popular</option>
                                <option value="newest">New Arrivals</option>
                                <option value="best_selling">Best Selling</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                            </select><button class="active" aria-label="Grid view">
                                <i class="bi bi-grid-3x3-gap-fill"></i></button><button aria-label="List view"><i
                                    class="bi bi-list"></i></button>
                        </div>
                    </div>
                    <div v-if="status === 'pending' && !data">Loading products...</div>
                    <div v-else-if="error">Products could not be loaded.</div>
                    <div v-else-if="!products.length">No products found.</div>
                    <div v-else class="shop-products">
                        <article v-for="product in products" :key="product.id" class="shop-product">
                            <div class="shop-product-media">
                                <NuxtLink class="shop-product-photo" :to="{ path: '/product', query: { id: product.id } }" :style="{
                                    backgroundImage: product.image_url ? `url(${product.image_url})` : 'none',
                                    backgroundSize: 'contain',
                                    backgroundPosition: 'center',
                                    backgroundColor: '#faf7f4'
                                }"></NuxtLink><em v-if="productBadge(product)" class="product-label"
                                    :class="productBadge(product).className">{{ productBadge(product).text
                                    }}</em><button class="shop-heart" :class="{ active: hasProduct(product.id) }" type="button" :disabled="wishlistBusyId === product.id" :aria-label="hasProduct(product.id) ? 'Remove from wishlist' : 'Add to wishlist'" @click="changeWishlist(product.id)">
                                    <i class="bi" :class="hasProduct(product.id) ? 'bi-heart-fill' : 'bi-heart'"></i>
                                </button><NuxtLink class="shop-cart"
                                    :to="{ path: '/product', query: { id: product.id } }"><i
                                        class="bi bi-eye"></i> View product</NuxtLink>
                            </div>
                            <div class="shop-product-info">
                                <small>{{ product.category?.category_name ?? category?.category_name }}</small>
                                <h2>
                                    <NuxtLink :to="{ path: '/product', query: { id: product.id } }">{{
                                        product.product_name }}
                                    </NuxtLink>
                                </h2>
                                <div class="shop-price"><small v-if="product.has_variant_pricing">From </small>৳{{
                                    formatPrice(product.listing_final_price ?? product.final_price) }}
                                    <del v-if="Number(product.effective_discount) > 0">৳{{
                                        formatPrice(product.listing_regular_price ?? product.product_price) }}</del>
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
                        <NuxtLink v-for="page in visiblePages" :key="page"
                            :class="{ active: page === pagination.current_page }" :to="pageLink(page)">{{ page }}
                        </NuxtLink>
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
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel0" aria-expanded="true">Category <i class="bi bi-chevron-down"></i></button>
                <div class="collapse show" id="mobileFilterPanel0">
                    <label v-for="item in visibleCategories" :key="`mobile-category-${item.id}`"><input type="checkbox" :checked="isCategorySelected(item.url)" @change="selectCategory(item.url)"> {{ item.category_name }} <span>{{ categoryProductCount(item) }}</span></label>
                    <small v-if="!filterCategories.length" class="filter-empty">No category available</small>
                    <button v-if="hiddenCategoryCount" type="button" class="filter-more" @click="showAllCategories = !showAllCategories">{{ showAllCategories ? 'Show less' : `+ ${hiddenCategoryCount} more` }}</button>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel1" aria-expanded="true">Subcategory <i class="bi bi-chevron-down"></i></button>
                <div class="collapse show" id="mobileFilterPanel1">
                    <label v-for="item in visibleSubcategories" :key="`mobile-subcategory-${item.id}`"><input type="checkbox" :checked="isCategorySelected(item.url)" @change="selectCategory(item.url)"> {{ item.category_name }} <span>{{ categoryProductCount(item) }}</span></label>
                    <small v-if="!subcategories.length" class="filter-empty">No subcategory available</small>
                    <button v-if="hiddenSubcategoryCount" type="button" class="filter-more" @click="showAllSubcategories = !showAllSubcategories">{{ showAllSubcategories ? 'Show less' : `+ ${hiddenSubcategoryCount} more` }}</button>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel5"
                    aria-expanded="true">
                    Brand <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel5">
                    <label v-for="brand in brands" :key="`mobile-brand-${brand.id}`">
                        <input type="checkbox" :checked="isBrandSelected(brand.id)" @change="toggleBrand(brand.id)" />
                        {{
                        brand.name }}
                        <span>{{ brand.product_count }}</span>
                    </label>
                </div>
            </div>
            <div v-for="attribute in attributeFilters" :key="`mobile-attribute-${attribute.id}`"
                class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse"
                    :data-bs-target="`#mobileAttribute${attribute.id}`" aria-expanded="true">
                    {{ attribute.name }} <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" :id="`mobileAttribute${attribute.id}`">
                    <label v-for="value in attribute.values" :key="`mobile-value-${value.id}`">
                        <input type="checkbox" :checked="isAttributeValueSelected(value.id)"
                            @change="toggleAttributeValue(value.id)" /> {{ value.value }}
                        <span>{{ value.product_count }}</span>
                    </label>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel2"
                    aria-expanded="true">
                    Price <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel2">
                    <div class="dual-price-filter">
                        <strong class="price-filter-selection">৳{{ formatPrice(draftMinPrice) }} – ৳{{
                            formatPrice(draftMaxPrice) }}</strong>
                        <div class="dual-range">
                            <div class="dual-range-rail"></div>
                            <div class="dual-range-progress" :style="priceProgressStyle"></div>
                            <input v-model.number="draftMinPrice" type="range" :min="priceFloor" :max="priceCeiling"
                                :step="priceStep" aria-label="Minimum price" @input="updatePriceFilter('min')" />
                            <input v-model.number="draftMaxPrice" type="range" :min="priceFloor" :max="priceCeiling"
                                :step="priceStep" aria-label="Maximum price" @input="updatePriceFilter('max')" />
                        </div>
                        <button class="price-filter-reset" type="button" @click="resetPriceFilter">Reset price
                            range</button>
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
<style scoped>
.ocean-hero { position:relative; min-height:250px; padding:0; overflow:hidden; display:grid; place-items:center; isolation:isolate; background:linear-gradient(118deg,#041f32 0%,#064d68 36%,#0787a6 69%,#45c4ce 100%); box-shadow:inset 0 -1px rgba(255,255,255,.25); }
.ocean-hero::before { content:""; position:absolute; z-index:0; inset:-45% -10%; opacity:.2; background:repeating-radial-gradient(ellipse at 50% 100%,transparent 0 24px,rgba(170,244,255,.35) 26px 27px,transparent 29px 54px); transform:perspective(500px) rotateX(58deg) scale(1.15); animation:waterCaustics 12s linear infinite; }
.ocean-hero::after { content:""; position:absolute; z-index:1; inset:0; pointer-events:none; background:linear-gradient(90deg,rgba(1,18,31,.28),transparent 32%,transparent 68%,rgba(7,106,124,.05)),linear-gradient(180deg,rgba(255,255,255,.08),transparent 40%); }
.ocean-content { position:relative; z-index:5; width:min(720px,calc(100% - 40px)); padding:42px 30px 78px; color:#fff; }
.ocean-content::before { content:""; position:absolute; z-index:-1; inset:25px 0 58px; border:1px solid rgba(255,255,255,.13); border-radius:22px; background:linear-gradient(135deg,rgba(255,255,255,.09),rgba(255,255,255,.025)); box-shadow:0 22px 60px rgba(0,24,38,.14); backdrop-filter:blur(2px); }
.ocean-kicker { display:inline-flex; align-items:center; gap:8px; margin-bottom:10px; border:1px solid rgba(202,250,255,.25); border-radius:999px; background:rgba(4,46,65,.22); padding:6px 12px; letter-spacing:.22em; font-size:.62rem; font-weight:800; color:#c9f8ff; }
.ocean-kicker::before { content:""; width:5px; height:5px; border-radius:50%; background:#7cf0e6; box-shadow:0 0 0 5px rgba(124,240,230,.12); }
.ocean-hero h1 { margin:0; color:#fff; font-size:clamp(2.15rem,4vw,3.6rem); font-weight:760; letter-spacing:-.035em; text-shadow:0 9px 30px rgba(0,20,34,.3); }
.ocean-hero p { max-width:600px; margin:9px auto 0; color:rgba(235,253,255,.82); font-size:.88rem; letter-spacing:.01em; }
.ocean-glow { position:absolute; z-index:1; border-radius:50%; filter:blur(1px); background:radial-gradient(circle,rgba(171,248,255,.32),rgba(116,232,245,.08) 38%,transparent 70%); animation:oceanFloat 8s ease-in-out infinite; }
.ocean-glow-one { width:430px; height:430px; left:-80px; top:-290px; }.ocean-glow-two { width:370px; height:370px; right:-40px; bottom:-245px; animation-delay:-3s; }
.ocean-wave { position:absolute; left:-15%; width:130%; transform-origin:center bottom; will-change:transform; }
.wave-back { z-index:3; bottom:-91px; height:122px; border-radius:47% 54% 0 0 / 43% 51% 0 0; background:linear-gradient(90deg,rgba(147,230,239,.3),rgba(180,246,247,.48)); animation:oceanWaveBack 8s ease-in-out infinite alternate; }
.wave-front { z-index:4; bottom:-112px; height:128px; border-radius:52% 43% 0 0 / 45% 48% 0 0; background:#f7f9f8; box-shadow:0 -8px 28px rgba(147,240,242,.12); animation:oceanWaveFront 6s ease-in-out -2s infinite alternate; }
.ocean-bubble { position:absolute; z-index:2; border:1px solid rgba(218,252,255,.38); border-radius:50%; box-shadow:inset 2px 2px 4px rgba(255,255,255,.15); animation:bubbleRise 9s linear infinite; }
.bubble-one{width:12px;height:12px;left:19%;bottom:-20px}.bubble-two{width:7px;height:7px;left:74%;bottom:-20px;animation-delay:-4s}.bubble-three{width:17px;height:17px;left:89%;bottom:-20px;animation-delay:-6s}.filter-empty { display:block; padding:8px 0; color:#88938d; font-size:.7rem; }
.filter-more { width:100%; margin-top:7px; border:1px dashed #b9c8c1; border-radius:5px; background:#f7faf8; padding:8px 10px; color:#e6513d; font-size:.69rem; font-weight:750; text-align:left; transition:.2s ease; }
.filter-more:hover { border-color:#ff7967; background:#fff3f0; }
@keyframes oceanWaveBack { from{transform:translateX(-6%) scaleY(.94)} to{transform:translateX(6%) scaleY(1.05)} }
@keyframes oceanWaveFront { from{transform:translateX(6%) scaleY(.96)} to{transform:translateX(-6%) scaleY(1.04)} }
@keyframes waterCaustics { from{transform:perspective(500px) rotateX(58deg) translateX(-18px) scale(1.15)} to{transform:perspective(500px) rotateX(58deg) translateX(36px) scale(1.15)} }
@keyframes oceanFloat { 0%,100%{transform:translateY(0) scale(1)} 50%{transform:translateY(18px) scale(1.08)} }
@keyframes bubbleRise { 0%{transform:translateY(0);opacity:0} 15%{opacity:.7} 100%{transform:translateY(-270px);opacity:0} }
@media (prefers-reduced-motion:reduce){.ocean-wave,.ocean-glow,.ocean-bubble{animation:none}}
@media(max-width:575px){.ocean-hero{min-height:210px;padding:0}.ocean-content{width:calc(100% - 24px);padding:34px 18px 65px}.ocean-content::before{inset:19px 0 49px;border-radius:16px}.ocean-kicker{font-size:.55rem}.ocean-hero p{font-size:.76rem;padding:0 10px}}
</style>