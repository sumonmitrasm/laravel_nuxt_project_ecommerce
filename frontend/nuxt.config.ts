// nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  app: {
    head: {
      title: 'Molla - Bootstrap eCommerce Template',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, shrink-to-fit=no' },
        { name: 'keywords', content: 'HTML5 Template' },
        { name: 'description', content: 'Molla - Bootstrap eCommerce Template' },
        { name: 'author', content: 'p-themes' },
        { name: 'apple-mobile-web-app-title', content: 'Molla' },
        { name: 'application-name', content: 'Molla' },
        { name: 'msapplication-TileColor', content: '#cc9966' },
        { name: 'msapplication-config', content: '/assets/images/icons/browserconfig.xml' },
        { name: 'theme-color', content: '#ffffff' }
      ],
      link: [
        // Favicons
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/assets/images/icons/apple-touch-icon.png' },
        { rel: 'icon', type: 'image/png', sizes: '32x32', href: '/assets/images/icons/favicon-32x32.png' },
        { rel: 'icon', type: 'image/png', sizes: '16x16', href: '/assets/images/icons/favicon-16x16.png' },
        { rel: 'shortcut icon', href: '/assets/images/icons/favicon.ico' },
        
        // CSS Files
        { rel: 'stylesheet', href: '/assets/vendor/line-awesome/line-awesome/line-awesome/css/line-awesome.min.css' },
        { rel: 'stylesheet', href: '/assets/css/bootstrap.min.css' },
        { rel: 'stylesheet', href: '/assets/css/plugins/owl-carousel/owl.carousel.css' },
        { rel: 'stylesheet', href: '/assets/css/plugins/magnific-popup/magnific-popup.css' },
        { rel: 'stylesheet', href: '/assets/css/plugins/jquery.countdown.css' },
        { rel: 'stylesheet', href: '/assets/css/style.css' },
        { rel: 'stylesheet', href: '/assets/css/skins/skin-demo-13.css' },
        { rel: 'stylesheet', href: '/assets/css/demos/demo-13.css' }
      ]
    }
  }
})
