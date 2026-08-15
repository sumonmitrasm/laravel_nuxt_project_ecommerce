<script setup>
const { data } = await useCatalogMenu()
const sections = computed(() => data.value?.categories ?? [])
</script>

<template>
  <header>
     <div class="topbar py-2">
        <div class="container d-flex justify-content-between"><span>Free delivery over ৳3,000</span><span>Help Center ·
                Track Order</span></div>
    </div>
    <nav class="navbar navbar-expand-lg bg-white py-3 sticky-top shadow-sm">
        <div class="container"><button class="navbar-toggler border-0" data-bs-toggle="offcanvas"
                data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button><NuxtLink class="navbar-brand fs-3"
                to="/">NOVA<span class="brand-dot">CART</span></NuxtLink>
            <form class="d-none d-lg-flex flex-grow-1 mx-5">
                <div class="input-group"><select class="form-select flex-grow-0" style="width:150px">
                        <option value="">All categories</option>
                        <optgroup v-for="section in sections" :key="section.id" :label="section.name">
                            <option v-for="category in section.categories" :key="category.id" :value="category.url">
                                {{ category.category_name }}
                            </option>
                        </optgroup>
                    </select><input class="form-control" placeholder="Search products, brands and categories"><button
                        class="btn btn-brand px-4"><i class="bi bi-search"></i></button></div>
            </form>
            <div class="d-flex gap-2"><NuxtLink class="nav-icon d-none d-sm-grid" to="/login"><i
                        class="bi bi-person"></i></NuxtLink><NuxtLink class="nav-icon d-none d-sm-grid" to="/wishlist"><i
                        class="bi bi-heart"></i><span class="badge bg-danger rounded-pill">2</span></NuxtLink><NuxtLink
                    class="nav-icon" to="/cart"><i class="bi bi-cart3 fs-5"></i><span
                        class="badge bg-danger rounded-pill">3</span></NuxtLink></div>
        </div>
    </nav>
    <div class="category-bar bg-white">
        <div class="container d-flex align-items-center"><NuxtLink class="btn btn-brand rounded-0 px-4 py-3"
                to="/#heroCarousel"><i class="bi bi-grid me-2"></i>Browse Categories</NuxtLink>
            <ul class="nav">
                <li><NuxtLink class="nav-link text-dark" to="/shop">New Arrivals</NuxtLink></li>
                <li><NuxtLink class="nav-link text-dark" to="/shop">Best Sellers</NuxtLink></li>
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
