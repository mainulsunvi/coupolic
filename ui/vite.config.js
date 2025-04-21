import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    headers: {
      'Access-Control-Allow-Origin': '*',
    }
  },
  build: {
    outDir: '../assets/build',
    assetsDir: 'assets',
    rollupOptions: {
      output: {
        inlineDynamicImports: true,
        entryFileNames: 'assets/coupolic-admin-app-script.js',
        assetFileNames: `assets/coupolic-admin-app.[ext]`,
      }
    },
  }
})
