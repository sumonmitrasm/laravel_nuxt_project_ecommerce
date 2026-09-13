<template>
    <main class="magazine-page">
        <div class="container magazine-layout">
            <article class="article-card">
               
                <h1>Secrets Your Parents Never Told You About Fashion</h1>

                <div class="post-meta">
                    <span><i class="bi bi-person-circle"></i> By John Doe</span>
                    <span><i class="bi bi-calendar3"></i> 12 August 2026</span>
                    <span><i class="bi bi-chat-dots"></i> 6 Comments</span>
                    <span><i class="bi bi-clock"></i> 5 Minute Read</span>
                </div>

                <img class="post-cover" src="/assets/images/hero-audio-premium.png" alt="Featured blog image">

                <div class="post-content">
                    <p>
                        Fashion is more than the clothes we wear. It is a simple way to express personality, confidence and creativity. The right choices can make everyday dressing easier and more enjoyable.
                    </p>
                    <p>
                        You do not need a crowded wardrobe to look good. A few comfortable and versatile pieces can create many useful combinations when colour, fit and quality work together.
                    </p>

                    <blockquote>
                        Personal style begins when you choose what feels right for you, rather than following every new trend.
                    </blockquote>

                    <p>
                        Start with the pieces you use most often. Keep them easy to combine, then add one or two distinctive items that bring colour and character to your outfit.
                    </p>

                    <h2>The Ultimate Guide To Fashion</h2>
                    <p>
                        Good style is built slowly. Pay attention to fabric, comfort and fit before labels. Choose items that suit your daily routine and take care of them so they continue to look their best.
                    </p>

                    <div class="post-gallery">
                        <img src="/assets/images/product-1.svg" alt="Fashion inspiration one">
                        <img src="/assets/images/product-2.svg" alt="Fashion inspiration two">
                        <img src="/assets/images/product-3.svg" alt="Fashion inspiration three">
                        <img src="/assets/images/product-4.svg" alt="Fashion inspiration four">
                    </div>
                </div>
            </article>

            <aside class="blog-sidebar">
                <section class="sidebar-box">
                    <h3>Social Networks</h3>
                    <div class="social-list">
                        <a href="#"><i class="bi bi-instagram instagram"></i><span>Instagram</span></a>
                        <a href="#"><i class="bi bi-twitter-x twitter"></i><span>Twitter</span></a>
                        <a href="#"><i class="bi bi-facebook facebook"></i><span>Facebook</span></a>
                        <a href="#"><i class="bi bi-youtube youtube"></i><span>YouTube</span></a>
                        <a href="#"><i class="bi bi-pinterest pinterest"></i><span>Pinterest</span></a>
                        <a href="#"><i class="bi bi-linkedin linkedin"></i><span>LinkedIn</span></a>
                    </div>
                </section>

                <section class="sidebar-box popular-box">
                    <div class="popular-tabs">
                        <button class="active">POPULAR</button>
                        <button>RECENT</button>
                        <button>TRENDY</button>
                    </div>

                    <a href="#" class="popular-post">
                        <img src="/assets/images/product-1.svg" alt="Popular post">
                        <span><strong>Undeniable Proof That You Need Travel</strong><small>06 August 2026</small></span>
                    </a>
                    <a href="#" class="popular-post">
                        <img src="/assets/images/product-2.svg" alt="Popular post">
                        <span><strong>What Food Can Teach Us About Sports</strong><small>04 August 2026</small></span>
                    </a>
                    <a href="#" class="popular-post">
                        <img src="/assets/images/product-3.svg" alt="Popular post">
                        <span><strong>Everything You Need To Know Today</strong><small>01 August 2026</small></span>
                    </a>
                    <a href="#" class="popular-post">
                        <img src="/assets/images/product-4.svg" alt="Popular post">
                        <span><strong>Meet The Style Jobs Of The Tech Industry</strong><small>28 July 2026</small></span>
                    </a>
                </section>

                <section class="sidebar-box">
                    <h3>Tag Cloud</h3>
                    <div class="tag-cloud">
                        <a href="#">FASHION</a><a href="#">HEALTH</a><a href="#">TECHNOLOGY</a>
                        <a href="#">LIFESTYLE</a><a href="#">FOOD</a><a href="#">SPORT</a>
                        <a href="#">PHOTOGRAPHY</a><a href="#">TRAVEL</a><a href="#">STYLE</a>
                    </div>
                </section>
            </aside>
        </div>
    </main>
</template>

<style scoped>
.magazine-page { padding: 40px 0 65px; background: #f5f6fa; color: #1e2026; }
.magazine-layout { max-width: 1120px; display: grid; grid-template-columns: minmax(0, 1.9fr) minmax(260px, .75fr); gap: 24px; align-items: start; }
.article-card, .sidebar-box { background: #fff; border-radius: 6px; box-shadow: 0 5px 22px rgba(32, 37, 47, .06); }
.article-card { padding: 30px; }
.post-category { display: inline-block; padding: 6px 10px; border-radius: 3px; background: #ef3b8f; color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .5px; }
.article-card h1 { max-width: 760px; margin: 16px 0 14px; font-size: clamp(27px, 2.4vw, 36px); line-height: 1.18; letter-spacing: -.8px; }
.post-meta { margin-bottom: 22px; display: flex; flex-wrap: wrap; gap: 17px; color: #969aa3; font-size: 11px; }
.post-meta span { display: inline-flex; align-items: center; gap: 6px; }
.post-cover { width: 100%; height: 370px; display: block; border-radius: 6px; object-fit: cover; background: #edf1ef; }
.post-content { padding-top: 24px; color: #5f626a; font-size: 14px; line-height: 1.8; }
.post-content p { margin: 0 0 22px; }
.post-content h2 { margin: 38px 0 14px; color: #202229; font-size: 23px; line-height: 1.3; }
.post-content blockquote { margin: 32px 0; padding: 25px 30px; border-left: 4px solid #ef3b8f; background: #f8f8fa; color: #353740; font-size: 17px; font-style: italic; font-weight: 600; line-height: 1.7; }
.post-gallery { margin-top: 28px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.post-gallery img { width: 100%; height: 190px; display: block; border-radius: 5px; object-fit: contain; background: #f4f5f7; }
.blog-sidebar { display: grid; gap: 24px; }
.sidebar-box { padding: 21px; }
.sidebar-box h3 { margin: 0 0 16px; color: #22242a; font-size: 18px; }
.social-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 13px; }
.social-list a { display: flex; align-items: center; gap: 8px; color: #50535a; font-size: 11px; text-decoration: none; }
.social-list i { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 4px; color: #fff; }
.instagram { background: #e43a93; }.twitter { background: #2ba9e0; }.facebook { background: #3467b3; }.youtube { background: #ef3434; }.pinterest { background: #cf2435; }.linkedin { background: #1976a9; }
.popular-tabs { margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #eee; }
.popular-tabs button { padding: 9px 3px; border: 0; background: transparent; color: #9a9da5; font-size: 9px; font-weight: 800; }
.popular-tabs button.active { border-bottom: 2px solid #4c64df; color: #4c64df; }
.popular-post { padding: 10px 0; display: grid; grid-template-columns: 62px 1fr; gap: 11px; border-bottom: 1px solid #f0f1f3; color: #24262d; text-decoration: none; }
.popular-post img { width: 62px; height: 55px; object-fit: contain; background: #f4f5f7; }
.popular-post strong, .popular-post small { display: block; }
.popular-post strong { font-size: 11px; line-height: 1.35; }
.popular-post small { margin-top: 5px; color: #a4a7ae; font-size: 8px; }
.tag-cloud { display: flex; flex-wrap: wrap; gap: 8px; }
.tag-cloud a { padding: 7px 10px; border: 1px solid #e2e3e7; border-radius: 3px; color: #71747b; font-size: 9px; font-weight: 700; text-decoration: none; }
.tag-cloud a:hover { border-color: #ef3b8f; color: #ef3b8f; }
@media (max-width: 991px) {
    .magazine-layout { grid-template-columns: 1fr; }
    .blog-sidebar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
    .magazine-page { padding: 22px 0 45px; }
    .article-card { padding: 20px; }
    .article-card h1 { font-size: 30px; }
    .post-cover { height: 280px; }
    .post-gallery, .blog-sidebar { grid-template-columns: 1fr; }
    .post-gallery img { height: 210px; }
    .post-content blockquote { padding: 20px; }
}
</style>