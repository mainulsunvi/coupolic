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
    assetsDir: '.',
    emptyOutDir: true,
    minify: true,
    rollupOptions: {
      output: {
        inlineDynamicImports: true,
        entryFileNames: 'coupolic-ui-scripts.js',
        assetFileNames: `coupolic-ui-styles.[ext]`,
      }
    },
    // lib: {
    //   entry: './src/main.js',
    //   formats: ['es'],
    // }
  }
})
