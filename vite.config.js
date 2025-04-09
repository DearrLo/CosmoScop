import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  base: '/js/animation/',
  plugins: [vue()],
  build: {
    outDir: 'public/js/animation',
    emptyOutDir: true,
    rollupOptions: {
      input: 'assets/animations/src/main.ts'
    }
  },
  publicDir: false
})

