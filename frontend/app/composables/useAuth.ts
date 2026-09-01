type AuthUser = {
  id: number
  name: string
  email: string
  email_verified_at: string | null
}

type AuthResponse = {
  status: boolean
  message?: string
  user: AuthUser
}

type LoginPayload = {
  email: string
  password: string
  remember: boolean
}

type RegisterPayload = {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export const useAuth = () => {
  const config = useRuntimeConfig()

  const user = useState<AuthUser | null>(
    'auth-user',
    () => null
  )

  const authLoaded = useState<boolean>(
    'auth-loaded',
    () => false
  )

  const isAuthenticated = computed(
    () => user.value !== null
  )

  const csrf = async () => {
    await $fetch('/sanctum/csrf-cookie', {
      baseURL: config.public.backendBase,
      credentials: 'include',
    })
  }

  const fetchUser = async () => {
    try {
      const response = await $fetch<AuthResponse>(
        '/auth/user',
        {
          baseURL: config.public.apiBase,
          credentials: 'include',
        }
      )

      user.value = response.user
    } catch (error: any) {
      if (error?.status === 401) {
        user.value = null
      } else {
        throw error
      }
    } finally {
      authLoaded.value = true
    }

    return user.value
  }

  const login = async (payload: LoginPayload) => {
    await csrf()

    const response = await $fetch<AuthResponse>(
      '/auth/login',
      {
        baseURL: config.public.apiBase,
        method: 'POST',
        credentials: 'include',
        body: payload,
      }
    )

    user.value = response.user
    authLoaded.value = true

    return response
  }

  const register = async (
    payload: RegisterPayload
  ) => {
    await csrf()

    const response = await $fetch<AuthResponse>(
      '/auth/register',
      {
        baseURL: config.public.apiBase,
        method: 'POST',
        credentials: 'include',
        body: payload,
      }
    )

    user.value = response.user
    authLoaded.value = true

    return response
  }

  const logout = async () => {
    await $fetch('/auth/logout', {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
    })

    user.value = null
    authLoaded.value = true
  }

  return {
    user,
    authLoaded,
    isAuthenticated,
    fetchUser,
    login,
    register,
    logout,
  }
}