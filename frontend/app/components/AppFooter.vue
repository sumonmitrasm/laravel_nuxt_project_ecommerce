<template>
 <footer class="footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h3 class="text-white">NOVA<span class="brand-dot">CART</span></h3>
                    <p class="col-lg-8">An original Bootstrap 5 commerce UI foundation, ready for your backend.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Shop</h6><a class="d-block my-2" href="shop.html">All products</a><a
                        class="d-block my-2" href="wishlist.html">Wishlist</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Account</h6><a class="d-block my-2" href="login.html">Login</a><a
                        class="d-block my-2" href="register.html">Register</a>
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
        <div class="mobile-menu-head"><a class="mobile-menu-brand" href="index.html">NOVA<span>CART</span></a><button
                class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
        <div class="mobile-menu-body">
            <form class="mobile-search" action="shop.html"><input type="search" placeholder="Search products"><button
                    aria-label="Search"><i class="bi bi-search"></i></button></form>
            <div class="mobile-quick"><a href="login.html"><i class="bi bi-person"></i><span>Account</span></a><a
                    href="wishlist.html"><i class="bi bi-heart"></i><span>Wishlist</span></a><a href="cart.html"><i
                        class="bi bi-cart3"></i><span>Cart</span></a></div>
            <div class="mobile-nav-label">Shop by section</div>
            <nav class="mobile-nav">
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileElectronics" aria-expanded="true"><span><i class="bi bi-phone"></i>
                            Electronics</span><i class="bi bi-chevron-down"></i></button>
                    <div class="collapse show" id="mobileElectronics"><a href="shop.html">Smartphones</a><a
                            href="shop.html">Computers &amp; Laptop</a><a href="shop.html">TV &amp; Audio</a><a
                            href="shop.html">Cameras</a></div>
                </div>
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileFashion"><span><i class="bi bi-bag"></i> Fashion &amp; Lifestyle</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileFashion"><a href="shop.html">Women's Fashion</a><a
                            href="shop.html">Men's Fashion</a><a href="shop.html">Watches</a><a href="shop.html">Bags
                            &amp; Accessories</a></div>
                </div>
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileHome"><span><i class="bi bi-house-heart"></i> Home &amp; Living</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileHome"><a href="shop.html">Furniture</a><a href="shop.html">Kitchen
                            Appliances</a><a href="shop.html">Home Decor</a><a href="shop.html">Lighting</a></div>
                </div>
                <div class="mobile-nav-group"><button type="button" data-bs-toggle="collapse"
                        data-bs-target="#mobileMore"><span><i class="bi bi-grid"></i> More Categories</span><i
                            class="bi bi-chevron-down"></i></button>
                    <div class="collapse" id="mobileMore"><a href="shop.html">Sports &amp; Outdoor</a><a
                            href="shop.html">Baby &amp; Kids</a><a href="shop.html">Automotive</a><a
                            href="shop.html">Books &amp; Media</a></div>
                </div>
            </nav>
            <div class="mobile-nav-label">Discover</div>
            <div class="mobile-menu-links"><a href="shop.html">New Arrivals <i class="bi bi-arrow-right"></i></a><a
                    href="shop.html">Best Sellers <i class="bi bi-arrow-right"></i></a><a href="shop.html">Offers <i
                        class="bi bi-arrow-right"></i></a><a href="compare.html">Compare <i
                        class="bi bi-arrow-right"></i></a></div>
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
            <h6>Technology</h6><a class="d-block p-2" href="shop.html">Phones</a><a class="d-block p-2"
                href="shop.html">Computers</a><a class="d-block p-2" href="shop.html">Audio</a>
            <hr>
            <h6>Fashion</h6><a class="d-block p-2" href="shop.html">Men</a><a class="d-block p-2"
                href="shop.html">Women</a>
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