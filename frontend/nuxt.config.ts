import { createHash } from 'node:crypto'
import { readFileSync } from 'node:fs'

// Public assets keep their filename after generation. Change the URL when the
// stylesheet changes so browsers and Hostinger CDN fetch the deployed version.
const stylesheetVersion = createHash('sha256')
  .update(readFileSync(new URL('./public/assets/css/style.css', import.meta.url)))
  .digest('hex')
  .slice(0, 12)

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: false },
  // `nuxt generate` static HTML তৈরি করে। SSR চালু রাখলে generated HTML-এ
  // homepage-এর heading ও hero image থাকে, তাই slow mobile connection-এও
  // browser/Lighthouse JavaScript শেষ হওয়ার অপেক্ষা না করেই LCP পায়.
  ssr: true,

  runtimeConfig: {
    public: {
      backendBase: 'https://admin.shahinenterprise.com.bd',
      apiBase: 'https://admin.shahinenterprise.com.bd/api',
    }
  },

  // This project is deployed as static files.  Prerender the public landing
  // page for a fast first paint, but do not crawl every client-side link while
  // generating: a few legacy links intentionally have no static page yet.
  nitro: {
    prerender: {
      crawlLinks: false,
      routes: ['/'],
    },
  },

  app: {
    head: {
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, shrink-to-fit=no' },
        { name: 'theme-color', content: '#ffffff' }
      ],
      link: [
        { rel: 'stylesheet', href: '/assets/vendor/bootstrap/css/bootstrap.min.css' },
        { rel: 'stylesheet', href: '/assets/vendor/bootstrap-icons/bootstrap-icons.css' },
        { rel: 'stylesheet', href: `/assets/css/style.css?v=${stylesheetVersion}` },
      ]
    }
  }
})
