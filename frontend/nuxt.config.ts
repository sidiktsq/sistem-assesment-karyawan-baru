import { defineNuxtConfig } from 'nuxt/config'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: false },
  ssr: false,
  srcDir: 'src',
  css: ['~/style.css'],
  experimental: {
    // @ts-ignore
    viteEnvironmentApi: true
  },
  app: {
    head: {
      title: 'Sistem Assessment Karyawan Baru',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ]
    }
  }
} as any)
