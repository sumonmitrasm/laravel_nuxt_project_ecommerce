export type UserAddress = {
  id: number
  label: string
  recipient_name: string
  phone: string
  alternative_phone: string | null
  division: string
  district: string
  upazila: string
  area: string | null
  postal_code: string | null
  address_line: string
  is_default: boolean
  status: boolean
}

export type AddressPayload = Omit<UserAddress, 'id' | 'status'>

type AddressListResponse = { status: boolean; addresses: UserAddress[] }
type AddressMutationResponse = { status: boolean; message: string; address?: UserAddress }

export const useAddresses = () => {
  const config = useRuntimeConfig()
  const xsrfToken = useCookie<string | null>('XSRF-TOKEN')
  const addresses = useState<UserAddress[]>('user-addresses', () => [])
  const addressesLoaded = useState<boolean>('user-addresses-loaded', () => false)
  const defaultAddress = computed(() => addresses.value.find(address => address.is_default) ?? null)

  const csrf = async () => {
    await $fetch('/sanctum/csrf-cookie', {
      baseURL: config.public.backendBase,
      credentials: 'include',
    })
    refreshCookie('XSRF-TOKEN')
  }

  const csrfHeaders = (): Record<string, string> => xsrfToken.value
    ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrfToken.value) }
    : {}

  const fetchAddresses = async (force = false) => {
    if (addressesLoaded.value && !force) return addresses.value

    const response = await $fetch<AddressListResponse>('/auth/addresses', {
      baseURL: config.public.apiBase,
      credentials: 'include',
    })
    addresses.value = response.addresses
    addressesLoaded.value = true
    return addresses.value
  }

  const createAddress = async (payload: AddressPayload) => {
    await csrf()
    const response = await $fetch<AddressMutationResponse>('/auth/addresses', {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
      headers: csrfHeaders(),
      body: payload,
    })
    await fetchAddresses(true)
    return response
  }

  const updateAddress = async (id: number, payload: AddressPayload) => {
    await csrf()
    const response = await $fetch<AddressMutationResponse>(`/auth/addresses/${id}`, {
      baseURL: config.public.apiBase,
      method: 'PATCH',
      credentials: 'include',
      headers: csrfHeaders(),
      body: payload,
    })
    await fetchAddresses(true)
    return response
  }

  const removeAddress = async (id: number) => {
    await csrf()
    const response = await $fetch<AddressMutationResponse>(`/auth/addresses/${id}`, {
      baseURL: config.public.apiBase,
      method: 'DELETE',
      credentials: 'include',
      headers: csrfHeaders(),
    })
    await fetchAddresses(true)
    return response
  }

  const makeDefaultAddress = async (id: number) => {
    await csrf()
    const response = await $fetch<AddressMutationResponse>(`/auth/addresses/${id}/default`, {
      baseURL: config.public.apiBase,
      method: 'PATCH',
      credentials: 'include',
      headers: csrfHeaders(),
    })
    await fetchAddresses(true)
    return response
  }

  return {
    addresses,
    addressesLoaded,
    defaultAddress,
    fetchAddresses,
    createAddress,
    updateAddress,
    removeAddress,
    makeDefaultAddress,
  }
}
