export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: false },

  nitro: {
    prerender: {
      crawlLinks: false,
      failOnError: true,
      routes: ['/']
    }
  },

  runtimeConfig: {
    public: {
      // These are the live defaults. frontend/.env overrides them on localhost.
      backendBase: 'https://admin.shahinenterprise.com.bd',
      apiBase: 'https://admin.shahinenterprise.com.bd/api',
    }
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
        { rel: 'stylesheet', href: '/assets/css/style.css' },
      ]
    }
  }
})

