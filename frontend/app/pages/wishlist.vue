<template>
  <main>
        <section class="shop-hero wishlist-hero">
            <div class="container text-center">
                <h1>My Wishlist</h1>
                <p>Saved favourites</p>
            </div>
        </section>
        <div class="shop-breadcrumb">
            <div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><span>Wishlist</span>
            </div>
        </div>
        <section class="wishlist-layout container py-5">
            <div class="wishlist-intro">
                <div>
                    <h2>Saved for later <span data-wishlist-heading-count="">3 items</span></h2>
                    <p>Keep track of products you love and move them to your cart whenever you’re ready.</p>
                </div>
                <div><button type="button" data-share-wishlist=""><i class="bi bi-share"></i> Share
                        wishlist</button><button type="button" class="wishlist-add-all" data-add-all=""><i
                            class="bi bi-cart3"></i> Add all to cart</button></div>
            </div>
            <div class="wishlist-table-head"><span>Product</span><span>Price</span><span>Stock
                    status</span><span></span><span></span></div>
            <div class="wishlist-items" data-wishlist-items="">
                <article class="wishlist-item" data-wishlist-item="">
                    <div class="wishlist-product"><NuxtLink to="/product" class="wishlist-image"
                            style="--x:3;--y:0"><span class="wishlist-label top">Top</span></NuxtLink>
                        <div><small>Wearables</small><NuxtLink to="/product">Orbit Smart Watch S2</NuxtLink>
                            <div class="wishlist-rating">★★★★★ <span>(9 reviews)</span></div><button type="button"
                                data-wishlist-remove-mobile=""><i class="bi bi-trash3"></i> Remove</button>
                        </div>
                    </div>
                    <div class="wishlist-price">৳6,990</div>
                    <div class="wishlist-stock in-stock"><i class="bi bi-check-circle"></i> In stock</div><button
                        class="wishlist-cart" type="button" data-wishlist-cart=""><i class="bi bi-cart3"></i> Add to
                        cart</button><button class="wishlist-remove" type="button" data-wishlist-remove=""
                        aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                </article>
                <article class="wishlist-item" data-wishlist-item="">
                    <div class="wishlist-product"><NuxtLink to="/product" class="wishlist-image"
                            style="--x:1;--y:2"><span class="wishlist-label new">New</span></NuxtLink>
                        <div><small>Cameras</small><NuxtLink to="/product">Pocket Mirrorless Camera</NuxtLink>
                            <div class="wishlist-rating">★★★★★ <span>(7 reviews)</span></div><button type="button"
                                data-wishlist-remove-mobile=""><i class="bi bi-trash3"></i> Remove</button>
                        </div>
                    </div>
                    <div class="wishlist-price">৳42,800</div>
                    <div class="wishlist-stock in-stock"><i class="bi bi-check-circle"></i> In stock</div><button
                        class="wishlist-cart" type="button" data-wishlist-cart=""><i class="bi bi-cart3"></i> Add to
                        cart</button><button class="wishlist-remove" type="button" data-wishlist-remove=""
                        aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                </article>
                <article class="wishlist-item" data-wishlist-item="">
                    <div class="wishlist-product"><NuxtLink to="/product" class="wishlist-image"
                            style="--x:1;--y:1"><span class="wishlist-label sale">Sale</span></NuxtLink>
                        <div><small>Fashion</small><NuxtLink to="/product">Everyday Leather Tote</NuxtLink>
                            <div class="wishlist-rating">★★★★☆ <span>(5 reviews)</span></div><button type="button"
                                data-wishlist-remove-mobile=""><i class="bi bi-trash3"></i> Remove</button>
                        </div>
                    </div>
                    <div class="wishlist-price">৳4,850 <del>৳5,600</del></div>
                    <div class="wishlist-stock low-stock"><i class="bi bi-exclamation-circle"></i> Only 2 left</div>
                    <button class="wishlist-cart" type="button" data-wishlist-cart=""><i class="bi bi-cart3"></i> Add to
                        cart</button><button class="wishlist-remove" type="button" data-wishlist-remove=""
                        aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                </article>
            </div>
            <div class="wishlist-empty" data-wishlist-empty=""><i class="bi bi-heart"></i>
                <h2>Your wishlist is empty</h2>
                <p>Save products you love and find them here anytime.</p><NuxtLink to="/shop">Explore products</NuxtLink>
            </div>
            <div class="wishlist-footer"><NuxtLink to="/shop"><i class="bi bi-arrow-left"></i> Continue shopping</NuxtLink>
                <div><span>Share:</span><a href="#"><i class="bi bi-facebook"></i></a><a href="#"><i
                            class="bi bi-twitter-x"></i></a><a href="#"><i class="bi bi-whatsapp"></i></a></div>
            </div>
            <section class="wishlist-benefits"><span><i class="bi bi-truck"></i><b>Fast delivery</b><small>Across
                        Bangladesh</small></span><span><i class="bi bi-shield-check"></i><b>Secure
                        payment</b><small>Protected checkout</small></span><span><i
                        class="bi bi-arrow-counterclockwise"></i><b>Easy returns</b><small>Within 7
                        days</small></span><span><i class="bi bi-headset"></i><b>Helpful support</b><small>Whenever you
                        need us</small></span></section>
        </section>
    </main>
</template>
