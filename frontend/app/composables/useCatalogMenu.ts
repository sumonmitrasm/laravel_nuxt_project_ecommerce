export const useCatalogMenu = () => {
  const config = useRuntimeConfig()

  return useFetch('/menu', {
    baseURL: config.public.apiBase,
    key: 'catalog-menu',
    // Do not wait for the menu API before changing pages.
    lazy: true,
  })
}
