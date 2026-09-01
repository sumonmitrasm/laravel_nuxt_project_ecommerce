<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})
</script>
<template>
  <main class="checkout-main">
        <div class="container">
            <div class="checkout-back"><a href="cart.html"><i class="bi bi-arrow-left"></i> Return to cart</a><span>Need
                    help? <a href="#">Contact support</a></span></div>
            <div class="row g-4 g-xl-5">
                <div class="col-lg-7">
                    <form class="checkout-form" id="checkoutForm" data-checkout-form="">
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>1</span>
                                <div>
                                    <h1>Contact information</h1>
                                    <p>We’ll send your receipt and delivery updates here.</p>
                                </div><a href="login.html">Already have an account? Log in</a>
                            </div>
                            <div class="field-grid"><label class="field full"><span>Email address</span><input
                                        type="email" name="email" placeholder="you@example.com" required><i
                                        class="bi bi-envelope"></i></label><label class="checkout-check full"><input
                                        type="checkbox" checked> Email me with news and offers</label></div>
                        </section>
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>2</span>
                                <div>
                                    <h2>Delivery address</h2>
                                    <p>Enter the address where you want your order delivered.</p>
                                </div>
                            </div>
                            <div class="field-grid"><label class="field"><span>First name</span><input name="firstName"
                                        autocomplete="given-name" required></label><label class="field"><span>Last
                                        name</span><input name="lastName" autocomplete="family-name"
                                        required></label><label class="field full"><span>Address</span><input
                                        name="address" placeholder="House number and street name"
                                        autocomplete="street-address" required><i
                                        class="bi bi-geo-alt"></i></label><label class="field full"><span>Apartment,
                                        suite, etc. <small>(optional)</small></span><input
                                        name="apartment"></label><label class="field"><span>City</span><input
                                        name="city" value="Dhaka" required></label><label
                                    class="field"><span>District</span><select name="district">
                                        <option>Dhaka</option>
                                        <option>Chattogram</option>
                                        <option>Sylhet</option>
                                        <option>Rajshahi</option>
                                        <option>Khulna</option>
                                    </select></label><label class="field"><span>Postal code</span><input name="postcode"
                                        inputmode="numeric" required></label><label
                                    class="field"><span>Phone</span><input name="phone" type="tel"
                                        placeholder="+880 1XXX-XXXXXX" required></label><label
                                    class="checkout-check full"><input type="checkbox"> Save this information for next
                                    time</label></div>
                        </section>
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>3</span>
                                <div>
                                    <h2>Shipping method</h2>
                                    <p>Choose how quickly you’d like to receive your order.</p>
                                </div>
                            </div>
                            <div class="checkout-options"><label class="selected"><input type="radio"
                                        name="checkoutShipping" value="0" checked><i
                                        class="bi bi-truck"></i><span><strong>Free delivery</strong><small>4–6 business
                                            days</small></span><b>Free</b></label><label><input type="radio"
                                        name="checkoutShipping" value="120"><i
                                        class="bi bi-box-seam"></i><span><strong>Standard delivery</strong><small>2–4
                                            business days</small></span><b>৳120</b></label><label><input type="radio"
                                        name="checkoutShipping" value="350"><i
                                        class="bi bi-lightning-charge"></i><span><strong>Express
                                            delivery</strong><small>Next business day</small></span><b>৳350</b></label>
                            </div>
                        </section>
                        <section class="checkout-section">
                            <div class="checkout-section-head"><span>4</span>
                                <div>
                                    <h2>Payment</h2>
                                    <p>All transactions are secure and encrypted.</p>
                                </div>
                                <div class="payment-logos"><b>VISA</b><b>MC</b></div>
                            </div>
                            <div class="checkout-options payment-methods"><label class="selected"><input type="radio"
                                        name="payment" value="card" checked><i
                                        class="bi bi-credit-card"></i><span><strong>Credit or debit
                                            card</strong><small>Visa, Mastercard</small></span></label>
                                <div class="card-fields"><label class="field full"><span>Card number</span><input
                                            inputmode="numeric" placeholder="0000 0000 0000 0000"><i
                                            class="bi bi-lock"></i></label><label class="field"><span>Expiry
                                            date</span><input placeholder="MM / YY"></label><label
                                        class="field"><span>Security code</span><input inputmode="numeric"
                                            placeholder="CVV"><i class="bi bi-question-circle"></i></label><label
                                        class="field full"><span>Name on card</span><input></label></div><label><input
                                        type="radio" name="payment" value="mobile"><i
                                        class="bi bi-phone"></i><span><strong>Mobile banking</strong><small>bKash or
                                            Nagad</small></span></label><label><input type="radio" name="payment"
                                        value="cod"><i class="bi bi-cash-stack"></i><span><strong>Cash on
                                            delivery</strong><small>Pay when your order arrives</small></span></label>
                            </div>
                        </section>
                        <button class="place-order-mobile d-lg-none" type="submit"><i class="bi bi-lock"></i> Place
                            order · <span data-checkout-mobile-total="">৳19,230</span></button>
                    </form>
                </div>
                <div class="col-lg-5">
                    <aside class="checkout-summary">
                        <h2>Order summary <span>3 items</span></h2>
                        <div class="checkout-products">
                            <div class="checkout-product"><span class="checkout-product-image"
                                    style="--x:2;--y:0"><b>1</b></span>
                                <div><a href="product.html">Pulse Wireless Headphones</a><small>Midnight</small></div>
                                <strong>৳8,490</strong>
                            </div>
                            <div class="checkout-product"><span class="checkout-product-image"
                                    style="--x:3;--y:0"><b>1</b></span>
                                <div><a href="product.html">Orbit Smart Watch S2</a><small>Graphite</small></div>
                                <strong>৳6,990</strong>
                            </div>
                            <div class="checkout-product"><span class="checkout-product-image"
                                    style="--x:2;--y:2"><b>1</b></span>
                                <div><a href="product.html">Aero Cycling Helmet</a><small>Medium</small></div>
                                <strong>৳3,750</strong>
                            </div>
                        </div>
                        <form class="checkout-coupon" data-checkout-coupon=""><input name="checkoutCoupon"
                                placeholder="Gift card or discount code"><button>Apply</button></form>
                        <div class="checkout-discount" data-checkout-discount=""></div>
                        <div class="checkout-totals">
                            <div><span>Subtotal</span><strong>৳19,230</strong></div>
                            <div><span>Shipping</span><strong data-checkout-shipping="">Free</strong></div>
                            <div class="checkout-grand-total"><span>Total <small>BDT</small></span><strong
                                    data-checkout-total="">৳19,230</strong></div>
                        </div><button class="place-order d-none d-lg-flex" type="submit" form="checkoutForm"
                            data-place-order=""><i class="bi bi-lock"></i> Place order</button>
                        <p class="checkout-terms">By placing your order, you agree to our <a href="#">Terms</a> and <a
                                href="#">Privacy Policy</a>.</p>
                        <div class="checkout-trust"><span><i class="bi bi-shield-check"></i><b>Secure
                                    payment</b><small>256-bit encryption</small></span><span><i
                                    class="bi bi-arrow-counterclockwise"></i><b>Easy returns</b><small>Within 7
                                    days</small></span><span><i class="bi bi-headset"></i><b>Here to
                                    help</b><small>Friendly support</small></span></div>
                    </aside>
                </div>
            </div>
        </div>
    </main>
    <div class="checkout-success" data-checkout-success="">
        <div><i class="bi bi-check2-circle"></i>
            <h2>Order placed successfully</h2>
            <p>Thank you! Your confirmation number is <strong>NC-20481</strong>.</p><a href="index.html">Continue
                shopping</a>
        </div>
    </div>
</template>