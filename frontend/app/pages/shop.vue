<template>
    <main>
        <section class="shop-hero">
            <div class="container text-center">
                <h1>Grid 4 Columns</h1>
                <p>Shop</p>
            </div>
        </section>
        <div class="shop-breadcrumb">
            <div class="container">
                <NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><NuxtLink to="/shop">Shop</NuxtLink
                ><i class="bi bi-chevron-right"></i><span>Grid 4 Columns</span>
            </div>
        </div>
        <section class="shop-catalog container py-4 py-lg-5">
            <div class="row g-4">
                <aside class="col-lg-3 shop-sidebar" id="shopFilters">
                    <div class="shop-filter-head"><strong>Filters</strong><button type="reset">Clean All</button></div>
                    <div class="shop-filter-group">
                        <button
                            class="shop-filter-title"
                            data-bs-toggle="collapse"
                            data-bs-target="#filterCategory"
                            aria-expanded="true"
                        >
                            Category <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterCategory">
                            <label><input type="checkbox" /> Electronics <span>24</span></label
                            ><label><input type="checkbox" /> Fashion <span>18</span></label
                            ><label><input type="checkbox" /> Home &amp; Living <span>12</span></label
                            ><label><input type="checkbox" /> Accessories <span>9</span></label
                            ><label><input type="checkbox" /> Sports <span>7</span></label>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button
                            class="shop-filter-title"
                            data-bs-toggle="collapse"
                            data-bs-target="#filterSubcategory"
                            aria-expanded="true"
                        >
                            Subcategory <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterSubcategory">
                            <label><input type="checkbox" /> Smartphones <span>8</span></label
                            ><label><input type="checkbox" /> Computers <span>6</span></label
                            ><label><input type="checkbox" /> Audio <span>5</span></label
                            ><label><input type="checkbox" /> Cameras <span>4</span></label
                            ><label><input type="checkbox" /> Wearables <span>6</span></label>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button
                            class="shop-filter-title"
                            data-bs-toggle="collapse"
                            data-bs-target="#filterPrice"
                            aria-expanded="true"
                        >
                            Price <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse show" id="filterPrice">
                            <div class="dual-price-filter" data-price-filter data-min="0" data-max="100000">
                                <strong class="price-filter-selection" data-price-output>৳0 – ৳100,000</strong>
                                <div class="dual-range">
                                    <div class="dual-range-rail"></div>
                                    <div class="dual-range-progress" data-price-progress></div>
                                    <input
                                        type="range"
                                        min="0"
                                        max="100000"
                                        step="500"
                                        value="0"
                                        data-price-min
                                        aria-label="Minimum price"
                                    />
                                    <input
                                        type="range"
                                        min="0"
                                        max="100000"
                                        step="500"
                                        value="100000"
                                        data-price-max
                                        aria-label="Maximum price"
                                    />
                                </div>
                                <button class="price-filter-reset" type="button" data-price-reset>
                                    Reset price range
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="shop-filter-group">
                        <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#filterRating">
                            Customer rating <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse" id="filterRating">
                            <label><input type="checkbox" /> <b class="filter-stars">★★★★★</b></label
                            ><label><input type="checkbox" /> <b class="filter-stars">★★★★</b> &amp; up</label>
                        </div>
                    </div>
                </aside>
                <div class="col-lg-9">
                    <div class="shop-toolbar">
                        <button
                            class="mobile-filter-btn d-lg-none"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#mobileShopFilters"
                        >
                            <i class="bi bi-sliders"></i> Filters
                        </button>
                        <div>Showing <strong>1–8</strong> of <strong>56</strong> products</div>
                        <div class="shop-sort">
                            <label for="sortProducts">Sort by:</label
                            ><select id="sortProducts">
                                <option>Most Popular</option>
                                <option>Newest</option>
                                <option>Price: Low to High</option>
                                <option>Price: High to Low</option></select
                            ><button class="active" aria-label="Grid view">
                                <i class="bi bi-grid-3x3-gap-fill"></i></button
                            ><button aria-label="List view"><i class="bi bi-list"></i></button>
                        </div>
                    </div>
                    <div class="shop-products">
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 0; --y: 0"></span
                                ><em class="product-label new">New</em
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Computers</small>
                                <h2><NuxtLink to="/product">NovaBook Air Laptop</NuxtLink></h2>
                                <div class="shop-price">৳74,909</div>
                                <div class="shop-rating">★★★★★ <span>(8 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 1; --y: 0"></span
                                ><em class="product-label top">Top</em
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Smartphones</small>
                                <h2><NuxtLink to="/product">Nova X Pro Smartphone</NuxtLink></h2>
                                <div class="shop-price">৳54,500</div>
                                <div class="shop-rating">★★★★★ <span>(12 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 2; --y: 0"></span
                                ><em class="product-label sale">Sale</em
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Audio</small>
                                <h2><NuxtLink to="/product">Pulse Wireless Headphones</NuxtLink></h2>
                                <div class="shop-price">৳8,490 <del>৳9,900</del></div>
                                <div class="shop-rating">★★★★☆ <span>(6 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 3; --y: 0"></span
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Wearables</small>
                                <h2><NuxtLink to="/product">Orbit Smart Watch S2</NuxtLink></h2>
                                <div class="shop-price">৳6,990</div>
                                <div class="shop-rating">★★★★★ <span>(9 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 0; --y: 1"></span
                                ><em class="product-label sale">Sale</em
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>TV &amp; Audio</small>
                                <h2><NuxtLink to="/product">Vision Class 4K Smart TV</NuxtLink></h2>
                                <div class="shop-price">৳69,999 <del>৳79,999</del></div>
                                <div class="shop-rating">★★★★☆ <span>(10 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 1; --y: 1"></span
                                ><em class="product-label new">New</em
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Fashion</small>
                                <h2><NuxtLink to="/product">Everyday Leather Tote</NuxtLink></h2>
                                <div class="shop-price">৳4,850</div>
                                <div class="shop-rating">★★★★★ <span>(5 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 2; --y: 2"></span
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Sports</small>
                                <h2><NuxtLink to="/product">Aero Cycling Helmet</NuxtLink></h2>
                                <div class="shop-price">৳3,750</div>
                                <div class="shop-rating">★★★★☆ <span>(4 Reviews)</span></div>
                            </div>
                        </article>
                        <article class="shop-product">
                            <div class="shop-product-media">
                                <span class="shop-product-photo" style="--x: 1; --y: 2"></span
                                ><em class="product-label top">Top</em
                                ><button class="shop-heart" type="button"><i class="bi bi-heart"></i></button
                                ><span class="shop-cart"><i class="bi bi-cart3"></i> Add to cart</span>
                            </div>
                            <div class="shop-product-info">
                                <small>Cameras</small>
                                <h2><NuxtLink to="/product">Pocket Mirrorless Camera</NuxtLink></h2>
                                <div class="shop-price">৳42,800</div>
                                <div class="shop-rating">★★★★★ <span>(7 Reviews)</span></div>
                            </div>
                        </article>
                    </div>
                    <nav class="shop-pagination" aria-label="Product pages">
                        <span class="disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span
                        ><NuxtLink class="active" to="/shop?page=1">1</NuxtLink><NuxtLink to="/shop?page=2">2</NuxtLink
                        ><NuxtLink to="/shop?page=3">3</NuxtLink
                        ><NuxtLink to="/shop?page=2" aria-label="Next page"
                            ><i class="bi bi-chevron-right"></i
                        ></NuxtLink>
                    </nav>
                </div>
            </div>
        </section>
    </main>

    <div class="offcanvas offcanvas-start shop-filter-drawer" id="mobileShopFilters">
        <div class="offcanvas-header">
            <h5>Filters</h5>
            <button class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="shop-filter-head"><strong>Filters</strong><button type="reset">Clean All</button></div>
            <div class="shop-filter-group">
                <button
                    class="shop-filter-title"
                    data-bs-toggle="collapse"
                    data-bs-target="#mobileFilterPanel0"
                    aria-expanded="true"
                >
                    Category <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel0">
                    <label><input type="checkbox" /> Electronics <span>24</span></label
                    ><label><input type="checkbox" /> Fashion <span>18</span></label
                    ><label><input type="checkbox" /> Home &amp; Living <span>12</span></label
                    ><label><input type="checkbox" /> Accessories <span>9</span></label
                    ><label><input type="checkbox" /> Sports <span>7</span></label>
                </div>
            </div>
            <div class="shop-filter-group">
                <button
                    class="shop-filter-title"
                    data-bs-toggle="collapse"
                    data-bs-target="#mobileFilterPanel1"
                    aria-expanded="true"
                >
                    Subcategory <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel1">
                    <label><input type="checkbox" /> Smartphones <span>8</span></label
                    ><label><input type="checkbox" /> Computers <span>6</span></label
                    ><label><input type="checkbox" /> Audio <span>5</span></label
                    ><label><input type="checkbox" /> Cameras <span>4</span></label
                    ><label><input type="checkbox" /> Wearables <span>6</span></label>
                </div>
            </div>
            <div class="shop-filter-group">
                <button
                    class="shop-filter-title"
                    data-bs-toggle="collapse"
                    data-bs-target="#mobileFilterPanel2"
                    aria-expanded="true"
                >
                    Price <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="mobileFilterPanel2">
                    <div class="dual-price-filter" data-price-filter data-min="0" data-max="100000">
                        <strong class="price-filter-selection" data-price-output>৳0 – ৳100,000</strong>
                        <div class="dual-range">
                            <div class="dual-range-rail"></div>
                            <div class="dual-range-progress" data-price-progress></div>
                            <input
                                type="range"
                                min="0"
                                max="100000"
                                step="500"
                                value="0"
                                data-price-min
                                aria-label="Minimum price"
                            />
                            <input
                                type="range"
                                min="0"
                                max="100000"
                                step="500"
                                value="100000"
                                data-price-max
                                aria-label="Maximum price"
                            />
                        </div>
                        <button class="price-filter-reset" type="button" data-price-reset>Reset price range</button>
                    </div>
                </div>
            </div>
            <div class="shop-filter-group">
                <button class="shop-filter-title" data-bs-toggle="collapse" data-bs-target="#mobileFilterPanel3">
                    Customer rating <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="mobileFilterPanel3">
                    <label><input type="checkbox" /> <b class="filter-stars">★★★★★</b></label
                    ><label><input type="checkbox" /> <b class="filter-stars">★★★★</b> &amp; up</label>
                </div>
            </div>
        </div>
    </div>
</template>
