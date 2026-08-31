export type CartOption = {
  name: string | null
  value: string
  color_code: string | null
}

export type CartItem = {
  id: number
  product_id: number
  product_variant_id: number | null
  name: string
  code: string
  image_url: string | null
  sku: string | null
  regular_price: number
  unit_price: number
  discount_percentage: number
  quantity: number
  maximum_quantity: number
  stock: number | null
  available: boolean
  options: CartOption[]
  line_total: number
}

export type CartResponse = {
  status: boolean
  cart_id: number | null
  cart_count: number
  items_count: number
  items: CartItem[]
  summary: {
    subtotal: number
    discount: number
    shipping: number
    total: number
  }
  message?: string
}

type AddCartPayload = {
  product_id: number
  product_variant_id: number | null
  quantity: number
}

export const useCart = () => {
  const config = useRuntimeConfig()

  const cart = useState<CartResponse | null>(
    'shopping-cart',
    () => null
  )

  const cartCount = useState<number>(
    'cart-count',
    () => 0
  )

  const cartLoaded = useState<boolean>(
    'cart-loaded',
    () => false
  )

  const guestToken = useCookie<string | null>(
    'guest_cart_token',
    {
      maxAge: 60 * 60 * 24 * 30,
      sameSite: 'lax',
    }
  )

  const ensureGuestToken = () => {
    if (! guestToken.value && import.meta.client) {
      guestToken.value = crypto.randomUUID()
    }

    return guestToken.value
  }

  const requestHeaders = () => {
    const token = ensureGuestToken()

    if (! token) {
      throw new Error('Guest cart token could not be created.')
    }

    return {
      'X-Guest-Cart-Token': token,
    }
  }

  const syncCart = (response: CartResponse) => {
    cart.value = response
    cartCount.value = Number(response.cart_count) || 0
    cartLoaded.value = true

    return response
  }

  const fetchCart = async (force = false) => {
    if (! import.meta.client) return

    if (cartLoaded.value && ! force) {
      return cart.value
    }

    const response = await $fetch<CartResponse>('/cart', {
      baseURL: config.public.apiBase,
      headers: requestHeaders(),
    })

    return syncCart(response)
  }

  const addToCart = async (payload: AddCartPayload) => {
    const response = await $fetch<CartResponse>(
      '/cart/items',
      {
        baseURL: config.public.apiBase,
        method: 'POST',
        headers: requestHeaders(),
        body: payload,
      }
    )

    return syncCart(response)
  }

  const updateCartItem = async (
    itemId: number,
    quantity: number
  ) => {
    const response = await $fetch<CartResponse>(
      `/cart/items/${itemId}`,
      {
        baseURL: config.public.apiBase,
        method: 'PATCH',
        headers: requestHeaders(),
        body: { quantity },
      }
    )

    return syncCart(response)
  }

  const removeCartItem = async (itemId: number) => {
    const response = await $fetch<CartResponse>(
      `/cart/items/${itemId}`,
      {
        baseURL: config.public.apiBase,
        method: 'DELETE',
        headers: requestHeaders(),
      }
    )

    return syncCart(response)
  }

  const clearCart = async () => {
    const response = await $fetch<CartResponse>('/cart', {
      baseURL: config.public.apiBase,
      method: 'DELETE',
      headers: requestHeaders(),
    })

    return syncCart(response)
  }

  return {
    cart,
    cartCount,
    cartLoaded,
    fetchCart,
    addToCart,
    updateCartItem,
    removeCartItem,
    clearCart,
  }
}