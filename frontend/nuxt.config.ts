// nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // API Base URL Setup
  runtimeConfig: {
    public: {
      apiBase: 'http://127.0.0.1:8000/api',
    }
  },

  app: {
    head: {
      title: 'Bootstrap eCommerce Template',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, shrink-to-fit=no' },
        { name: 'keywords', content: 'HTML5 Template' },
        { name: 'description', content: 'NovaCart — Bootstrap 5 Store' },
        { name: 'author', content: 'p-themes' },
        { name: 'apple-mobile-web-app-title', content: 'Molla' },
        { name: 'application-name', content: 'Molla' },
        { name: 'msapplication-TileColor', content: '#cc9966' },
        { name: 'theme-color', content: '#ffffff' }
      ],
      link: [
       
        
        // CSS Files
        { rel: 'stylesheet', href: '/assets/vendor/bootstrap/css/bootstrap.min.css' },
        { rel: 'stylesheet', href: '/assets/vendor/bootstrap-icons/bootstrap-icons.css' },
        { rel: 'stylesheet', href: '/assets/css/style.css' },
      ]
    }
  }
})