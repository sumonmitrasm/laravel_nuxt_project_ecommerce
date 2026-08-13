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
                    ><NuxtLink to="/login"><i class="bi bi-person"></i> Login</NuxtLink>
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
                ><NuxtLink class="navbar-brand fs-3" to="/">NOVA<span class="brand-dot">CART</span></NuxtLink>
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
                    <NuxtLink class="nav-icon d-none d-sm-grid" to="/shop" aria-label="Search products"
                        ><i class="bi bi-search"></i></NuxtLink
                    ><NuxtLink class="nav-icon d-none d-sm-grid" to="/wishlist"><i class="bi bi-heart"></i></NuxtLink
                    ><NuxtLink class="nav-icon" to="/cart"
                        ><i class="bi bi-cart3"></i><span class="badge bg-danger rounded-pill">3</span></NuxtLink
                    >
                </div>
            </div>
        </nav>
    </header>
</template>
