<script setup>
const router = useRouter()
const config = useRuntimeConfig()
const { data } = await useCatalogMenu()
const sections = computed(() => data.value?.categories ?? [])
const { cartCount, fetchCart } = useCart()
const { wishlistCount, fetchWishlist } = useWishlist()

const searchText = ref('')
const selectedCategory = ref('')
const suggestions = ref([])
const searching = ref(false)
const showSuggestions = ref(false)
let searchTimer

const money = value => `\u09F3${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})}`

watch([searchText, selectedCategory], ([term]) => {
    clearTimeout(searchTimer)
    const query = term.trim()

    if (query.length < 2) {
        suggestions.value = []
        searching.value = false
        return
    }

    searching.value = true
    searchTimer = setTimeout(async () => {
        const params = { q: query }

        if (selectedCategory.value) {
            params.category = selectedCategory.value
        }

        try {
            const response = await $fetch('/search', {
                baseURL: config.public.apiBase,
                query: params
            })
            suggestions.value = response.products ?? []
            showSuggestions.value = true
        } catch {
            suggestions.value = []
        } finally {
            searching.value = false
        }
    }, 350)
})

const submitSearch = () => {
    const params = {}
    const query = searchText.value.trim()

    if (query) {
        params.q = query
    }

    if (selectedCategory.value) {
        params.category = selectedCategory.value
    }

    showSuggestions.value = false
    router.push({ path: '/shop', query: params })
}

const closeSuggestions = () => setTimeout(() => { showSuggestions.value = false }, 150)

onBeforeUnmount(() => clearTimeout(searchTimer))
onMounted(() => {
    fetchCart().catch(error => console.error('Cart load error:', error))
    fetchWishlist().catch(() => null)
})
</script>

<template>
  <header>
     <div class="topbar py-2">
        <div class="container d-flex justify-content-between"><span>Free delivery over ৳3,000</span><span>Help Center ·
                Track Order</span></div>
    </div>
    <nav class="navbar navbar-expand-lg bg-white py-3 sticky-top shadow-sm">
        <div class="container"><button class="navbar-toggler border-0" data-bs-toggle="offcanvas"
                data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button><SiteLogo class="navbar-brand fs-3" />
            <form class="header-search-form d-none d-lg-flex flex-grow-1 mx-5" @submit.prevent="submitSearch">
                <div class="input-group">
                    <select v-model="selectedCategory" class="form-select flex-grow-0" aria-label="Search category">
                        <option value="">All categories</option>
                        <optgroup v-for="section in sections" :key="section.id" :label="section.name">
                            <option v-for="category in section.categories" :key="category.id" :value="category.url">{{ category.category_name }}</option>
                        </optgroup>
                    </select>
                    <input v-model="searchText" class="form-control" type="search" autocomplete="off"
                        placeholder="Search products, brands and categories" aria-label="Search products"
                        @focus="showSuggestions = searchText.trim().length >= 2" @blur="closeSuggestions">
                    <button class="btn btn-brand px-4" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                </div>
                <div v-if="showSuggestions" class="header-search-results">
                    <div v-if="searching" class="header-search-message">Searching...</div>
                    <template v-else-if="suggestions.length">
                        <NuxtLink v-for="product in suggestions" :key="product.id"
                            :to="{ path: '/product', query: { id: product.id } }" class="header-search-result">
                            <img v-if="product.image_url" :src="product.image_url" :alt="product.name">
                            <span v-else class="header-search-placeholder"><i class="bi bi-image"></i></span>
                            <span><small>{{ product.category_name }}</small><strong>{{ product.name }}</strong></span>
                            <b>{{ money(product.final_price) }}</b>
                        </NuxtLink>
                        <button class="header-search-all" type="submit">View all results <i class="bi bi-arrow-right"></i></button>
                    </template>
                    <div v-else class="header-search-message">No matching products found.</div>
                </div>
            </form>
            <div class="d-flex gap-2"><NuxtLink class="nav-icon d-none d-sm-grid" to="/login"><i
                        class="bi bi-person"></i></NuxtLink><NuxtLink class="nav-icon d-none d-sm-grid" to="/wishlist"><i
                        class="bi bi-heart"></i><span v-if="wishlistCount > 0" class="badge bg-danger rounded-pill">{{ wishlistCount }}</span></NuxtLink><NuxtLink
                    class="nav-icon" to="/cart"><i class="bi bi-cart3 fs-5"></i><span v-if="cartCount > 0"
                        class="badge bg-danger rounded-pill">{{ cartCount }}</span></NuxtLink></div>
        </div>
    </nav>
    <div class="category-bar bg-white">
        <div class="container d-flex align-items-center"><NuxtLink class="btn btn-brand rounded-0 px-4 py-3"
                to="/#heroCarousel"><i class="bi bi-grid me-2"></i>Browse Categories</NuxtLink>
            <ul class="nav">
                <li><NuxtLink class="nav-link text-dark" :to="{ path: '/shop', query: { sort: 'newest' } }">New Arrivals</NuxtLink></li>
                <li><NuxtLink class="nav-link text-dark" :to="{ path: '/shop', query: { sort: 'best_selling' } }">Best Sellers</NuxtLink></li>
                <li><NuxtLink class="nav-link text-dark" to="/shop">Offers</NuxtLink></li>
                <li><NuxtLink class="nav-link text-dark" to="/compare">Compare</NuxtLink></li>
                <li class="nav-item dropdown"><NuxtLink class="nav-link text-dark dropdown-toggle" to="/blog"
                        data-bs-toggle="dropdown" aria-expanded="false">Blog</NuxtLink>
                    <ul class="dropdown-menu blog-nav-menu">
                        <li><NuxtLink class="dropdown-item" to="/blog"><i class="bi bi-journal-richtext"></i> Blog</NuxtLink></li>
                        <li><NuxtLink class="dropdown-item" to="/tags"><i class="bi bi-tags"></i> Tags</NuxtLink></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
  </header>
</template>
