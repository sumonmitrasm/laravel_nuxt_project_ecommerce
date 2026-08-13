<template>
    <main>
        <section class="blog-masthead">
            <div class="container">
                <div>
                    <span class="blog-eyebrow">NOVACART JOURNAL</span>
                    <h1>Ideas for better<br />everyday living.</h1>
                    <p>Useful buying guides, fresh product stories and thoughtful inspiration—curated by our team.</p>
                </div>
                <NuxtLink class="blog-feature" to="/blog#latest"
                    ><span>Editor’s pick</span><strong>How to build a smarter workspace in 2026</strong
                    ><em>8 min read <i class="bi bi-arrow-up-right"></i></em
                ></NuxtLink>
            </div>
        </section>
        <div class="blog-topic-bar">
            <div class="container">
                <NuxtLink class="active" to="/blog">All stories</NuxtLink><NuxtLink to="/tags#technology">Technology</NuxtLink
                ><NuxtLink to="/tags#home">Home &amp; Living</NuxtLink><NuxtLink to="/tags#style">Style</NuxtLink
                ><NuxtLink to="/tags#guides">Buying guides</NuxtLink>
            </div>
        </div>
        <section class="container blog-content" id="latest">
            <div class="blog-section-head">
                <div>
                    <span>THE LATEST</span>
                    <h2>Stories worth your time</h2>
                </div>
                <p>Practical advice and considered recommendations from people who care about the details.</p>
            </div>
            <div class="blog-grid">
                <article class="blog-card blog-card-lead">
                    <NuxtLink class="blog-card-image tone-lilac" to="/blog?article=laptop-guide"
                        ><i class="bi bi-laptop"></i><span>Technology</span></NuxtLink
                    >
                    <div class="blog-card-body">
                        <div class="blog-meta">Technology <i></i> 8 min read</div>
                        <h3><NuxtLink to="/blog?article=laptop-guide">A practical guide to choosing your next everyday laptop</NuxtLink></h3>
                        <p>Performance, battery life and display quality explained without the unnecessary jargon.</p>
                        <NuxtLink class="blog-read" to="/blog?article=laptop-guide">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                    </div>
                </article>
                <article class="blog-card">
                    <NuxtLink class="blog-card-image tone-mint" to="/blog?article=wireless-headphones"><i class="bi bi-headphones"></i><span>Audio</span></NuxtLink>
                    <div class="blog-card-body">
                        <div class="blog-meta">Buying guide <i></i> 6 min read</div>
                        <h3><NuxtLink to="/blog?article=wireless-headphones">What really matters when buying wireless headphones</NuxtLink></h3>
                        <p>Comfort, codecs and battery life—the features that make a real difference.</p>
                        <NuxtLink class="blog-read" to="/blog?article=wireless-headphones">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                    </div>
                </article>
                <article class="blog-card">
                    <NuxtLink class="blog-card-image tone-peach" to="/blog?article=calmer-home"
                        ><i class="bi bi-house-heart"></i><span>Home</span></NuxtLink
                    >
                    <div class="blog-card-body">
                        <div class="blog-meta">Home &amp; Living <i></i> 5 min read</div>
                        <h3><NuxtLink to="/blog?article=calmer-home">Seven small upgrades that make a home feel calmer</NuxtLink></h3>
                        <p>Simple lighting, storage and comfort ideas for rooms of every size.</p>
                        <NuxtLink class="blog-read" to="/blog?article=calmer-home">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                    </div>
                </article>
                <article class="blog-card">
                    <NuxtLink class="blog-card-image tone-blue" to="/blog?article=smartwatch-features"
                        ><i class="bi bi-smartwatch"></i><span>Wearables</span></NuxtLink
                    >
                    <div class="blog-card-body">
                        <div class="blog-meta">Technology <i></i> 7 min read</div>
                        <h3><NuxtLink to="/blog?article=smartwatch-features">Smartwatch features you will actually use every day</NuxtLink></h3>
                        <p>A realistic look at health, notifications and battery performance.</p>
                        <NuxtLink class="blog-read" to="/blog?article=smartwatch-features">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                    </div>
                </article>
                <article class="blog-card">
                    <NuxtLink class="blog-card-image tone-sand" to="/blog?article=versatile-wardrobe"><i class="bi bi-bag"></i><span>Style</span></NuxtLink>
                    <div class="blog-card-body">
                        <div class="blog-meta">Style <i></i> 4 min read</div>
                        <h3><NuxtLink to="/blog?article=versatile-wardrobe">Build a versatile wardrobe with fewer, better pieces</NuxtLink></h3>
                        <p>Timeless essentials that work harder and stay relevant longer.</p>
                        <NuxtLink class="blog-read" to="/blog?article=versatile-wardrobe">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                    </div>
                </article>
            </div>
            <nav class="blog-pagination" aria-label="Blog pages">
                <NuxtLink class="active" to="/blog?page=1">1</NuxtLink><NuxtLink to="/blog?page=2">2</NuxtLink><NuxtLink to="/blog?page=3">3</NuxtLink
                ><NuxtLink to="/blog?page=2" aria-label="Next page"><i class="bi bi-arrow-right"></i></NuxtLink>
            </nav>
        </section>
        <section class="blog-newsletter">
            <div class="container">
                <div>
                    <span>THE WEEKLY EDIT</span>
                    <h2>Good ideas, delivered.</h2>
                    <p>A concise edit of products, guides and inspiration. No clutter.</p>
                </div>
                <form>
                    <label class="visually-hidden" for="blogEmail">Email address</label
                    ><input id="blogEmail" type="email" placeholder="Your email address" required /><button
                        type="submit"
                    >
                        Subscribe <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
        </section>
    </main>
</template>
