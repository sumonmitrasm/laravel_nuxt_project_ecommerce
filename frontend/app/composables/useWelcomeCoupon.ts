type WelcomeCoupon = {
  code: string
  name: string
  description: string | null
}

export const useWelcomeCoupon = () => {
  const config = useRuntimeConfig()
  const coupon = useState<WelcomeCoupon | null>('welcome-coupon', () => null)
  const loaded = useState<boolean>('welcome-coupon-loaded', () => false)

  const fetchWelcomeCoupon = async () => {
    if (loaded.value) return coupon.value

    const response = await $fetch<{ coupon: WelcomeCoupon | null }>('/auth/welcome-coupon', {
      baseURL: config.public.apiBase,
      credentials: 'include',
    })

    coupon.value = response.coupon
    loaded.value = true
    return coupon.value
  }

  const clearWelcomeCoupon = () => {
    coupon.value = null
    loaded.value = false
  }

  return { coupon, fetchWelcomeCoupon, clearWelcomeCoupon }
}
