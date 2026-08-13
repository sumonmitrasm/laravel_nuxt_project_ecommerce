<script setup lang="ts">
const productPage = ref<HTMLElement | null>(null)
const interactionCleanups: Array<() => void> = []

onBeforeUnmount(() => {
    interactionCleanups.splice(0).forEach(cleanup => cleanup())
})

onMounted(() => {
    const page = productPage.value
    if (!page) return

    const mainImage = page.querySelector<HTMLImageElement>('[data-product-main]')
    const zoomContainer = page.querySelector<HTMLElement>('[data-zoom-container]')
    const modal = document.querySelector<HTMLElement>('[data-zoom-modal]')
    const modalImage = modal?.querySelector<HTMLImageElement>('[data-zoom-image]')
    interactionCleanups.splice(0).forEach(cleanup => cleanup())

    const listen = (element: EventTarget | null | undefined, event: string, handler: EventListener) => {
        if (!element) return
        element.addEventListener(event, handler)
        interactionCleanups.push(() => element.removeEventListener(event, handler))
    }

    page.querySelectorAll<HTMLElement>('[data-product-image]').forEach((button) => {
        listen(button, 'click', () => {
            page.querySelectorAll('[data-product-image]').forEach(item => item.classList.remove('active'))
            button.classList.add('active')
            if (!mainImage) return
            const image = button.dataset.productImage
            if (image) mainImage.src = image
            mainImage.style.objectPosition = button.dataset.position || 'center'
            if (modalImage && image) modalImage.src = image
        })
    })

    listen(zoomContainer, 'mousemove', ((event: MouseEvent) => {
        if (!mainImage || !zoomContainer || !window.matchMedia('(hover: hover)').matches) return
        const rect = zoomContainer.getBoundingClientRect()
        const x = ((event.clientX - rect.left) / rect.width) * 100
        const y = ((event.clientY - rect.top) / rect.height) * 100
        mainImage.style.transformOrigin = `${x}% ${y}%`
        mainImage.style.transform = 'scale(1.8)'
    }) as EventListener)

    listen(zoomContainer, 'mouseleave', () => {
        if (mainImage) mainImage.style.transform = 'scale(1)'
    })

    listen(page.querySelector('[data-open-zoom]'), 'click', () => modal?.classList.add('show'))
    listen(modal?.querySelector('[data-close-zoom]'), 'click', () => modal?.classList.remove('show'))

    interactionCleanups.push(() => {
        modal?.classList.remove('show')
        if (mainImage) mainImage.style.transform = 'scale(1)'
    })
})
</script>

<template>
  <div ref="productPage">
  <main>
        <div class="product-breadcrumb">
            <div class="container"><a href="index.html">Home</a><i class="bi bi-chevron-right"></i><a
                    href="shop.html">Products</a><i class="bi bi-chevron-right"></i><span>Pulse Wireless
                    Headphones</span>
                <div class="ms-auto d-none d-sm-flex"><a href="#"><i class="bi bi-chevron-left"></i> Prev</a><a
                        href="#">Next <i class="bi bi-chevron-right"></i></a></div>
            </div>
        </div>
        <section class="product-detail container py-4">
            <div class="row g-4 g-xl-5">
                <div class="col-lg-6">
                    <div class="product-gallery">
                        <div class="product-thumbs" role="tablist"><button class="active" type="button"
                                data-product-image="/assets/images/product-1.svg"><img src="/assets/images/product-1.svg"
                                    alt="Front view"></button><button type="button"
                                data-product-image="/assets/images/category-products-grid.png"
                                data-position="66.666% 0%"><span class="thumb-sprite"
                                    style="--tx:2;--ty:0"></span></button><button type="button"
                                data-product-image="/assets/images/product-3.svg"><img src="/assets/images/product-3.svg"
                                    alt="Detail view"></button><button type="button"
                                data-product-image="/assets/images/product-1.svg"><img src="/assets/images/product-1.svg"
                                    alt="Side view"></button></div>
                        <div class="product-main-image" data-zoom-container=""><img src="/assets/images/product-1.svg"
                                alt="Pulse Wireless Headphones" data-product-main=""><button type="button"
                                data-open-zoom="" aria-label="Open image zoom"><i
                                    class="bi bi-arrows-fullscreen"></i></button><span class="zoom-hint"><i
                                    class="bi bi-search"></i> Hover to zoom</span></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="product-summary">
                        <div class="product-stock"><span></span> In stock · Ready to ship</div>
                        <h1>Pulse Wireless Headphones</h1>
                        <div class="product-review"><span>★★★★★</span><a href="#reviews">(86 reviews)</a><i></i><span
                                class="sku">SKU: NC-AU-104</span></div>
                        <div class="product-detail-price">৳8,490 <del>৳9,900</del><em>Save 14%</em></div>
                        <p class="product-intro">Immersive sound, soft memory cushions and up to 40-hour battery life.
                            Built for focused work, effortless calls and everyday listening.</p>
                        <div class="product-benefits"><span><i class="bi bi-truck"></i> Free delivery</span><span><i
                                    class="bi bi-shield-check"></i> 1-year warranty</span><span><i
                                    class="bi bi-arrow-counterclockwise"></i> 7-day returns</span></div>
                        <div class="product-option">
                            <div><strong>Colour:</strong><span data-selected-color="">Midnight</span></div>
                            <div class="color-swatches"><button class="active" style="--swatch:#17211d"
                                    data-color="Midnight" aria-label="Midnight"></button><button
                                    style="--swatch:#ff5a3c" data-color="Coral" aria-label="Coral"></button><button
                                    style="--swatch:#d7ddd9" data-color="Cloud" aria-label="Cloud"></button></div>
                        </div>
                        <div class="product-option">
                            <div><strong>Connection:</strong></div>
                            <div class="connection-options"><button class="active"
                                    type="button">Bluetooth</button><button type="button">Bluetooth + USB-C</button>
                            </div>
                        </div>
                        <div class="product-purchase-row">
                            <div class="product-qty qty"><button class="minus" type="button">−</button><input value="1"
                                    inputmode="numeric" aria-label="Quantity"><button class="plus"
                                    type="button">+</button></div><button class="product-add-cart" type="button"
                                data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button><button
                                class="product-action" type="button" aria-label="Add to wishlist"><i
                                    class="bi bi-heart"></i></button><button class="product-action d-none d-sm-grid"
                                type="button" aria-label="Compare"><i class="bi bi-arrow-left-right"></i></button>
                        </div><button class="buy-now" type="button">Buy it now</button>
                        <div class="product-meta"><span><strong>Category:</strong> Audio,
                                Headphones</span><span><strong>Share:</strong> <a href="#"><i
                                        class="bi bi-facebook"></i></a><a href="#"><i class="bi bi-twitter-x"></i></a><a
                                    href="#"><i class="bi bi-instagram"></i></a></span></div>
                    </div>
                </div>
            </div>
        </section>
        <section class="product-tabs container pb-5" id="productDetails">
            <ul class="nav" role="tablist">
                <li><button class="active" data-bs-toggle="tab" data-bs-target="#description" aria-selected="true"
                        role="tab">Description</button></li>
                <li><button data-bs-toggle="tab" data-bs-target="#specifications" aria-selected="false" tabindex="-1"
                        role="tab">Specifications</button></li>
                <li><button data-bs-toggle="tab" data-bs-target="#shipping" aria-selected="false" tabindex="-1"
                        role="tab">Shipping &amp; Returns</button></li>
                <li><button data-bs-toggle="tab" data-bs-target="#reviews" aria-selected="false" tabindex="-1"
                        role="tab">Reviews (86)</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <h2>Sound designed around you</h2>
                    <p>Balanced drivers deliver detailed highs and warm, controlled bass. Adaptive cushioning keeps the
                        fit comfortable through long sessions, while dual microphones make every call clearer.</p>
                    <div class="description-points"><span><i class="bi bi-check2"></i> 40-hour battery</span><span><i
                                class="bi bi-check2"></i> Active noise cancellation</span><span><i
                                class="bi bi-check2"></i> Multipoint Bluetooth 5.3</span><span><i
                                class="bi bi-check2"></i> Fast USB-C charging</span></div>
                </div>
                <div class="tab-pane fade" id="specifications" role="tabpanel">
                    <table>
                        <tbody>
                            <tr>
                                <th>Driver</th>
                                <td>40mm dynamic</td>
                            </tr>
                            <tr>
                                <th>Battery</th>
                                <td>Up to 40 hours</td>
                            </tr>
                            <tr>
                                <th>Weight</th>
                                <td>265g</td>
                            </tr>
                            <tr>
                                <th>Connectivity</th>
                                <td>Bluetooth 5.3, USB-C</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <h2>Fast, dependable delivery</h2>
                    <p>Nationwide delivery within 2–5 business days. Unused products can be returned in their original
                        packaging within 7 days.</p>
                </div>
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <h2>Customer reviews</h2>
                    <p>Customers rate these headphones 4.8 out of 5 for sound quality, battery life and comfort.</p>
                </div>
            </div>
        </section>
    </main>
    <div class="product-zoom-modal" data-zoom-modal="" aria-hidden="true"><button type="button" data-close-zoom=""
            aria-label="Close"><i class="bi bi-x-lg"></i></button><img src="/assets/images/product-1.svg"
            alt="Zoomed product" data-zoom-image=""><span>Scroll or pinch to inspect</span></div>
    <div class="product-sticky-cart" data-sticky-cart="">
        <div class="container"><img src="/assets/images/product-1.svg" alt="">
            <div><strong>Pulse Wireless Headphones</strong><span>৳8,490</span></div>
            <div class="product-qty qty d-none d-sm-flex"><button class="minus">−</button><input value="1"><button
                    class="plus">+</button></div><button class="product-add-cart" data-toast="Added to cart"><i
                    class="bi bi-cart3"></i> Add to cart</button>
        </div>
    </div>
  </div>
</template>
