<script setup>
definePageMeta({
  layout: 'home'
})

const activeDealFilter = ref('all')
const activeTrendingFilter = ref('all')
const { data, pending, error } = await useCatalogMenu()
const pageSeo = computed(() => data.value?.seo)

usePageSeo(pageSeo)

const sections = computed(() => data.value?.categories ?? [])
const activeSectionId = ref(sections.value[0]?.id ?? null)
const activeSection = computed(() =>
  sections.value.find(section => section.id === activeSectionId.value) ?? sections.value[0] ?? null
)

const sectionIcons = ['bi-phone', 'bi-bag', 'bi-house-heart', 'bi-heart-pulse', 'bi-controller', 'bi-bicycle', 'bi-balloon', 'bi-car-front']
const sectionIcon = index => sectionIcons[index % sectionIcons.length]
</script>


<template>
  <main>
        <section class="hero-market container py-4 py-lg-5">
            <div class="row g-0 hero-shell">
                <aside class="col-lg-3 category-sidebar">
                    <div class="category-sidebar-title"><i class="bi bi-list me-2"></i>Browse Categories</div>
                    <nav v-if="sections.length" aria-label="Product categories">
                        <div v-for="(section, sectionIndex) in sections" :key="section.id" class="desktop-category-item">
                            <NuxtLink :to="{ path: '/shop', query: { section: section.id } }"><span>
                                    <img v-if="section.image_url" :src="section.image_url" :alt="section.name"
                                        class="sidebar-section-image">
                                    <i v-else class="bi" :class="sectionIcon(sectionIndex)"></i>{{ section.name }}</span><i
                                    class="bi bi-chevron-right"></i></NuxtLink>
                            <div v-if="section.categories?.length" class="category-flyout">
                                <div v-for="category in section.categories" :key="category.id">
                                    <h3><NuxtLink :to="{ path: '/shop', query: { category: category.url } }">{{ category.category_name }}</NuxtLink></h3>
                                    <NuxtLink v-for="subcategory in category.subcategories" :key="subcategory.id"
                                        :to="{ path: '/shop', query: { category: subcategory.url } }">{{ subcategory.category_name }}</NuxtLink>
                                    <NuxtLink v-if="!category.subcategories?.length"
                                        :to="{ path: '/shop', query: { category: category.url } }">View products</NuxtLink>
                                </div>
                                <div class="flyout-feature">
                                    <img v-if="section.image_url" :src="section.image_url" :alt="section.name" class="flyout-section-image">
                                    <i v-else class="bi bi-grid"></i><small>Featured section</small>
                                    <strong>Explore {{ section.name }}</strong>
                                    <NuxtLink :to="{ path: '/shop', query: { section: section.id } }">Explore now <i class="bi bi-arrow-right"></i></NuxtLink>
                                </div>
                            </div>
                        </div>
                    </nav>
                    <nav v-else aria-label="Product categories">
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-phone"></i>
                                    Electronics</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Mobiles &amp; Computing</h3><NuxtLink to="/shop">Smartphones</NuxtLink><NuxtLink
                                        to="/shop">Feature Phones</NuxtLink><NuxtLink to="/shop">Laptop &amp;
                                        Notebook</NuxtLink><NuxtLink to="/shop">Desktop Computers</NuxtLink><NuxtLink
                                        to="/shop">Tablets</NuxtLink>
                                </div>
                                <div>
                                    <h3>TV &amp; Entertainment</h3><NuxtLink to="/shop">Smart Televisions</NuxtLink><NuxtLink
                                        to="/shop">Headphones</NuxtLink><NuxtLink to="/shop">Speakers</NuxtLink><NuxtLink
                                        to="/shop">Gaming Consoles</NuxtLink><NuxtLink to="/shop">Cameras</NuxtLink>
                                </div>
                                <div class="flyout-feature"><i class="bi bi-laptop"></i><small>Featured
                                        collection</small><strong>Technology that works for you</strong><NuxtLink
                                        to="/shop">Explore now <i class="bi bi-arrow-right"></i></NuxtLink></div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-bag"></i> Fashion
                                    &amp; Lifestyle</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Women's Fashion</h3><NuxtLink to="/shop">Dresses</NuxtLink><NuxtLink to="/shop">Tops
                                        &amp; T-shirts</NuxtLink><NuxtLink to="/shop">Shoes</NuxtLink><NuxtLink
                                        to="/shop">Handbags</NuxtLink><NuxtLink to="/shop">Jewellery</NuxtLink>
                                </div>
                                <div>
                                    <h3>Men's Fashion</h3><NuxtLink to="/shop">Shirts</NuxtLink><NuxtLink to="/shop">Jeans &amp;
                                        Trousers</NuxtLink><NuxtLink to="/shop">Footwear</NuxtLink><NuxtLink to="/shop">Watches</NuxtLink><NuxtLink
                                        to="/shop">Accessories</NuxtLink>
                                </div>
                                <div>
                                    <h3>Kids &amp; Lifestyle</h3><NuxtLink to="/shop">Boys' Clothing</NuxtLink><NuxtLink
                                        to="/shop">Girls' Clothing</NuxtLink><NuxtLink to="/shop">Beauty &amp; Care</NuxtLink><NuxtLink
                                        to="/shop">Travel Accessories</NuxtLink>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-house-heart"></i>
                                    Home &amp; Living</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Furniture</h3><NuxtLink to="/shop">Living Room</NuxtLink><NuxtLink
                                        to="/shop">Bedroom</NuxtLink><NuxtLink to="/shop">Office Furniture</NuxtLink><NuxtLink
                                        to="/shop">Storage</NuxtLink>
                                </div>
                                <div>
                                    <h3>Kitchen &amp; Dining</h3><NuxtLink to="/shop">Cookware</NuxtLink><NuxtLink
                                        to="/shop">Kitchen Appliances</NuxtLink><NuxtLink to="/shop">Dinnerware</NuxtLink><NuxtLink
                                        to="/shop">Drinkware</NuxtLink>
                                </div>
                                <div>
                                    <h3>Home Improvement</h3><NuxtLink to="/shop">Lighting</NuxtLink><NuxtLink to="/shop">Home
                                        Decor</NuxtLink><NuxtLink to="/shop">Tools &amp; Hardware</NuxtLink><NuxtLink
                                        to="/shop">Garden</NuxtLink>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-heart-pulse"></i>
                                    Health &amp; Beauty</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Beauty</h3><NuxtLink to="/shop">Skin Care</NuxtLink><NuxtLink to="/shop">Hair Care</NuxtLink><NuxtLink
                                        to="/shop">Makeup</NuxtLink><NuxtLink to="/shop">Fragrance</NuxtLink>
                                </div>
                                <div>
                                    <h3>Personal Care</h3><NuxtLink to="/shop">Bath &amp; Body</NuxtLink><NuxtLink
                                        to="/shop">Grooming</NuxtLink><NuxtLink to="/shop">Wellness</NuxtLink><NuxtLink
                                        to="/shop">Medical Supplies</NuxtLink>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-controller"></i>
                                    Gaming &amp; Accessories</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Gaming</h3><NuxtLink to="/shop">Consoles</NuxtLink><NuxtLink to="/shop">Controllers</NuxtLink><NuxtLink
                                        to="/shop">Gaming Headsets</NuxtLink><NuxtLink to="/shop">Games</NuxtLink>
                                </div>
                                <div>
                                    <h3>Computer Accessories</h3><NuxtLink to="/shop">Keyboard &amp; Mouse</NuxtLink><NuxtLink
                                        to="/shop">Monitors</NuxtLink><NuxtLink to="/shop">Storage</NuxtLink><NuxtLink
                                        to="/shop">Networking</NuxtLink>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-bicycle"></i>
                                    Sports &amp; Outdoor</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Sports</h3><NuxtLink to="/shop">Fitness Equipment</NuxtLink><NuxtLink
                                        to="/shop">Cycling</NuxtLink><NuxtLink to="/shop">Team Sports</NuxtLink><NuxtLink
                                        to="/shop">Sportswear</NuxtLink>
                                </div>
                                <div>
                                    <h3>Outdoor</h3><NuxtLink to="/shop">Camping</NuxtLink><NuxtLink to="/shop">Travel Gear</NuxtLink><NuxtLink
                                        to="/shop">Garden &amp; Outdoor</NuxtLink>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-balloon"></i> Baby,
                                    Kids &amp; Toys</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Baby Care</h3><NuxtLink to="/shop">Feeding</NuxtLink><NuxtLink to="/shop">Diapers</NuxtLink><NuxtLink
                                        to="/shop">Nursery</NuxtLink>
                                </div>
                                <div>
                                    <h3>Toys</h3><NuxtLink to="/shop">Educational Toys</NuxtLink><NuxtLink to="/shop">Outdoor
                                        Toys</NuxtLink><NuxtLink to="/shop">Games &amp; Puzzles</NuxtLink>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-category-item"><NuxtLink to="/shop"><span><i class="bi bi-car-front"></i>
                                    Automotive</span><i class="bi bi-chevron-right"></i></NuxtLink>
                            <div class="category-flyout">
                                <div>
                                    <h3>Car Accessories</h3><NuxtLink to="/shop">Interior Accessories</NuxtLink><NuxtLink
                                        to="/shop">Car Electronics</NuxtLink><NuxtLink to="/shop">Cleaning &amp; Care</NuxtLink>
                                </div>
                                <div>
                                    <h3>Parts &amp; Tools</h3><NuxtLink to="/shop">Motorcycle Accessories</NuxtLink><NuxtLink
                                        to="/shop">Tyres &amp; Wheels</NuxtLink><NuxtLink to="/shop">Automotive Tools</NuxtLink>
                                </div>
                            </div>
                        </div>
                    </nav>
                </aside>
                <div class="col-lg-9">
                    <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel"
                        data-bs-interval="5000">
                        <div class="carousel-indicators"><button type="button" data-bs-target="#heroCarousel"
                                data-bs-slide-to="0" class="active" aria-current="true"
                                aria-label="Slide 1"></button><button type="button" data-bs-target="#heroCarousel"
                                data-bs-slide-to="1" aria-label="Slide 2"></button><button type="button"
                                data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button></div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="hero-slide hero-slide-one">
                                    <div class="hero-copy">
                                        <div class="eyebrow mb-3">New season · smart living</div>
                                        <h1>Built for your everyday.</h1>
                                        <p>Considered technology and modern essentials, selected for a smarter life.</p>
                                        <div class="hero-offer"><small>From</small><strong>৳6,990</strong><span>Free
                                                delivery</span></div><NuxtLink to="/shop"
                                            class="btn btn-brand rounded-pill px-4 py-3">Shop collection <i
                                                class="bi bi-arrow-right ms-2"></i></NuxtLink>
                                        <div class="hero-assurance"><span><i class="bi bi-check2-circle"></i> Genuine
                                                products</span><span><i class="bi bi-arrow-counterclockwise"></i> Easy
                                                returns</span></div>
                                    </div>
                                    <div class="hero-art"><span class="art-circle"></span><i
                                            class="bi bi-headphones"></i><i class="bi bi-smartwatch"></i></div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="hero-slide hero-slide-two">
                                    <div class="hero-copy">
                                        <div class="eyebrow mb-3">Trade-in offer · save 25%</div>
                                        <h1>Power your next big idea.</h1>
                                        <p>Fast laptops, crisp displays and accessories built to keep up with you.</p>
                                        <div class="hero-offer"><small>Save up
                                                to</small><strong>25%</strong><span>Official warranty</span></div><NuxtLink
                                            to="/shop" class="btn btn-dark rounded-pill px-4 py-3">Explore
                                            technology <i class="bi bi-arrow-right ms-2"></i></NuxtLink>
                                        <div class="hero-assurance"><span><i class="bi bi-check2-circle"></i> Genuine
                                                products</span><span><i class="bi bi-arrow-counterclockwise"></i> Easy
                                                returns</span></div>
                                    </div>
                                    <div class="hero-art"><span class="art-circle"></span><i class="bi bi-laptop"></i><i
                                            class="bi bi-mouse"></i></div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="hero-slide hero-slide-three">
                                    <div class="hero-copy">
                                        <div class="eyebrow mb-3">Sound that moves you</div>
                                        <h1>Turn up every moment.</h1>
                                        <p>Premium wireless audio with all-day comfort and incredible clarity.</p>
                                        <div class="hero-offer"><small>Special
                                                price</small><strong>৳8,490</strong><span>40-hour battery</span></div><NuxtLink
                                            to="/shop" class="btn btn-brand rounded-pill px-4 py-3">Shop audio <i
                                                class="bi bi-arrow-right ms-2"></i></NuxtLink>
                                        <div class="hero-assurance"><span><i class="bi bi-check2-circle"></i> Genuine
                                                products</span><span><i class="bi bi-arrow-counterclockwise"></i> Easy
                                                returns</span></div>
                                    </div>
                                    <div class="hero-art"><span class="art-circle"></span><i
                                            class="bi bi-earbuds"></i><i class="bi bi-speaker"></i></div>
                                </div>
                            </div>
                        </div><button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel"
                            data-bs-slide="prev"><span class="carousel-control-prev-icon"
                                aria-hidden="true"></span><span class="visually-hidden">Previous</span></button><button
                            class="carousel-control-next" type="button" data-bs-target="#heroCarousel"
                            data-bs-slide="next"><span class="carousel-control-next-icon"
                                aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                    </div>
                </div>
            </div>
        </section>
        <section class="category-section container py-4" id="category-slider">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h2 class="section-title mb-0">Shop by Category</h2>
                </div>
                <div class="d-flex align-items-center gap-2"><button class="category-arrow" type="button"
                        data-category-prev="" aria-label="Previous categories" disabled><i
                            class="bi bi-arrow-left"></i></button><button class="category-arrow" type="button"
                        data-category-next="" aria-label="Next categories" disabled><i
                            class="bi bi-arrow-right"></i></button><NuxtLink to="/shop"
                        class="category-view-all ms-2">View all</NuxtLink></div>
            </div>
            <div v-if="pending" class="category-api-message">Loading categories...</div>
            <div v-else-if="error" class="category-api-message category-api-error">Categories could not be loaded.</div>
            <template v-else>
                <div class="section-filter-tabs" role="tablist" aria-label="Product sections">
                    <button v-for="section in sections" :key="section.id" type="button" role="tab"
                        :class="{ active: activeSection?.id === section.id }"
                        :aria-selected="activeSection?.id === section.id"
                        @click="activeSectionId = section.id">
                        <img v-if="section.image_url" :src="section.image_url" alt="">
                        <span>{{ section.name }}</span>
                    </button>
                </div>

                <div v-if="activeSection?.categories?.length" class="category-slider">
                    <NuxtLink v-for="category in activeSection.categories" :key="category.id"
                        :to="{ path: '/shop', query: { category: category.url } }" class="category-slide">
                        <img v-if="category.image_url || activeSection.image_url"
                            :src="category.image_url || activeSection.image_url"
                            :alt="category.category_name" class="category-visual category-api-image" loading="lazy">
                        <span v-else class="category-visual category-api-placeholder"><i class="bi bi-grid"></i></span>
                        <strong>{{ category.category_name }}</strong>
                        <small>{{ category.products_count ?? 0 }} products</small>
                    </NuxtLink>
                </div>
                <div v-else class="category-api-message">No categories are available in this section yet.</div>
            </template>
        </section>
        <section class="hot-deals py-5" id="hot-deals">
            <div class="container">
                <div class="deals-heading">
                    <h2>Hot Deals Products</h2>
                    <div class="deal-tabs" role="tablist" aria-label="Filter hot deals"><button
                            :class="{ active: activeDealFilter === 'all' }" type="button" @click="activeDealFilter = 'all'">All</button><button
                            :class="{ active: activeDealFilter === 'electronics' }" type="button" @click="activeDealFilter = 'electronics'">Electronics</button><button
                            :class="{ active: activeDealFilter === 'furniture' }" type="button" @click="activeDealFilter = 'furniture'">Furniture</button><button
                            :class="{ active: activeDealFilter === 'clothes' }" type="button" @click="activeDealFilter = 'clothes'">Clothes</button><button
                            :class="{ active: activeDealFilter === 'accessories' }" type="button" @click="activeDealFilter = 'accessories'">Accessories</button></div>
                </div>
                <div class="deals-wrap"><button class="deal-arrow deal-prev" type="button" aria-label="Previous deals"
                        disabled><i class="bi bi-chevron-left"></i></button>
                    <div class="deals-track" data-deals-track="">
                        <article class="deal-card" :class="{ 'is-hidden': activeDealFilter !== 'all' && activeDealFilter !== 'furniture' }">
                            <div class="deal-media"><span class="deal-badge sale">Sale</span><img
                                    src="/assets/images/product-4.svg" alt="Nordic wooden stool"><button
                                    class="deal-add-cart" type="button" data-toast="Added to cart"><i
                                        class="bi bi-cart3"></i><span>Add to cart</span></button></div>
                            <small>Furniture</small>
                            <h3><NuxtLink to="/product">Nordic Wooden Stool</NuxtLink></h3>
                            <div class="deal-price">৳5,299 <del>৳6,500</del></div>
                            <div class="deal-rating"><span>★★★★★</span> <small>(2 Reviews)</small></div>
                        </article>
                        <article class="deal-card" :class="{ 'is-hidden': activeDealFilter !== 'all' && activeDealFilter !== 'electronics' }">
                            <div class="deal-media">
                                <div class="badge-stack"><span class="deal-badge top">Top</span><span
                                        class="deal-badge sale">Sale</span></div><img src="/assets/images/product-1.svg"
                                    alt="Pulse wireless headphones"><button class="deal-add-cart" type="button"
                                    data-toast="Added to cart"><i class="bi bi-cart3"></i><span>Add to
                                        cart</span></button>
                            </div>
                            <div class="deal-countdown" data-countdown="28800">
                                <b><span>08</span><small>hours</small></b><em>:</em><b><span>00</span><small>mins</small></b><em>:</em><b><span>00</span><small>secs</small></b>
                            </div><small>Electronics</small>
                            <h3><NuxtLink to="/product">Pulse Wireless Headphones</NuxtLink></h3>
                            <div class="deal-price">৳8,490 <del>৳9,900</del></div>
                            <div class="deal-rating"><span>★★★★★</span> <small>(4 Reviews)</small></div>
                            <div class="deal-colors"><i class="blue"></i><i class="coral"></i><i class="black"></i>
                            </div>
                        </article>
                        <article class="deal-card" :class="{ 'is-hidden': activeDealFilter !== 'all' && activeDealFilter !== 'furniture' }">
                            <div class="deal-media"><span class="deal-badge sale">Sale</span><img
                                    src="/assets/images/product-3.svg" alt="Modern two seater sofa"><button
                                    class="deal-add-cart" type="button" data-toast="Added to cart"><i
                                        class="bi bi-cart3"></i><span>Add to cart</span></button></div>
                            <small>Furniture</small>
                            <h3><NuxtLink to="/product">Modern 2-Seater Sofa</NuxtLink></h3>
                            <div class="deal-price">৳35,000 <del>৳42,000</del></div>
                            <div class="deal-rating"><span>★★★★<i>★</i></span> <small>(6 Reviews)</small></div>
                        </article>
                        <article class="deal-card" :class="{ 'is-hidden': activeDealFilter !== 'all' && activeDealFilter !== 'clothes' }">
                            <div class="deal-media"><span class="deal-badge sale">Sale</span><img
                                    src="/assets/images/product-2.svg" alt="Premium biker jacket"><button
                                    class="deal-add-cart" type="button" data-toast="Added to cart"><i
                                        class="bi bi-cart3"></i><span>Add to cart</span></button></div>
                            <small>Clothes</small>
                            <h3><NuxtLink to="/product">Premium Biker Jacket</NuxtLink></h3>
                            <div class="deal-price">৳12,400 <del>৳15,500</del></div>
                            <div class="deal-rating"><span>★★★★<i>★</i></span> <small>(4 Reviews)</small></div>
                            <div class="deal-colors"><i class="brown"></i><i class="grey"></i></div>
                        </article>
                        <article class="deal-card" :class="{ 'is-hidden': activeDealFilter !== 'all' && activeDealFilter !== 'electronics' }">
                            <div class="deal-media">
                                <div class="badge-stack"><span class="deal-badge top">Top</span><span
                                        class="deal-badge sale">Sale</span></div><img src="/assets/images/product-4.svg"
                                    alt="Smart 4K television"><button class="deal-add-cart" type="button"
                                    data-toast="Added to cart"><i class="bi bi-cart3"></i><span>Add to
                                        cart</span></button>
                            </div>
                            <div class="deal-countdown" data-countdown="23400">
                                <b><span>06</span><small>hours</small></b><em>:</em><b><span>30</span><small>mins</small></b><em>:</em><b><span>00</span><small>secs</small></b>
                            </div><small>Electronics</small>
                            <h3><NuxtLink to="/product">Vision Class 4K Smart TV</NuxtLink></h3>
                            <div class="deal-price">৳69,999 <del>৳79,999</del></div>
                            <div class="deal-rating"><span>★★★★<i>★</i></span> <small>(10 Reviews)</small></div>
                        </article>
                        <article class="deal-card" :class="{ 'is-hidden': activeDealFilter !== 'all' && activeDealFilter !== 'accessories' }">
                            <div class="deal-media"><span class="deal-badge top">Top</span><img
                                    src="/assets/images/product-2.svg" alt="Orbit smart watch"><button
                                    class="deal-add-cart" type="button" data-toast="Added to cart"><i
                                        class="bi bi-cart3"></i><span>Add to cart</span></button></div>
                            <small>Accessories</small>
                            <h3><NuxtLink to="/product">Orbit Smart Watch S2</NuxtLink></h3>
                            <div class="deal-price">৳6,990 <del>৳8,200</del></div>
                            <div class="deal-rating"><span>★★★★★</span> <small>(8 Reviews)</small></div>
                        </article>
                    </div><button class="deal-arrow deal-next" type="button" aria-label="Next deals"><i
                            class="bi bi-chevron-right"></i></button>
                </div>
                <div class="deal-dots" aria-hidden="true"><span class="active"></span><span></span></div>
            </div>
        </section>
        <section class="container py-5 smart-picks-section">
            <div class="d-flex justify-content-between align-items-end mb-4 smart-picks-heading">
                <div>
                    <div class="eyebrow">This week’s favourites</div>
                    <h2 class="section-title">Trending Products</h2>
                </div>
                <div class="trending-tabs"><button :class="{ active: activeTrendingFilter === 'all' }" type="button" @click="activeTrendingFilter = 'all'">Top
                        Rated</button><button :class="{ active: activeTrendingFilter === 'selling' }" type="button" @click="activeTrendingFilter = 'selling'">Best Selling</button><button
                        :class="{ active: activeTrendingFilter === 'sale' }" type="button" @click="activeTrendingFilter = 'sale'">On Sale</button></div>
            </div>
            <div class="row g-3" id="products">
                <div class="trending-promo">
                    <div><small>Smart technology</small>
                        <h3>Your everyday essentials, upgraded.</h3>
                        <p>Selected devices at prices worth discovering.</p>
                    </div><NuxtLink to="/shop">Shop now <i class="bi bi-arrow-right"></i></NuxtLink>
                </div>
                <div class="col-6 col-lg-3" :class="{ 'trending-muted': activeTrendingFilter !== 'all' && activeTrendingFilter !== 'selling' }">
                    <div class="product-card card h-100 p-2"><NuxtLink to="/product" class="product-media"><img
                                src="/assets/images/product-1.svg" alt="Wireless headphones"><span
                                class="smart-badge">Best seller</span><button class="smart-wishlist" type="button"
                                aria-label="Add to wishlist"><i class="bi bi-heart"></i></button></NuxtLink>
                        <div class="card-body">
                            <div class="smart-rating"><span>★★★★★</span><small> 4.8</small></div><small
                                class="text-secondary">Audio</small>
                            <h6 class="mt-1"><NuxtLink class="text-dark text-decoration-none" to="/product">Pulse
                                    Wireless Headphones</NuxtLink></h6>
                            <div><span class="price">৳8,490</span> <span class="old-price">৳9,900</span></div><button
                                class="btn btn-dark w-100 mt-3" data-toast="Added to cart">Add to cart</button>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3" :class="{ 'trending-muted': activeTrendingFilter !== 'all' && activeTrendingFilter !== 'sale' }">
                    <div class="product-card card h-100 p-2">
                        <div class="product-media"><img src="/assets/images/product-2.svg" alt="Smartwatch"><span
                                class="smart-badge">Popular</span><button class="smart-wishlist" type="button"
                                aria-label="Add to wishlist"><i class="bi bi-heart"></i></button></div>
                        <div class="card-body">
                            <div class="smart-rating"><span>★★★★★</span><small> 4.8</small></div><small
                                class="text-secondary">Wearables</small>
                            <h6 class="mt-1">Orbit Smart Watch S2</h6>
                            <div><span class="price">৳6,990</span></div><button class="btn btn-dark w-100 mt-3"
                                data-toast="Added to cart">Add to cart</button>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3" :class="{ 'trending-muted': activeTrendingFilter !== 'all' && activeTrendingFilter !== 'selling' }">
                    <div class="product-card card h-100 p-2">
                        <div class="product-media"><img src="/assets/images/product-3.svg" alt="Speaker"><span
                                class="smart-badge">Popular</span><button class="smart-wishlist" type="button"
                                aria-label="Add to wishlist"><i class="bi bi-heart"></i></button></div>
                        <div class="card-body">
                            <div class="smart-rating"><span>★★★★★</span><small> 4.8</small></div><small
                                class="text-secondary">Audio</small>
                            <h6 class="mt-1">Room Mini Speaker</h6>
                            <div><span class="price">৳3,450</span></div><button class="btn btn-dark w-100 mt-3"
                                data-toast="Added to cart">Add to cart</button>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3" :class="{ 'trending-muted': activeTrendingFilter !== 'all' }">
                    <div class="product-card card h-100 p-2">
                        <div class="product-media"><img src="/assets/images/product-4.svg" alt="Camera"><span
                                class="smart-badge">New</span><button class="smart-wishlist" type="button"
                                aria-label="Add to wishlist"><i class="bi bi-heart"></i></button></div>
                        <div class="card-body">
                            <div class="smart-rating"><span>★★★★★</span><small> 4.8</small></div><small
                                class="text-secondary">Camera</small>
                            <h6 class="mt-1">Pocket Action Camera</h6>
                            <div><span class="price">৳14,800</span></div><button class="btn btn-dark w-100 mt-3"
                                data-toast="Added to cart">Add to cart</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="more-products-section">
            <div class="container">
                <div class="more-products-head"><div><small>Explore more</small><h2>Products you may love</h2><p>Fresh finds selected across our most popular categories.</p></div><NuxtLink to="/shop">Shop all products <i class="bi bi-arrow-right"></i></NuxtLink></div>
                <div class="more-products-grid">
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:0;--my:0"></span><em class="new">New</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Computers</small><h3><NuxtLink to="/product">NovaBook Air Laptop</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(5)</small></div><div class="more-product-price">৳74,900</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:1;--my:0"></span><em class="top">Top</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Smartphones</small><h3><NuxtLink to="/product">Nova X Pro Smartphone</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(6)</small></div><div class="more-product-price">৳54,500</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:2;--my:0"></span><em class="sale">Sale</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Audio</small><h3><NuxtLink to="/product">Pulse Wireless Headphones</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(7)</small></div><div class="more-product-price">৳8,490</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:3;--my:0"></span><em class="top">Top</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Wearables</small><h3><NuxtLink to="/product">Orbit Smart Watch S2</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(8)</small></div><div class="more-product-price">৳6,990</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:0;--my:1"></span><em class="sale">Sale</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Televisions</small><h3><NuxtLink to="/product">Vision Class 4K Smart TV</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(9)</small></div><div class="more-product-price">৳69,999</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:1;--my:1"></span><em class="new">New</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Fashion</small><h3><NuxtLink to="/product">Everyday Leather Tote</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(10)</small></div><div class="more-product-price">৳4,850</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:2;--my:1"></span><em class="popular">Popular</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Furniture</small><h3><NuxtLink to="/product">Nordic Lounge Chair</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(11)</small></div><div class="more-product-price">৳18,900</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:3;--my:1"></span><em class="sale">Sale</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Kitchen</small><h3><NuxtLink to="/product">Pro Kitchen Blender</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(12)</small></div><div class="more-product-price">৳7,250</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:0;--my:2"></span><em class="top">Top</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Gaming</small><h3><NuxtLink to="/product">Nova Gaming Controller</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(5)</small></div><div class="more-product-price">৳4,390</div></div></article>
                    <article class="more-product-card" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:1;--my:2"></span><em class="new">New</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Cameras</small><h3><NuxtLink to="/product">Pocket Mirrorless Camera</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(6)</small></div><div class="more-product-price">৳42,800</div></div></article>
                    <article class="more-product-card more-product-hidden" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:2;--my:2"></span><em class="popular">Popular</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Sports</small><h3><NuxtLink to="/product">Aero Cycling Helmet</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(7)</small></div><div class="more-product-price">৳3,750</div></div></article>
                    <article class="more-product-card more-product-hidden" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:3;--my:2"></span><em class="sale">Sale</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Gifts</small><h3><NuxtLink to="/product">Signature Gift Box</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(8)</small></div><div class="more-product-price">৳2,490</div></div></article>
                    <article class="more-product-card more-product-hidden" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:0;--my:3"></span><em class="new">New</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Office</small><h3><NuxtLink to="/product">Compact Office Printer</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(9)</small></div><div class="more-product-price">৳16,500</div></div></article>
                    <article class="more-product-card more-product-hidden" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:1;--my:3"></span><em class="popular">Popular</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Baby & Kids</small><h3><NuxtLink to="/product">Wooden Learning Set</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(10)</small></div><div class="more-product-price">৳3,150</div></div></article>
                    <article class="more-product-card more-product-hidden" data-more-product><NuxtLink class="more-product-media" to="/product"><span style="--mx:2;--my:3"></span><em class="top">Top</em><button type="button" class="more-product-heart" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button><button type="button" class="more-product-cart" data-toast="Added to cart"><i class="bi bi-cart3"></i> Add to cart</button></NuxtLink><div class="more-product-info"><small>Automotive</small><h3><NuxtLink to="/product">Smart City Car Kit</NuxtLink></h3><div class="more-product-rating"><span>★★★★★</span><small>(11)</small></div><div class="more-product-price">৳8,900</div></div></article>
                </div>
                <div class="more-products-action"><button type="button" data-view-more-products><span>View more products</span><i class="bi bi-chevron-down"></i></button><small>Showing 10 of 15 products</small></div>
            </div>
        </section>
        <section class="container pb-5">
            <div class="feature-strip p-4">
                <div class="row g-4 text-center">
                    <div class="col-6 col-lg-3"><i class="bi bi-truck fs-3"></i>
                        <h6 class="mt-2">Fast delivery</h6>
                    </div>
                    <div class="col-6 col-lg-3"><i class="bi bi-shield-check fs-3"></i>
                        <h6 class="mt-2">Secure payment</h6>
                    </div>
                    <div class="col-6 col-lg-3"><i class="bi bi-arrow-counterclockwise fs-3"></i>
                        <h6 class="mt-2">Easy returns</h6>
                    </div>
                    <div class="col-6 col-lg-3"><i class="bi bi-headset fs-3"></i>
                        <h6 class="mt-2">Helpful support</h6>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
.section-filter-tabs{display:flex;gap:8px;overflow-x:auto;margin:0 0 14px;padding:2px 0 8px;scrollbar-width:thin}.section-filter-tabs button{display:flex;flex:0 0 auto;align-items:center;gap:8px;border:1px solid #e1e4e2;background:#fff;padding:8px 14px;color:var(--ink);font-size:.78rem}.section-filter-tabs button.active{border-color:var(--brand);background:var(--brand);color:#fff}.section-filter-tabs img{width:25px;height:25px;border-radius:50%;object-fit:cover}.category-api-image{width:112px!important;height:105px!important;object-fit:contain}.category-api-placeholder{display:grid!important;width:112px!important;height:105px!important;place-items:center;background:#f5f7f5!important;color:var(--brand)}.category-api-message{display:grid;min-height:172px;border:1px solid #e1e4e2;place-items:center;background:#fff;color:#78817d}.category-api-error{color:#b84d42}@media(max-width:575.98px){.section-filter-tabs button{padding:7px 11px}.category-api-image,.category-api-placeholder{width:112px!important;height:92px!important}}
.flyout-section-image{width:58px;height:58px;margin-bottom:12px;object-fit:contain}
.sidebar-section-image{flex:0 0 22px;width:22px;height:22px;margin-right:12px;object-fit:contain}
</style>
