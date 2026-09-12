export type WishlistItem = {
  id: number
  product_id: number
  name: string
  category_name: string
  image_url: string | null
  regular_price: number
  final_price: number
  has_discount: boolean
  stock: number | null
  in_stock: boolean
}

type WishlistResponse = {
  status: boolean
  count: number
  items?: WishlistItem[]
  message?: string
}

export const useWishlist = () => {
  const config = useRuntimeConfig()
  const xsrfToken = useCookie<string | null>('XSRF-TOKEN')
  const items = useState<WishlistItem[]>('wishlist-items', () => [])
  const wishlistCount = useState<number>('wishlist-count', () => 0)
  const wishlistLoaded = useState<boolean>('wishlist-loaded', () => false)

  const csrf = async () => {
    await $fetch('/sanctum/csrf-cookie', {
      baseURL: config.public.backendBase,
      credentials: 'include',
    })
    refreshCookie('XSRF-TOKEN')
  }

  const headers = () => xsrfToken.value
    ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) }
    : {}

  const fetchWishlist = async (force = false) => {
    if (!import.meta.client) return
    if (wishlistLoaded.value && !force) return items.value

    try {
      const response = await $fetch<WishlistResponse>('/auth/wishlist', {
        baseURL: config.public.apiBase,
        credentials: 'include',
      })
      items.value = response.items ?? []
      wishlistCount.value = response.count
    } catch (error: any) {
      if (error?.status === 401 || error?.statusCode === 401) {
        items.value = []
        wishlistCount.value = 0
      } else {
        throw error
      }
    } finally {
      wishlistLoaded.value = true
    }

    return items.value
  }

  const addToWishlist = async (productId: number) => {
    await csrf()
    const response = await $fetch<WishlistResponse>('/auth/wishlist', {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
      headers: headers(),
      body: { product_id: productId },
    })
    wishlistCount.value = response.count
    await fetchWishlist(true)
    return response
  }

  const removeFromWishlist = async (productId: number) => {
    await csrf()
    const response = await $fetch<WishlistResponse>(`/auth/wishlist/${productId}`, {
      baseURL: config.public.apiBase,
      method: 'DELETE',
      credentials: 'include',
      headers: headers(),
    })
    items.value = items.value.filter(item => item.product_id !== productId)
    wishlistCount.value = response.count
    return response
  }

  const clearWishlist = () => {
    items.value = []
    wishlistCount.value = 0
    wishlistLoaded.value = false
  }

  const hasProduct = (productId: number) => items.value.some(item => item.product_id === productId)

  const toggleWishlist = async (productId: number) => {
    if (hasProduct(productId)) return removeFromWishlist(productId)
    return addToWishlist(productId)
  }

  return {
    items,
    wishlistCount,
    wishlistLoaded,
    fetchWishlist,
    addToWishlist,
    removeFromWishlist,
    toggleWishlist,
    hasProduct,
    clearWishlist,
  }
}