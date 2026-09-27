<script setup lang="ts">
import type { PageSeoData } from '~/composables/usePageSeo'

type CatalogSeoResponse = {
  seo?: PageSeoData
}

const { data: catalogData } = useCatalogMenu()

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
