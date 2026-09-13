<script setup>
const config = useRuntimeConfig()
const route = useRoute()

const page = computed(() => Number(route.query.page || 1))

const { data, pending, error } = await useFetch(`${config.public.apiBase}/blog`, {
    query: { page }
})

const blogs = computed(() => data.value?.blogs?.data || [])
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
        <section class="blog-masthead">
            <div class="container">
                <div>
                    <span class="blog-eyebrow">NOVACART JOURNAL</span>
                    <h1>Ideas for better<br>everyday living.</h1>
                    <p>Useful buying guides, fresh product stories and thoughtful inspiration curated by our team.</p>
                </div>
                <NuxtLink class="blog-feature" to="/blog#latest">
                    <span>Editor's pick</span>
                    <strong>Fresh stories, useful guides and product inspiration</strong>
                    <em>Explore now <i class="bi bi-arrow-up-right"></i></em>
                </NuxtLink>
            </div>
        </section>

        <section id="latest" class="container blog-content">
            <div class="blog-section-head">
                <div>
                    <span>THE LATEST</span>
                    <h2>Stories worth your time</h2>
                </div>
                <p>Practical advice and considered recommendations from people who care about the details.</p>
            </div>

            <p v-if="pending" class="blog-message">Loading blogs...</p>
            <p v-else-if="error" class="blog-message">Blogs could not be loaded.</p>

            <div v-else-if="blogs.length" class="blog-grid">
                <article class="blog-card blog-card-lead">
                    <NuxtLink class="blog-card-image" :to="`/blogDetail?id=${blogs[0].id}&slug=${blogs[0].slug}`">
                        <img v-if="blogs[0].image_url" :src="blogs[0].image_url" :alt="blogs[0].title">
                        <i v-else class="bi bi-image"></i>
                    </NuxtLink>
                    <div class="blog-card-body">
                        <div class="blog-meta">Blog <i></i> {{ formatDate(blogs[0].published_at) }}</div>
                        <h3><NuxtLink :to="`/blogDetail?id=${blogs[0].id}&slug=${blogs[0].slug}`">{{ blogs[0].title }}</NuxtLink></h3>
                        <p>{{ blogs[0].excerpt }}</p>
                        <NuxtLink class="blog-read" :to="`/blogDetail?id=${blogs[0].id}&slug=${blogs[0].slug}`">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                    </div>
                </article>

                <div class="blog-grid-right">
                    <article v-for="blog in blogs.slice(1, 3)" :key="blog.id" class="blog-card">
                        <NuxtLink class="blog-card-image" :to="`/blogDetail?id=${blog.id}&slug=${blog.slug}`">
                            <img v-if="blog.image_url" :src="blog.image_url" :alt="blog.title">
                            <i v-else class="bi bi-image"></i>
                        </NuxtLink>
                        <div class="blog-card-body">
                            <div class="blog-meta">Blog <i></i> {{ formatDate(blog.published_at) }}</div>
                            <h3><NuxtLink :to="`/blogDetail?id=${blog.id}&slug=${blog.slug}`">{{ blog.title }}</NuxtLink></h3>
                            <p>{{ blog.excerpt }}</p>
                            <NuxtLink class="blog-read" :to="`/blogDetail?id=${blog.id}&slug=${blog.slug}`">Read story <i class="bi bi-arrow-right"></i></NuxtLink>
                        </div>
                    </article>
                </div>
            </div>

            <p v-else class="blog-message">No blogs found.</p>

            <nav v-if="pagination.last_page > 1" class="blog-pagination" aria-label="Blog pages">
                <NuxtLink
                    v-for="pageNumber in pagination.last_page"
                    :key="pageNumber"
                    :class="{ active: pageNumber === pagination.current_page }"
                    :to="`/blog?page=${pageNumber}`"
                >{{ pageNumber }}</NuxtLink>
            </nav>
        </section>
    </main>
</template>