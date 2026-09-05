const scripts = [
  '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
  '/assets/js/interactions.js',
]

function loadScript(src: string) {
  return new Promise<void>((resolve, reject) => {
    const existing = document.querySelector<HTMLScriptElement>(`script[src="${src}"]`)

    if (existing) {
      if (existing.dataset.loaded === 'true') resolve()
      else existing.addEventListener('load', () => resolve(), { once: true })
      return
    }

    const script = document.createElement('script')
    script.src = src
    script.dataset.novacartScript = 'true'
    script.addEventListener('load', () => {
      script.dataset.loaded = 'true'
      resolve()
    }, { once: true })
    script.addEventListener('error', () => reject(new Error(`Failed to load ${src}`)), { once: true })
    document.body.appendChild(script)
  })
}

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.hook('app:mounted', async () => {
    for (const src of scripts) await loadScript(src)

    const bootstrap = (window as typeof window & {
      bootstrap?: {
        Carousel: {
          getOrCreateInstance: (element: Element, options?: { interval: number; ride: string }) => {
            cycle: () => void
          }
        }
      }
    }).bootstrap

    if (!bootstrap) return

    document.querySelectorAll('.carousel').forEach((element) => {
      const carousel = bootstrap.Carousel.getOrCreateInstance(element, {
        interval: Number(element.getAttribute('data-bs-interval')) || 5000,
        ride: 'carousel',
      })
      carousel.cycle()
    })
  })
})
