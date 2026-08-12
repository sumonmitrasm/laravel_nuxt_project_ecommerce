<template>
  <header>
     <div class="topbar py-2">
        <div class="container d-flex justify-content-between"><span>Free delivery over ৳3,000</span><span>Help Center ·
                Track Order</span></div>
    </div>
    <nav class="navbar navbar-expand-lg bg-white py-3 sticky-top shadow-sm">
        <div class="container"><button class="navbar-toggler border-0" data-bs-toggle="offcanvas"
                data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button><a class="navbar-brand fs-3"
                href="index.html">NOVA<span class="brand-dot">CART</span></a>
            <form class="d-none d-lg-flex flex-grow-1 mx-5">
                <div class="input-group"><select class="form-select flex-grow-0" style="width:150px">
                        <option>All categories</option>
                        <option>Technology</option>
                        <option>Fashion</option>
                    </select><input class="form-control" placeholder="Search products, brands and categories"><button
                        class="btn btn-brand px-4"><i class="bi bi-search"></i></button></div>
            </form>
            <div class="d-flex gap-2"><a class="nav-icon d-none d-sm-grid" href="login.html"><i
                        class="bi bi-person"></i></a><a class="nav-icon d-none d-sm-grid" href="wishlist.html"><i
                        class="bi bi-heart"></i><span class="badge bg-danger rounded-pill">2</span></a><a
                    class="nav-icon" href="cart.html"><i class="bi bi-cart3 fs-5"></i><span
                        class="badge bg-danger rounded-pill">3</span></a></div>
        </div>
    </nav>
    <div class="category-bar bg-white">
        <div class="container d-flex align-items-center"><a class="btn btn-brand rounded-0 px-4 py-3"
                href="#heroCarousel"><i class="bi bi-grid me-2"></i>Browse Categories</a>
            <ul class="nav">
                <li><a class="nav-link text-dark" href="shop.html">New Arrivals</a></li>
                <li><a class="nav-link text-dark" href="shop.html">Best Sellers</a></li>
                <li><a class="nav-link text-dark" href="shop.html">Offers</a></li>
                <li><a class="nav-link text-dark" href="compare.html">Compare</a></li>
                <li class="nav-item dropdown"><a class="nav-link text-dark dropdown-toggle" href="blog.html"
                        data-bs-toggle="dropdown" aria-expanded="false">Blog</a>
                    <ul class="dropdown-menu blog-nav-menu">
                        <li><a class="dropdown-item" href="blog.html"><i class="bi bi-journal-richtext"></i> Blog</a></li>
                        <li><a class="dropdown-item" href="tags.html"><i class="bi bi-tags"></i> Tags</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
  </header>
</template>