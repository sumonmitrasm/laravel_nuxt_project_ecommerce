// nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  app: {
    head: {
      title: 'Molla E-Commerce',
      link: [
        // Molla CSS ফাইলগুলো
        { rel: 'stylesheet', href: '/assets/css/bootstrap.min.css' },
        { rel: 'stylesheet', href: '/assets/css/style.css' }
      ],
      script: [
        // Molla JS ফাইলগুলো (প্রয়োজন অনুযায়ী)
        { src: '/assets/js/jquery.min.js', tagPosition: 'bodyClose' },
        { src: '/assets/js/bootstrap.bundle.min.js', tagPosition: 'bodyClose' },
        { src: '/assets/js/main.js', tagPosition: 'bodyClose' }
      ]
    }
  }
})