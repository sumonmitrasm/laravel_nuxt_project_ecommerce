import type { Ref } from 'vue'

export type PageSeoData = {
  title: string
  description: string | null
  keywords: string | null
  robots: string
  canonical: string | null
  image: string | null
  favicon: string | null
  type: 'website' | 'product'
  schema: string | Record<string, unknown> | null
}

export const usePageSeo = (seo: Ref<PageSeoData | null | undefined>) => {
  const schema = computed<Record<string, unknown> | null>(() => {
    const value = seo.value?.schema

    if (! value) return null
    if (typeof value !== 'string') return value

    try {
      const parsed = JSON.parse(value)
      return parsed && typeof parsed === 'object' && ! Array.isArray(parsed)
        ? parsed
        : null
    } catch {
      return null
    }
  })

  useSeoMeta({
    title: () => seo.value?.title || 'NovaCart',
    description: () => seo.value?.description || undefined,
    robots: () => seo.value?.robots || 'index, follow',
    ogTitle: () => seo.value?.title || 'NovaCart',
    ogDescription: () => seo.value?.description || undefined,
    ogUrl: () => seo.value?.canonical || undefined,
    ogImage: () => seo.value?.image || undefined,
    ogType: 'website',
    twitterCard: 'summary_large_image',
    twitterTitle: () => seo.value?.title || 'NovaCart',
    twitterDescription: () => seo.value?.description || undefined,
    twitterImage: () => seo.value?.image || undefined,
  })

  useHead(() => ({
    meta: seo.value?.keywords
      ? [{ name: 'keywords', content: seo.value.keywords }]
      : [],
    link: [
      ...(seo.value?.canonical
        ? [{
            key: 'page-canonical',
            rel: 'canonical' as const,
            href: seo.value.canonical,
          }]
        : []),
    ],
    script: schema.value
      ? [{
          key: 'page-schema',
          type: 'application/ld+json',
          textContent: JSON.stringify(schema.value).replaceAll('<', '\u003C'),
        }]
      : [],
  }))
}
