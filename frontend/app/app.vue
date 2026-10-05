<script setup lang="ts">
import type { PageSeoData } from '~/composables/usePageSeo'

type CatalogSeoResponse = {
  seo?: PageSeoData
}

const { data: catalogData, refresh: refreshCatalog } = useCatalogMenu()
const route = useRoute()

// Prerendered HTML may contain settings from deployment day. Refresh only
// after hydration, and when navigating, so server and browser markup agree.
onMounted(() => { void refreshCatalog({ dedupe: 'defer' }) })
// Search, sort and pagination queries do not change the shared site menu.
watch(() => route.path, () => { void refreshCatalog({ dedupe: 'defer' }) })

const favicon = computed(() =>
  (catalogData.value as CatalogSeoResponse | null)?.seo?.favicon
)

useHead(() => ({
  link: favicon.value
    ? [{
        key: 'site-favicon',
        rel: 'icon' as const,
        href: favicon.value,
      }]
    : [],
}))
</script>
<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <AppToast />
  </div>
</template>

<style>
/* Hide only the page scrollbar; keep wheel, touch and keyboard scrolling. */
html {
  scrollbar-width: none;
}

html::-webkit-scrollbar {
  display: none;
}
</style>
