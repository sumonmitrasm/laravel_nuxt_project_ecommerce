export type LocationOption = {
  id: number
  name: string
  bn_name: string | null
}

type LocationResponse = {
  status: boolean
  locations: LocationOption[]
}

export const useLocations = () => {
  const config = useRuntimeConfig()
  const divisions = useState<LocationOption[]>('delivery-divisions', () => [])

  const fetchDivisions = async () => {
    if (Array.isArray(divisions.value) && divisions.value.length) return divisions.value
    const response = await $fetch<LocationResponse>('/locations/divisions', { baseURL: config.public.apiBase })
    divisions.value = Array.isArray(response.locations) ? response.locations : []
    return divisions.value
  }

  const fetchDistricts = async (divisionId: number) => {
    const response = await $fetch<LocationResponse>(`/locations/divisions/${divisionId}/districts`, {
      baseURL: config.public.apiBase,
    })
    return Array.isArray(response.locations) ? response.locations : []
  }

  const fetchUpazilas = async (districtId: number) => {
    const response = await $fetch<LocationResponse>(`/locations/districts/${districtId}/upazilas`, {
      baseURL: config.public.apiBase,
    })
    return Array.isArray(response.locations) ? response.locations : []
  }

  return { divisions, fetchDivisions, fetchDistricts, fetchUpazilas }
}
