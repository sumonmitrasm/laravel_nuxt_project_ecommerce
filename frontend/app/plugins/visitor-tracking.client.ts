export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const visitorId = useCookie<string | null>('store_visitor_id', {
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
  })

  if (!visitorId.value) {
    visitorId.value = crypto.randomUUID()
  }

  const logVisit = (to: { fullPath: string }) => {
    $fetch('/visitors', {
      baseURL: config.public.apiBase,
      method: 'POST',
      credentials: 'include',
      body: {
        visitor_id: visitorId.value,
        path: to.fullPath,
        page_title: document.title,
        referrer: document.referrer || null,
      },
    }).catch(() => {})
  }

  nuxtApp.hook('page:finish', () => logVisit(useRoute()))
})
