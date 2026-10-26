export type CatalogSite = {
  name?: string | null
  logo?: string | null
  phone?: string | null
  email?: string | null
  address?: string | null
  description?: string | null
  copyright_year?: string | number | null
}

export type CatalogMenuResponse = {
  site?: CatalogSite
  [key: string]: any
}

export const useCatalogMenu = () => {
  const config = useRuntimeConfig()

  return useFetch<CatalogMenuResponse>('/menu', {
    baseURL: config.public.apiBase,
    key: 'catalog-menu',
    // Reuse generated data only for the first hydration. A refresh must
    // request current settings, even if hydration has not finished yet.
    getCachedData(key, nuxtApp, context) {
      if (context.cause === 'initial' && nuxtApp.isHydrating) {
        return nuxtApp.payload.data[key]
      }
      return undefined
    },
    cache: 'no-store',
    // Do not wait for the menu API before changing pages.
    lazy: true,
  })
}
