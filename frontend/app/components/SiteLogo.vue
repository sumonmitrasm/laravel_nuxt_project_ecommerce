<script setup lang="ts">
type SiteIdentity = {
  name?: string | null
  logo?: string | null
}

type CatalogSiteResponse = {
  site?: SiteIdentity
}

const { data: catalogData } = await useCatalogMenu()

const site = computed(() =>
  (catalogData.value as CatalogSiteResponse | null)?.site
)

const siteName = computed(() => site.value?.name || 'NovaCart')
</script>

<template>
  <NuxtLink to="/" :aria-label="`${siteName} home`">
    <img
      v-if="site?.logo"
      class="site-logo-image"
      :src="site.logo"
      :alt="siteName"
      width="190"
      height="52"
    >
    <span v-else>{{ siteName }}</span>
  </NuxtLink>
</template>

<style scoped>
.site-logo-image {
  display: block;
  width: auto;
  max-width: 190px;
  height: 52px;
  object-fit: contain;
}

@media (max-width: 575.98px) {
  .site-logo-image {
    max-width: 145px;
    height: 42px;
  }
}
</style>
