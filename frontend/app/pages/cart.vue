<template>
  <main>
        <section class="shop-hero cart-hero">
            <div class="container text-center">
                <h1>Shopping Cart</h1>
                <p>Shop</p>
            </div>
        </section>
        <div class="shop-breadcrumb">
            <div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><NuxtLink
                    to="/shop">Shop</NuxtLink><i class="bi bi-chevron-right"></i><span>Shopping Cart</span></div>
        </div>
        <section class="cart-layout container py-5">
            <div class="row g-4 g-xl-5">
                <div class="col-lg-8">
                    <div class="cart-table-head">
                        <span>Product</span><span>Price</span><span>Quantity</span><span>Total</span><span></span></div>
                    <div class="cart-items" data-cart-items="">
                        <article class="cart-item" data-cart-item="" data-price="8490">
                            <div class="cart-product"><span class="cart-product-image" style="--x:2;--y:0"></span>
                                <div><NuxtLink to="/product">Pulse Wireless Headphones</NuxtLink><small>Colour:
                                        Midnight</small><button type="button" data-move-wishlist=""><i
                                            class="bi bi-heart"></i> Move to wishlist</button></div>
                            </div>
                            <div class="cart-unit-price" data-label="Price">৳8,490</div>
                            <div class="cart-quantity qty"><button class="minus" type="button">−</button><input
                                    value="1" inputmode="numeric" aria-label="Quantity"><button class="plus"
                                    type="button">+</button></div>
                            <div class="cart-line-total" data-label="Total">৳8,490</div><button class="cart-remove"
                                type="button" data-remove-item="" aria-label="Remove product"><i
                                    class="bi bi-x-lg"></i></button>
                        </article>
                        <article class="cart-item" data-cart-item="" data-price="6990">
                            <div class="cart-product"><span class="cart-product-image" style="--x:3;--y:0"></span>
                                <div><NuxtLink to="/product">Orbit Smart Watch S2</NuxtLink><small>Colour:
                                        Graphite</small><button type="button" data-move-wishlist=""><i
                                            class="bi bi-heart"></i> Move to wishlist</button></div>
                            </div>
                            <div class="cart-unit-price" data-label="Price">৳6,990</div>
                            <div class="cart-quantity qty"><button class="minus" type="button">−</button><input
                                    value="1" inputmode="numeric" aria-label="Quantity"><button class="plus"
                                    type="button">+</button></div>
                            <div class="cart-line-total" data-label="Total">৳6,990</div><button class="cart-remove"
                                type="button" data-remove-item="" aria-label="Remove product"><i
                                    class="bi bi-x-lg"></i></button>
                        </article>
                        <article class="cart-item" data-cart-item="" data-price="3750">
                            <div class="cart-product"><span class="cart-product-image" style="--x:2;--y:2"></span>
                                <div><NuxtLink to="/product">Aero Cycling Helmet</NuxtLink><small>Size: Medium</small><button
                                        type="button" data-move-wishlist=""><i class="bi bi-heart"></i> Move to
                                        wishlist</button></div>
                            </div>
                            <div class="cart-unit-price" data-label="Price">৳3,750</div>
                            <div class="cart-quantity qty"><button class="minus" type="button">−</button><input
                                    value="1" inputmode="numeric" aria-label="Quantity"><button class="plus"
                                    type="button">+</button></div>
                            <div class="cart-line-total" data-label="Total">৳3,750</div><button class="cart-remove"
                                type="button" data-remove-item="" aria-label="Remove product"><i
                                    class="bi bi-x-lg"></i></button>
                        </article>
                    </div>
                    <div class="cart-empty" data-cart-empty=""><i class="bi bi-cart-x"></i>
                        <h2>Your cart is empty</h2>
                        <p>Looks like you haven’t added anything yet.</p><NuxtLink to="/shop">Start shopping</NuxtLink>
                    </div>
                    <div class="cart-tools">
                        <form data-coupon-form=""><input name="coupon" placeholder="Coupon code"><button
                                type="submit">Apply coupon <i class="bi bi-arrow-right"></i></button></form><button
                            type="button" data-update-cart="">Update cart <i class="bi bi-arrow-clockwise"></i></button>
                    </div><NuxtLink class="continue-shopping" to="/shop"><i class="bi bi-arrow-left"></i> Continue
                        shopping</NuxtLink>
                </div>
                <div class="col-lg-4">
                    <aside class="cart-summary">
                        <h2>Cart Total</h2>
                        <div class="summary-row"><span>Subtotal</span><strong data-cart-subtotal="">৳19,230</strong>
                        </div>
                        <div class="cart-shipping">
                            <h3>Shipping</h3><label><input type="radio" name="shipping" value="0" checked /><span>Free
                                    Shipping<small>Delivery in 4–6
                                        days</small></span><strong>৳0</strong></label><label><input type="radio"
                                    name="shipping" value="120"><span>Standard<small>Delivery in 2–4
                                        days</small></span><strong>৳120</strong></label><label><input type="radio"
                                    name="shipping" value="350"><span>Express<small>Delivery
                                        tomorrow</small></span><strong>৳350</strong></label>
                        </div><button class="estimate-address" type="button"><i
                                class="bi bi-geo-alt"></i><span>Delivering to <strong>Dhaka,
                                    Bangladesh</strong></span><u>Change</u></button>
                        <div class="coupon-success" data-coupon-message=""></div>
                        <div class="summary-total"><span>Total</span><strong data-cart-total="">৳19,230</strong></div><NuxtLink
                            class="cart-checkout" to="/checkout"><i class="bi bi-lock"></i> Proceed to
                            checkout</NuxtLink>
                        <div class="secure-note"><i class="bi bi-shield-check"></i> Secure checkout · Easy returns</div>
                    </aside>
                </div>
            </div>
        </section>
    </main>
</template>
