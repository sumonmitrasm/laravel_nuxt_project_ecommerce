type AuthUser = { id: number; name: string; email: string; email_verified_at: string | null }
type AuthResponse = { status: boolean; message?: string; user: AuthUser }
type RegisterResponse = { status: boolean; message: string; email: string }
type LoginPayload = { email: string; password: string; remember: boolean }
type RegisterPayload = { name: string; email: string; password: string; password_confirmation: string }

export const useAuth = () => {
  const config = useRuntimeConfig()
  const xsrfToken = useCookie<string | null>('XSRF-TOKEN')
  const user = useState<AuthUser | null>('auth-user', () => null)
  const authLoaded = useState<boolean>('auth-loaded', () => false)
  const isAuthenticated = computed(() => user.value !== null)

  const csrf = async () => {
    await $fetch('/sanctum/csrf-cookie', { baseURL: config.public.backendBase, credentials: 'include' })
    refreshCookie('XSRF-TOKEN')
  }
  const csrfHeaders = (): Record<string, string> => xsrfToken.value
    ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) }
    : {}

  const fetchUser = async () => {
    try {
      const response = await $fetch<AuthResponse>('/auth/user', { baseURL: config.public.apiBase, credentials: 'include' })
      user.value = response.user
      authLoaded.value = true
    } catch (error: any) {
      const status = error?.statusCode ?? error?.status
      if (status === 401 || status === 403) {
        user.value = null
        authLoaded.value = true
      } else {
        authLoaded.value = false
        throw error
      }
    }
    return user.value
  }

  const login = async (payload: LoginPayload) => {
    await csrf()
    const response = await $fetch<AuthResponse>('/auth/login', { baseURL: config.public.apiBase, method: 'POST', credentials: 'include', headers: csrfHeaders(), body: payload })
    user.value = response.user
    authLoaded.value = true
    return response
  }

  const register = async (payload: RegisterPayload) => {
    await csrf()
    const response = await $fetch<RegisterResponse>('/auth/register', { baseURL: config.public.apiBase, method: 'POST', credentials: 'include', headers: csrfHeaders(), body: payload })
    user.value = null
    authLoaded.value = true
    return response
  }

  const resendVerification = async (email: string) => {
    await csrf()
    return $fetch<{ status: boolean; message: string }>('/auth/email/resend', { baseURL: config.public.apiBase, method: 'POST', credentials: 'include', headers: csrfHeaders(), body: { email } })
  }

  const logout = async () => {
    await csrf()
    await $fetch('/auth/logout', { baseURL: config.public.apiBase, method: 'POST', credentials: 'include', headers: csrfHeaders() })
    user.value = null
    authLoaded.value = true
  }

  return { user, authLoaded, isAuthenticated, fetchUser, login, register, resendVerification, logout }
}
