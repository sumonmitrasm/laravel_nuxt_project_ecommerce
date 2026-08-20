type AddCartPayload = {
  product_id: number
  product_variant_id: number | null
  quantity: number
}

export const useCart = () => {
  const config = useRuntimeConfig()
  const cartCount = useState<number>('cart-count', () => 0)
  const cartLoaded = useState<boolean>('cart-loaded', () => false)
  const guestToken = useCookie<string | null>('guest_cart_token', {
    maxAge: 60 * 60 * 24 * 30,
    sameSite: 'lax',
  })

  const ensureGuestToken = () => {
    if (!guestToken.value && import.meta.client) {
      guestToken.value = crypto.randomUUID()
    }

    return guestToken.value
  }

  const requestHeaders = () => {
    const token = ensureGuestToken()
    if (!token) throw new Error('Guest cart token could not be created.')
    return { 'X-Guest-Cart-Token': token }
  }

  const fetchCart = async (force = false) => {
    if (!import.meta.client || (cartLoaded.value && !force)) return

    const response = await $fetch<{ cart_count: number }>('/cart', {
      baseURL: config.public.apiBase,
      headers: requestHeaders(),
    })

    cartCount.value = Number(response.cart_count) || 0
    cartLoaded.value = true
    return response
  }

  const addToCart = async (payload: AddCartPayload) => {
    const response = await $fetch<{ cart_count: number; message: string }>('/cart/items', {
      baseURL: config.public.apiBase,
      method: 'POST',
      headers: requestHeaders(),
      body: payload,
    })

    cartCount.value = Number(response.cart_count) || 0
    cartLoaded.value = true
    return response
  }

  return { cartCount, fetchCart, addToCart }
}
