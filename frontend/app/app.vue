<script setup lang="ts">
import type { PageSeoData } from '~/composables/usePageSeo'

type CatalogSeoResponse = {
  seo?: PageSeoData
}

const { data: catalogData } = await useCatalogMenu()

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
  </div>
</template>
