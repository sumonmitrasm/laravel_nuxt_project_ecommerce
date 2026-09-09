export type PlacedOrder = {
  id: number
  order_number: string
  payment_method: 'cod' | 'sslcommerz'
  payment_status: string
  order_status: string
  grand_total: string
  currency: string
}

type PlaceOrderPayload = {
  address_id: number
  shipping_method_id: number
  payment_method: 'cod' | 'sslcommerz'
  customer_note?: string | null
}

type PlaceOrderResponse = {
  status: boolean
  message: string
  order: PlacedOrder
}

export const useOrders = () => {
  const config = useRuntimeConfig()
  const xsrfToken = useCookie<string | null>('XSRF-TOKEN')
  const guestToken = useCookie<string | null>('guest_cart_token')

  const placeOrder = async (payload: PlaceOrderPayload) => {
    await $fetch('/sanctum/csrf-cookie', {
      baseURL: config.public.backendBase,
      credentials: 'include',
    })
    refreshCookie('XSRF-TOKEN')

    return await $fetch<PlaceOrderResponse>('/auth/orders', {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
      headers: {
        ...(guestToken.value ? { 'X-Guest-Cart-Token': guestToken.value } : {}),
        ...(xsrfToken.value ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) } : {}),
      },
      body: payload,
    })
  }

  return { placeOrder }
}
