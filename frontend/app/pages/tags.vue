<script setup>
const config = useRuntimeConfig()
const route = useRoute()

const selectedSlug = computed(() => route.query.slug || '')

const tagApiUrl = computed(() => {
    if (selectedSlug.value) {
        return `${config.public.apiBase}/tags/${selectedSlug.value}?page=${route.query.page || 1}`
    }

    return `${config.public.apiBase}/tags`
})

const { data, pending, error } = await useFetch(tagApiUrl)

const tags = computed(() => data.value?.tags || [])
const selectedTag = computed(() => data.value?.tag || null)
const tagBlogs = computed(() => data.value?.blogs?.data || [])
const pagination = computed(() => data.value?.blogs || {})

const formatDate = (date) => {
    if (!date) return ''

    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    })
}
</script>

<template>
    <main>
        <section class="tag-hero">
            <div class="container">
                <span class="blog-eyebrow">EXPLORE THE JOURNAL</span>
                <h1>{{ selectedTag ? selectedTag.name : 'Find your next idea.' }}</h1>
                <p v-if="selectedTag">{{ selectedTag.description || `Blogs filed under ${selectedTag.name}.` }}</p>
                <p v-else>Browse helpful stories by the topics you care about.</p>
            </div>
        </section>

        <section class="container tag-directory">
            <p v-if="pending" class="tag-message">Loading...</p>
            <p v-else-if="error" class="tag-message">Content could not be loaded.</p>

            <template v-else-if="selectedTag">
                <div class="tag-result-head">
                    <div>
                        <span>BROWSE ARTICLES</span>
                        <h2>{{ selectedTag.name }} Blogs</h2>
                    </div>
                    <NuxtLink to="/tags">View all topics</NuxtLink>
                </div>

                <div v-if="tagBlogs.length" class="tag-blog-grid">
                    <NuxtLink
                        v-for="blog in tagBlogs"
                        :key="blog.id"
                        :to="`/blogDetail?id=${blog.id}&slug=${blog.slug}`"
                        class="tag-blog-card"
                    >
                        <div class="tag-blog-image">
                            <img v-if="blog.image_url" :src="blog.image_url" :alt="blog.title">
                            <i v-else class="bi bi-image"></i>
                        </div>
                        <div class="tag-blog-body">
                            <small>{{ formatDate(blog.published_at) }}</small>
                            <h3>{{ blog.title }}</h3>
                            <p>{{ blog.excerpt }}</p>
                            <span>Read story <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </NuxtLink>
                </div>

                <p v-else class="tag-message">No blogs found under this tag.</p>

                <nav v-if="pagination.last_page > 1" class="tag-pages">
                    <NuxtLink
                        v-for="pageNumber in pagination.last_page"
                        :key="pageNumber"
                        :class="{ active: pageNumber === pagination.current_page }"
                        :to="`/tags?slug=${selectedTag.slug}&page=${pageNumber}`"
                    >{{ pageNumber }}</NuxtLink>
                </nav>
            </template>

            <template v-else>
                <div class="blog-section-head">
                    <div>
                        <span>ALL TOPICS</span>
                        <h2>Browse by interest</h2>
                    </div>
                    <p>Choose a topic to see all blogs published under it..</p>
                </div>

                <div class="tag-grid">
                    <NuxtLink
                        v-for="tag in tags"
                        :key="tag.id"
                        class="tag-tile tone-lilac"
                        :to="`/tags?slug=${tag.slug}`"
                    >
                        <span>
                            <img v-if="tag.image_url" :src="tag.image_url" :alt="tag.name">
                            <i v-else class="bi bi-tags"></i>
                        </span>
                        <div>
                            <small>{{ tag.blogs_count }} articles</small>
                            <h3>{{ tag.name }}</h3>
                            <p>{{ tag.description || `Read the latest ${tag.name} stories.` }}</p>
                        </div>
                        <i class="bi bi-arrow-up-right"></i>
                    </NuxtLink>
                </div>
            </template>
        </section>
    </main>
</template>

<style scoped>
.tag-message { padding: 45px 20px; text-align: center; background: #f5f7f6; color: #78827d; }
.tag-result-head { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: end; gap: 25px; }
.tag-result-head span { color: #ff5947; font-size: 10px; font-weight: 800; letter-spacing: 1.3px; }
.tag-result-head h2 { margin: 7px 0 0; font-size: 34px; }
.tag-result-head a { color: #ff5947; font-size: 13px; font-weight: 700; text-decoration: none; }
.tag-blog-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
.tag-blog-card { min-width: 0; border: 1px solid #e2e6e3; background: #fff; color: #15221c; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease; }
.tag-blog-card:hover { color: #15221c; transform: translateY(-4px); box-shadow: 0 15px 32px rgba(20, 32, 27, .08); }
.tag-blog-image { height: 220px; display: grid; place-items: center; overflow: hidden; background: #f3f5f4; }
.tag-blog-image img { width: 100%; height: 100%; display: block; object-fit: contain; }
.tag-blog-image > i { color: #9aa39e; font-size: 45px; }
.tag-blog-body { padding: 20px; }
.tag-blog-body small { color: #929b96; font-size: 10px; text-transform: uppercase; }
.tag-blog-body h3 { margin: 9px 0; display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; font-size: 18px; line-height: 1.35; }
.tag-blog-body p { margin: 0; display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; color: #78827d; font-size: 12px; line-height: 1.65; }
.tag-blog-body > span { margin-top: 17px; display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 700; }
.tag-pages { margin-top: 40px; display: flex; justify-content: center; gap: 8px; }
.tag-pages a { width: 38px; height: 38px; display: grid; place-items: center; border: 1px solid #dfe4e1; color: #53605a; text-decoration: none; }
.tag-pages a.active, .tag-pages a:hover { border-color: #ff5947; background: #ff5947; color: #fff; }
.tag-tile > span img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
@media (max-width: 991px) { .tag-blog-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 575px) {
    .tag-result-head { align-items: flex-start; flex-direction: column; }
    .tag-blog-grid { grid-template-columns: 1fr; }
    .tag-blog-image { height: 240px; }
}
</style>