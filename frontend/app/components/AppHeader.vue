<script setup>
const { cartCount, fetchCart } = useCart()
const { authLoaded, isAuthenticated, fetchUser } = useAuth()
const router = useRouter()
const config = useRuntimeConfig()
const searchOpen = ref(false)
const searchText = ref('')
const suggestions = ref([])
const searching = ref(false)
let searchTimer

const money = value => `\u09F3${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})}`

watch(searchText, term => {
    clearTimeout(searchTimer)
    const query = term.trim()

    if (query.length < 2) {
        suggestions.value = []
        searching.value = false
        return
    }

    searching.value = true
    searchTimer = setTimeout(async () => {
        try {
            const response = await $fetch('/search', {
                baseURL: config.public.apiBase,
                query: { q: query }
            })
            suggestions.value = response.products ?? []
        } catch {
            suggestions.value = []
        } finally {
            searching.value = false
        }
    }, 350)
})

const submitSearch = () => {
    const query = searchText.value.trim()

    if (!query) {
        return
    }

    searchOpen.value = false
    router.push({ path: '/shop', query: { q: query } })
}

if (!authLoaded.value) {
    await fetchUser().catch(() => null)
}

onBeforeUnmount(() => clearTimeout(searchTimer))
onMounted(() => fetchCart().catch(error => console.error('Cart load error:', error)))
</script>

<template>
    <header>
        <div class="shop-topbar">
            <div class="container">
                <span>USD <i class="bi bi-chevron-down"></i></span
                ><span>English <i class="bi bi-chevron-down"></i></span>
                <div class="ms-auto">
                    <a href="tel:+0123456789"><i class="bi bi-telephone"></i> Call: +0123 456 789</a
                    ><NuxtLink to="/wishlist"><i class="bi bi-heart"></i> Wishlist (2)</NuxtLink
                    ><NuxtLink class="topbar-link" to="/about">About us</NuxtLink
                    ><NuxtLink class="topbar-link" to="/contact">Contact us</NuxtLink
                    ><NuxtLink v-if="isAuthenticated" to="/account"><i class="bi bi-person"></i> My Account</NuxtLink
                    ><template v-else
                        ><NuxtLink to="/login"><i class="bi bi-box-arrow-in-right"></i> Login</NuxtLink
                        ><NuxtLink to="/register"><i class="bi bi-person-plus"></i> Register</NuxtLink
                    ></template>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg bg-white shop-navbar">
            <div class="container">
                <button
                    class="navbar-toggler border-0"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#menu"
                    aria-controls="menu"
                    aria-label="Open navigation menu"
                >
                    <span class="navbar-toggler-icon"></span></button
                ><SiteLogo class="navbar-brand fs-3" />
                <div class="shop-main-nav d-none d-lg-flex">
                    <NuxtLink to="/">Home</NuxtLink><NuxtLink to="/shop">Shop</NuxtLink
                    ><NuxtLink to="/product">Product</NuxtLink
                    ><button type="button" class="nav-placeholder">Pages</button
                    ><span class="shop-nav-dropdown"
                        ><NuxtLink to="/blog">Blog <i class="bi bi-chevron-down"></i></NuxtLink
                        ><span
                            ><NuxtLink to="/blog">Blog articles</NuxtLink
                            ><NuxtLink to="/tags">Browse tags</NuxtLink></span
                        ></span
                    >
                </div>
                <div class="ms-auto d-flex gap-2">
                    <div class="header-quick-search d-none d-sm-block">
                        <button class="nav-icon" type="button" aria-label="Search products" @click="searchOpen = !searchOpen">
                            <i class="bi" :class="searchOpen ? 'bi-x-lg' : 'bi-search'"></i>
                        </button>
                        <div v-if="searchOpen" class="header-quick-search-panel">
                            <form class="header-quick-search-form" @submit.prevent="submitSearch">
                                <input v-model="searchText" type="search" autocomplete="off" autofocus placeholder="What are you looking for?" aria-label="Search products">
                                <button type="submit" aria-label="Submit search"><i class="bi bi-search"></i></button>
                            </form>
                            <div v-if="searchText.trim().length >= 2" class="header-quick-results">
                                <div v-if="searching" class="header-quick-message">Searching...</div>
                                <template v-else-if="suggestions.length">
                                    <NuxtLink v-for="product in suggestions" :key="product.id"
                                        :to="{ path: '/product', query: { id: product.id } }" class="header-quick-result" @click="searchOpen = false">
                                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name">
                                        <span v-else class="header-quick-image"><i class="bi bi-image"></i></span>
                                        <span><small>{{ product.category_name }}</small><strong>{{ product.name }}</strong></span>
                                        <b>{{ money(product.final_price) }}</b>
                                    </NuxtLink>
                                    <button class="header-quick-all" type="button" @click="submitSearch">View all results <i class="bi bi-arrow-right"></i></button>
                                </template>
                                <div v-else class="header-quick-message">No matching products found.</div>
                            </div>
                        </div>
                    </div>
                    <NuxtLink class="nav-icon d-none d-sm-grid" to="/wishlist"><i class="bi bi-heart"></i></NuxtLink
                    ><NuxtLink class="nav-icon" to="/cart"
                        ><i class="bi bi-cart3"></i><span v-if="cartCount > 0" class="badge bg-danger rounded-pill">{{ cartCount }}</span></NuxtLink
                    >
                </div>
            </div>
        </nav>
    </header>
</template>
