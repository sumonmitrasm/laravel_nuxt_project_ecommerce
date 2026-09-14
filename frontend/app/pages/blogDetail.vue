<script setup>
const config = useRuntimeConfig()
const route = useRoute()

const blogApiUrl = computed(() => {
    return `${config.public.apiBase}/blog/${route.query.id}/${route.query.slug}`
})

const { data, pending, error } = await useFetch(blogApiUrl)

const blog = computed(() => data.value?.blog || null)
const tags = computed(() => blog.value?.tags || [])

const { data: blogListData } = await useFetch(
    `${config.public.apiBase}/blog`
)

const sidebarBlogs = computed(() => {
    const blogList = blogListData.value?.blogs?.data || []

    return blogList
        .filter(item => item.id !== blog.value?.id)
        .slice(0, 4)
})
const seo = computed(() => data.value?.seo || {})

const pageUrl = useRequestURL()
const shareUrl = computed(() => encodeURIComponent(pageUrl.href))
const shareTitle = computed(() => encodeURIComponent(blog.value?.title || ''))
const linkCopied = ref(false)

const copyBlogLink = async () => {
    if (!import.meta.client) return

    await navigator.clipboard.writeText(window.location.href)
    linkCopied.value = true

    setTimeout(() => {
        linkCopied.value = false
    }, 2000)
}
// console.log('query', route.query)
// console.log('id', route.query.id)
// console.log('slug', route.query.slug)
console.log('blog', blog.value)
console.log('tags', tags.value)
console.log('seo', seo.value)
const formatDate = (date) => {
    if (!date) return ''

    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    })
}

useHead(() => ({
    title: seo.value.title,
    meta: [
        { name: 'description', content: seo.value.description },
        { name: 'keywords', content: seo.value.keywords },
        { name: 'robots', content: seo.value.robots },
        { property: 'og:title', content: seo.value.title },
        { property: 'og:description', content: seo.value.description },
        { property: 'og:image', content: seo.value.image },
        { property: 'og:type', content: seo.value.type }
    ],
    link: [
        { rel: 'canonical', href: seo.value.canonical }
    ]
}))
</script>
<template>
    <main class="magazine-page">
        <div class="container magazine-layout">
            <article class="article-card">
               
                <h5>{{ blog.title }}</h5>

                <div class="post-meta">
                    <span><i class="bi bi-person-circle"></i> By John Doe</span>
                    <span><i class="bi bi-calendar3"></i> {{ formatDate(blog.published_at) }}</span>
                    <span><i class="bi bi-clock"></i> {{ blog.author.name }}</span>
                </div>

                <img class="post-cover" v-if="blog.image_url" :src="blog.image_url" :alt="blog.title">

                <div class="post-content">

                    <blockquote>
                        {{ blog.excerpt }}
                    </blockquote>

                    <p>
                        {{ blog.content }}
                    </p>
                </div>
            </article>

            <aside class="blog-sidebar">
                <section class="sidebar-box">
                    <h3>Share This Blog</h3>
                    <div class="social-list">
                        <a :href="`https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-facebook facebook"></i><span>Facebook</span>
                        </a>
                        <a :href="`https://twitter.com/intent/tweet?url=${shareUrl}&text=${shareTitle}`" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-twitter-x twitter"></i><span>Twitter</span>
                        </a>
                        <a :href="`https://www.linkedin.com/sharing/share-offsite/?url=${shareUrl}`" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-linkedin linkedin"></i><span>LinkedIn</span>
                        </a>
                        <a :href="`https://wa.me/?text=${shareTitle}%20${shareUrl}`" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp whatsapp"></i><span>WhatsApp</span>
                        </a>
                        <button type="button" @click="copyBlogLink">
                            <i class="bi bi-link-45deg copy-link"></i>
                            <span>{{ linkCopied ? 'Copied!' : 'Copy Link' }}</span>
                        </button>
                    </div>
                </section>
                <section class="sidebar-box popular-box">
                    <h3>Blogs</h3>

                    <NuxtLink
                        v-for="sidebarBlog in sidebarBlogs"
                        :key="sidebarBlog.id"
                        :to="`/blogDetail?id=${sidebarBlog.id}&slug=${sidebarBlog.slug}`"
                        class="popular-post"
                    >
                        <img v-if="sidebarBlog.image_url" :src="sidebarBlog.image_url" :alt="sidebarBlog.title">
                        <span v-else class="popular-image-empty"><i class="bi bi-image"></i></span>
                        <span>
                            <strong>{{ sidebarBlog.title }}</strong>
                            <small>{{ formatDate(sidebarBlog.published_at) }}</small>
                        </span>
                    </NuxtLink>
                </section>

                <section class="sidebar-box">
                    <h3>Tag Cloud</h3>
                    <div v-if="tags.length" class="tag-cloud">
                        <NuxtLink
                            v-for="tag in tags"
                            :key="tag.id"
                            :to="`/tags?slug=${tag.slug}`"
                        >
                            {{ tag.name }}
                        </NuxtLink>
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
.post-cover { width: 100%; height: 370px; display: block; border-radius: 6px; object-fit: contain; background: #f7f8f8; }
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
.social-list a, .social-list button { display: flex; align-items: center; gap: 8px; color: #50535a; font-size: 11px; text-decoration: none; }
.social-list button { padding: 0; border: 0; background: transparent; color: #50535a; font-size: 11px; text-align: left; }
.social-list i { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 4px; color: #fff; }
.whatsapp { background: #24a85a; }.copy-link { background: #6f7782; }.twitter { background: #2ba9e0; }.facebook { background: #3467b3; }.youtube { background: #ef3434; }.pinterest { background: #cf2435; }.linkedin { background: #1976a9; }
.popular-tabs { margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #eee; }
.popular-tabs button { padding: 9px 3px; border: 0; background: transparent; color: #9a9da5; font-size: 9px; font-weight: 800; }
.popular-tabs button.active { border-bottom: 2px solid #4c64df; color: #4c64df; }
.popular-post { padding: 10px 0; display: grid; grid-template-columns: 62px 1fr; gap: 11px; border-bottom: 1px solid #f0f1f3; color: #24262d; text-decoration: none; }
.popular-post img, .popular-image-empty { width: 62px; height: 55px; object-fit: cover; background: #f4f5f7; }
.popular-image-empty { display: grid; place-items: center; color: #90969f; }
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