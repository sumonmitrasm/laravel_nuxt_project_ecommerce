<template>
    <main>
        <section class="tag-hero">
            <div class="container">
                <span class="blog-eyebrow">EXPLORE THE JOURNAL</span>
                <h1>Find your next idea.</h1>
                <p>Browse helpful stories by the topics you care about.</p>
                <form class="tag-search" action="blog.html">
                    <i class="bi bi-search"></i
                    ><input
                        type="search"
                        placeholder="Search articles and topics"
                        aria-label="Search articles"
                    /><button>Search</button>
                </form>
            </div>
        </section>
        <section class="container tag-directory">
            <div class="blog-section-head">
                <div>
                    <span>ALL TOPICS</span>
                    <h2>Browse by interest</h2>
                </div>
                <p>From smarter technology to comfortable homes, every story is grouped to help you find it quickly.</p>
            </div>
            <div class="tag-grid">
                <NuxtLink class="tag-tile tone-lilac" id="technology" to="/blog?tag=technology"
                    ><span><i class="bi bi-cpu"></i></span>
                    <div>
                        <small>12 articles</small>
                        <h3>Technology</h3>
                        <p>Laptops, phones and tools for better work.</p>
                    </div>
                    <i class="bi bi-arrow-up-right"></i
                ></NuxtLink>
                <NuxtLink class="tag-tile tone-mint" to="/blog?tag=audio"
                    ><span><i class="bi bi-headphones"></i></span>
                    <div>
                        <small>8 articles</small>
                        <h3>Audio</h3>
                        <p>Headphones, speakers and listening guides.</p>
                    </div>
                    <i class="bi bi-arrow-up-right"></i
                ></NuxtLink>
                <NuxtLink class="tag-tile tone-peach" id="home" to="/blog?tag=home"
                    ><span><i class="bi bi-house-heart"></i></span>
                    <div>
                        <small>15 articles</small>
                        <h3>Home &amp; Living</h3>
                        <p>Comfortable spaces and useful upgrades.</p>
                    </div>
                    <i class="bi bi-arrow-up-right"></i
                ></NuxtLink>
                <NuxtLink class="tag-tile tone-blue" to="/blog?tag=wearables"
                    ><span><i class="bi bi-smartwatch"></i></span>
                    <div>
                        <small>7 articles</small>
                        <h3>Wearables</h3>
                        <p>Health, activity and connected essentials.</p>
                    </div>
                    <i class="bi bi-arrow-up-right"></i
                ></NuxtLink>
                <NuxtLink class="tag-tile tone-sand" id="style" to="/blog?tag=style"
                    ><span><i class="bi bi-bag"></i></span>
                    <div>
                        <small>10 articles</small>
                        <h3>Style</h3>
                        <p>Thoughtful fashion and everyday carry.</p>
                    </div>
                    <i class="bi bi-arrow-up-right"></i
                ></NuxtLink>
                <NuxtLink class="tag-tile tone-rose" id="guides" to="/blog?tag=buying-guides"
                    ><span><i class="bi bi-journal-check"></i></span>
                    <div>
                        <small>19 articles</small>
                        <h3>Buying Guides</h3>
                        <p>Clear comparisons and confident choices.</p>
                    </div>
                    <i class="bi bi-arrow-up-right"></i
                ></NuxtLink>
            </div>
            <div class="popular-tags">
                <h2>Popular tags</h2>
                <div>
                    <NuxtLink to="/blog?tag=smart-home">#smart-home</NuxtLink><NuxtLink to="/blog?tag=work-from-home">#work-from-home</NuxtLink
                    ><NuxtLink to="/blog?tag=new-arrivals">#new-arrivals</NuxtLink><NuxtLink to="/blog?tag=audio">#audio</NuxtLink
                    ><NuxtLink to="/blog?tag=gift-ideas">#gift-ideas</NuxtLink><NuxtLink to="/blog?tag=sustainable-style">#sustainable-style</NuxtLink
                    ><NuxtLink to="/blog?tag=productivity">#productivity</NuxtLink><NuxtLink to="/blog?tag=under-10000">#under-10000</NuxtLink>
                </div>
            </div>
        </section>
    </main>
</template>
