export const useCatalogMenu = () => {
  const config = useRuntimeConfig()

  return useFetch('/menu', {
    baseURL: config.public.apiBase,
    key: 'catalog-menu'
  })
}
