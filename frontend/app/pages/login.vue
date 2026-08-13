<template>
  <main class="account-main">
        <div class="account-shell">
            <aside class="account-visual">
                <div class="account-visual-content"><NuxtLink to="/"
                        class="account-mark">N<span>C</span></NuxtLink><small>Your shopping, simplified</small>
                    <h1>Everything you love, all in one place.</h1>
                    <p>Sign in to track orders, save favourites and enjoy a faster checkout experience.</p>
                    <div class="account-benefit-list"><span><i class="bi bi-box-seam"></i><b>Track every
                                order</b><small>Live updates from checkout to delivery</small></span><span><i
                                class="bi bi-heart"></i><b>Save your favourites</b><small>Keep products ready for
                                later</small></span><span><i class="bi bi-lightning-charge"></i><b>Checkout
                                faster</b><small>Securely save your delivery details</small></span></div>
                </div>
                <div class="account-visual-footer"><span><i class="bi bi-shield-check"></i> Secure &amp;
                        private</span><span>Trusted shopping experience</span></div>
            </aside>
            <section class="account-form-panel">
                <div class="account-form-wrap"><NuxtLink class="account-mobile-logo"
                        to="/">NOVA<span>CART</span></NuxtLink>
                    <div class="account-title"><small>Welcome back</small>
                        <h2>Sign in to your account</h2>
                        <p>Enter your details to continue to NovaCart.</p>
                    </div>
                    <form class="account-form" data-account-form="" data-account-mode="login"><label
                            class="account-field"><span>Email address</span>
                            <div><i class="bi bi-envelope"></i><input type="email" name="email"
                                    placeholder="you@example.com" autocomplete="email" required></div>
                        </label><label class="account-field"><span>Password</span>
                            <div><i class="bi bi-lock"></i><input type="password" name="password"
                                    placeholder="Enter your password" autocomplete="current-password" minlength="6"
                                    required><button type="button" data-password-toggle=""
                                    aria-label="Show password"><i class="bi bi-eye"></i></button></div>
                        </label>
                        <div class="account-form-row"><label><input type="checkbox" name="remember"> Remember
                                me</label><NuxtLink to="/contact">Forgot password?</NuxtLink></div><button class="account-submit"
                            type="submit"><span>Sign in</span><i class="bi bi-arrow-right"></i></button>
                        <div class="account-divider"><span>or continue with</span></div>
                        <div class="social-login"><button type="button"><b>G</b> Google</button><button type="button"><i
                                    class="bi bi-facebook"></i> Facebook</button></div>
                    </form>
                    <p class="account-switch">Don’t have an account? <NuxtLink to="/register">Create one for free</NuxtLink></p>
                    <div class="account-message" data-account-message=""></div>
                </div>
            </section>
        </div>
    </main>
</template>
