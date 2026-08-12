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
            <form class="mobile-search" action="shop.html"><input type="search" placeholder="Search products"><button
                    aria-label="Search"><i class="bi bi-search"></i></button></form>
            <div class="mobile-quick"><NuxtLink to="/login"><i class="bi bi-person"></i><span>Account</span></NuxtLink><NuxtLink
                    to="/wishlist"><i class="bi bi-heart"></i><span>Wishlist</span></NuxtLink><NuxtLink to="/cart"><i
                        class="bi bi-cart3"></i><span>Cart</span></NuxtLink></div>
            <div class="mobile-nav-label">Shop by section</div>
            <nav class="mobile-nav">
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileElectronics" aria-expanded="true"><span><i class="bi bi-phone"></i>
                            Electronics</span><i class="bi bi-chevron-down"></i></button>
                    <div class="collapse show" id="mobileElectronics"><NuxtLink to="/shop">Smartphones</NuxtLink><NuxtLink
                            to="/shop">Computers &amp; Laptop</NuxtLink><NuxtLink to="/shop">TV &amp; Audio</NuxtLink><NuxtLink
                            to="/shop">Cameras</NuxtLink></div>
                </div>
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileFashion"><span><i class="bi bi-bag"></i> Fashion &amp; Lifestyle</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileFashion"><NuxtLink to="/shop">Women's Fashion</NuxtLink><NuxtLink
                            to="/shop">Men's Fashion</NuxtLink><NuxtLink to="/shop">Watches</NuxtLink><NuxtLink to="/shop">Bags
                            &amp; Accessories</NuxtLink></div>
                </div>
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileHome"><span><i class="bi bi-house-heart"></i> Home &amp; Living</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileHome"><NuxtLink to="/shop">Furniture</NuxtLink><NuxtLink to="/shop">Kitchen
                            Appliances</NuxtLink><NuxtLink to="/shop">Home Decor</NuxtLink><NuxtLink to="/shop">Lighting</NuxtLink></div>
                </div>
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
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
            <h6>Technology</h6><NuxtLink class="d-block p-2" to="/shop">Phones</NuxtLink><NuxtLink class="d-block p-2"
                to="/shop">Computers</NuxtLink><NuxtLink class="d-block p-2" to="/shop">Audio</NuxtLink>
            <hr>
            <h6>Fashion</h6><NuxtLink class="d-block p-2" to="/shop">Men</NuxtLink><NuxtLink class="d-block p-2"
                to="/shop">Women</NuxtLink>
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