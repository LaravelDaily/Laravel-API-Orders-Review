import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:2202',
        changeOrigin: true,
        // rewrite: path => path.replace(/^\/api/, '')
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          // 'X-Requested-With': 'XMLHttpRequest'
        }
      }
    }
  }
})
