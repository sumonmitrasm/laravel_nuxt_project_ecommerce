const scripts = [
  '/assets/js/jquery.min.js',
  '/assets/js/bootstrap.bundle.min.js',
  '/assets/js/jquery.hoverIntent.min.js',
  '/assets/js/jquery.waypoints.min.js',
  '/assets/js/superfish.min.js',
  '/assets/js/owl.carousel.min.js',
  '/assets/js/bootstrap-input-spinner.js',
  '/assets/js/jquery.magnific-popup.min.js',
  '/assets/js/jquery.plugin.min.js',
  '/assets/js/jquery.countdown.min.js',
  '/assets/js/main.js',
  '/assets/js/demos/demo-13.js'
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
    script.dataset.mollaScript = 'true'
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
  })
})
