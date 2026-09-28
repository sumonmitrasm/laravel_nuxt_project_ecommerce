export type PlacedOrder = {
  id: number
  order_number: string
  payment_method: 'cod' | 'sslcommerz'
  payment_status: string
  order_status: string
  grand_total: string
  currency: string
}

export type AccountOrder = PlacedOrder & {
  placed_at: string
  items_count: number
}

type OrdersResponse = {
  status: boolean
  total_orders: number
  orders: AccountOrder[]
  pagination: { current_page: number; last_page: number; per_page: number; total: number }
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

type PaymentSessionResponse = {
  status: boolean
  gateway_url: string
}
export const useOrders = () => {
  const config = useRuntimeConfig()
  const xsrfToken = useCookie<string | null>('XSRF-TOKEN')
  const guestToken = useCookie<string | null>('guest_cart_token')
  const orders = useState<AccountOrder[]>('customer-orders', () => [])
  const ordersLoaded = useState<boolean>('customer-orders-loaded', () => false)
  const ordersPagination = useState<OrdersResponse['pagination'] | null>('customer-orders-pagination', () => null)

  const fetchOrders = async (force = false, page = 1) => {
    if (ordersLoaded.value && !force) return orders.value
    const response = await $fetch<OrdersResponse>('/auth/orders', {
      baseURL: config.public.apiBase,
      credentials: 'include',
      query: { page, per_page: 10 },
    })
    orders.value = page === 1 ? response.orders : [...orders.value, ...response.orders]
    ordersPagination.value = response.pagination
    ordersLoaded.value = true
    return orders.value
  }

  const loadMoreOrders = () => fetchOrders(true, (ordersPagination.value?.current_page ?? 1) + 1)

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

  const startSslCommerzPayment = async (orderNumber: string) => {
    await $fetch('/sanctum/csrf-cookie', {
      baseURL: config.public.backendBase,
      credentials: 'include',
    })
    refreshCookie('XSRF-TOKEN')

    return await $fetch<PaymentSessionResponse>(`/auth/orders/${encodeURIComponent(orderNumber)}/payment`, {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
      headers: xsrfToken.value
        ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) }
        : {},
    })
  }
  return { orders, ordersLoaded, ordersPagination, fetchOrders, loadMoreOrders, placeOrder, startSslCommerzPayment }
}
