export type ShippingMethod = { id:number; name:string; code:string; description:string|null; charge:string; delivery_time:string|null; icon:string|null }
type ShippingMethodsResponse = { status:boolean; shipping_methods:ShippingMethod[] }

export const useShippingMethods = () => {
  const config = useRuntimeConfig()
  const shippingMethods = useState<ShippingMethod[]>('shipping-methods', () => [])
  const fetchShippingMethods = async () => {
    const response = await $fetch<ShippingMethodsResponse>('/shipping-methods', { baseURL: config.public.apiBase })
    shippingMethods.value = Array.isArray(response.shipping_methods) ? response.shipping_methods : []
    return shippingMethods.value
  }
  return { shippingMethods, fetchShippingMethods }
}
